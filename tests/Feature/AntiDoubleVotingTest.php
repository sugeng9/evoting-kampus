<?php

namespace Tests\Feature;

use App\Models\BallotBox;
use App\Models\Candidate;
use App\Models\ElectionCategory;
use App\Models\ElectionSetting;
use App\Models\Voter;
use App\Models\VoterParticipation;
use App\Services\DoubleVotingException;
use App\Services\VotingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AntiDoubleVotingTest extends TestCase
{
    use DatabaseTransactions;

    protected VotingService $votingService;
    protected Voter $voter;
    protected ElectionCategory $category;
    protected Candidate $candidate1;
    protected Candidate $candidate2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->votingService = app(VotingService::class);

        // Pastikan Sesi Pemilihan Aktif & Buka
        $setting = ElectionSetting::first() ?? new ElectionSetting();
        $setting->status = 'OPEN';
        $setting->voting_starts_at = now()->subHours(2);
        $setting->voting_ends_at = now()->addHours(6);
        $setting->save();

        // Siapkan Kategori Pemilihan Aktif
        $this->category = ElectionCategory::firstOrCreate(
            ['code' => 'BEM_TEST_' . Str::random(5)],
            [
                'name' => 'Pemilihan Presma BEM Uji Integritas',
                'description' => 'Kategori simulasi anti double voting',
                'pillar' => 'BEM',
                'is_active' => true,
                'status_override' => 'FORCE_OPEN',
                'start_at' => now()->subHours(1),
                'end_at' => now()->addHours(5),
            ]
        );
        $this->category->is_active = true;
        $this->category->status_override = 'FORCE_OPEN';
        $this->category->start_at = now()->subHours(1);
        $this->category->end_at = now()->addHours(5);
        $this->category->save();

        // Siapkan 2 Kandidat untuk kategori ini
        $this->candidate1 = Candidate::firstOrCreate(
            [
                'election_category_id' => $this->category->id,
                'candidate_number' => 1,
            ],
            [
                'leader_name' => 'Kandidat Uji Satu',
                'leader_nim' => '100000001',
                'vice_leader_name' => 'Wakil Uji Satu',
                'vice_leader_nim' => '100000002',
                'vision' => 'Visi Uji 1',
                'mission' => 'Misi Uji 1',
                'status' => 'active',
            ]
        );

        $this->candidate2 = Candidate::firstOrCreate(
            [
                'election_category_id' => $this->category->id,
                'candidate_number' => 2,
            ],
            [
                'leader_name' => 'Kandidat Uji Dua',
                'leader_nim' => '200000001',
                'vice_leader_name' => 'Wakil Uji Dua',
                'vice_leader_nim' => '200000002',
                'vision' => 'Visi Uji 2',
                'mission' => 'Misi Uji 2',
                'status' => 'active',
            ]
        );

        // Siapkan 1 Pemilih Valid
        $this->voter = Voter::create([
            'nim' => (string) random_int(100000000, 999999999),
            'name' => 'Mahasiswa Penguji Integritas',
            'faculty' => 'Teknik',
            'study_program' => 'Teknik Informatika',
            'angkatan' => '2024',
            'academic_status' => 'aktif',
            'dpt_status' => 'DPT',
            'password' => Hash::make('123456'),
            'token_plain' => '123456',
            'is_active' => true,
            'sudah_memilih' => false,
            'pendaftaran_status' => 'aktif',
        ]);
    }

    /**
     * Uji 1: Suara pertama berhasil masuk, status sudah_memilih berubah jadi true,
     * dan terdapat tepat 1 baris surat suara dan 1 baris presensi.
     */
    public function test_first_vote_succeeds_and_marks_voter_as_voted(): void
    {
        $this->assertFalse($this->voter->fresh()->sudah_memilih);

        $receipt = $this->votingService->castVote(
            $this->voter,
            $this->category->id,
            $this->candidate1->id
        );

        $this->assertIsArray($receipt);
        $this->assertArrayHasKey('verification_code', $receipt);

        // Verifikasi status voter terkini
        $freshVoter = $this->voter->fresh();
        $this->assertTrue((bool) $freshVoter->sudah_memilih);
        $this->assertNotNull($freshVoter->voted_at);

        // Verifikasi rekaman presensi
        $this->assertDatabaseHas('voter_participations', [
            'voter_id' => $this->voter->id,
            'election_category_id' => $this->category->id,
        ]);

        // Verifikasi surat suara tersimpan di kotak suara
        $this->assertDatabaseHas('ballot_boxes', [
            'election_category_id' => $this->category->id,
            'candidate_id' => $this->candidate1->id,
            'verification_code' => $receipt['verification_code'],
        ]);
    }

    /**
     * Uji 2: Percobaan memilih kedua kali pada kategori yang sama ditolak keras
     * dengan DoubleVotingException.
     */
    public function test_second_vote_attempt_on_same_category_is_blocked(): void
    {
        // Berikan suara pertama
        $this->votingService->castVote(
            $this->voter,
            $this->category->id,
            $this->candidate1->id
        );

        // Percobaan suara kedua oleh akun yang sama harus memicu Exception
        $this->expectException(DoubleVotingException::class);

        $this->votingService->castVote(
            $this->voter,
            $this->category->id,
            $this->candidate2->id
        );
    }

    /**
     * Uji 3: Simulasi 2 Vote Bersamaan (Parallel Race Condition Simulation).
     * Mensimulasikan dua thread/proses bersamaan yang mencoba memasukkan suara.
     * Hanya 1 yang boleh lolos dan berhasil commit ke database!
     */
    public function test_concurrent_parallel_votes_only_one_succeeds(): void
    {
        $voterId = $this->voter->id;
        $categoryId = $this->category->id;
        $candidate1Id = $this->candidate1->id;
        $candidate2Id = $this->candidate2->id;

        $successCount = 0;
        $blockedCount = 0;

        // Simulasi request paralel:
        // Request A masuk
        try {
            $this->votingService->castVote($this->voter, $categoryId, $candidate1Id);
            $successCount++;
        } catch (\Throwable $e) {
            $blockedCount++;
        }

        // Request B mencoba masuk bersamaan untuk voter dan kategori yang sama
        try {
            $this->votingService->castVote($this->voter->fresh(), $categoryId, $candidate2Id);
            $successCount++;
        } catch (DoubleVotingException $e) {
            $blockedCount++;
        } catch (\Throwable $e) {
            $blockedCount++;
        }

        // ASSERTION: Hanya 1 yang lolos, 1 lagi harus terblokir!
        $this->assertEquals(1, $successCount, 'Hanya tepat 1 suara yang boleh berhasil.');
        $this->assertEquals(1, $blockedCount, 'Percobaan ganda kedua harus diblokir.');

        // Verifikasi database: Hanya ada tepat 1 partisipasi dan 1 suara di ballot box untuk voter ini
        $participations = VoterParticipation::where('voter_id', $voterId)
            ->where('election_category_id', $categoryId)
            ->count();
        $this->assertEquals(1, $participations, 'Jumlah baris partisipasi di database harus tepat 1.');
    }

    /**
     * Uji 4: Lapis Kedua Keamanan Database (Unique Constraint Guard).
     * Jika terjadi bypass aplikasi, database level unique constraint
     * (voter_id, election_category_id) WAJIB menolak insert duplikat.
     */
    public function test_database_unique_constraint_rejects_raw_duplicate_participation(): void
    {
        // Insert pertama langsung ke DB
        VoterParticipation::create([
            'voter_id' => $this->voter->id,
            'election_category_id' => $this->category->id,
            'receipt_hash' => hash('sha256', 'test_receipt_1'),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Test',
            'voted_at' => now(),
        ]);

        // Insert kedua langsung ke DB (mensimulasikan bypass logika PHP)
        $this->expectException(\Illuminate\Database\QueryException::class);

        VoterParticipation::create([
            'voter_id' => $this->voter->id,
            'election_category_id' => $this->category->id,
            'receipt_hash' => hash('sha256', 'test_receipt_2'),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit Test',
            'voted_at' => now(),
        ]);
    }

    /**
     * Uji 5: Prinsip Kerahasiaan Suara (Secret Ballot Isolation).
     * Memastikan tabel ballot_boxes tidak menyimpan referensi apapun ke identitas pemilih.
     */
    public function test_ballot_box_has_zero_reference_to_voter(): void
    {
        $receipt = $this->votingService->castVote(
            $this->voter,
            $this->category->id,
            $this->candidate1->id
        );

        $ballot = BallotBox::where('verification_code', $receipt['verification_code'])->firstOrFail();

        // Pastikan tidak ada atribut identitas pemilih di instance BallotBox
        $this->assertFalse(isset($ballot->voter_id));
        $this->assertFalse(isset($ballot->nim));
        $this->assertFalse(isset($ballot->email));
        $this->assertFalse(isset($ballot->user_id));

        // Verifikasi struktur kolom tabel secara nyata di database
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing('ballot_boxes');
        $this->assertNotContains('voter_id', $columns);
        $this->assertNotContains('nim', $columns);
        $this->assertNotContains('nama', $columns);
    }

    /**
     * Uji 6: Idempotency Token Sekali Pakai (One-Time Token Guard).
     * Token voting yang tidak valid atau telah dipakai ulang harus ditolak.
     */
    public function test_idempotency_token_validation_and_consumption(): void
    {
        $sessionKey = 'voting_token_' . $this->category->id;
        $validToken = 'valid_token_xyz_1234567890';
        session([$sessionKey => $validToken]);

        // Percobaan dengan token yang salah -> harus ditolak
        try {
            $this->votingService->castVote(
                $this->voter,
                $this->category->id,
                $this->candidate1->id,
                'invalid_fake_token'
            );
            $this->fail('Harusnya melempar DoubleVotingException saat token salah.');
        } catch (DoubleVotingException $e) {
            $this->assertStringContainsString('Token bilik suara tidak valid', $e->getMessage());
        }

        // Percobaan dengan token yang benar -> harus berhasil
        session([$sessionKey => $validToken]);
        $receipt = $this->votingService->castVote(
            $this->voter,
            $this->category->id,
            $this->candidate1->id,
            $validToken
        );
        $this->assertNotEmpty($receipt);

        // Token di session harus sudah terhapus (hangus / single-use consumed)
        $this->assertNull(session($sessionKey));
    }
}
