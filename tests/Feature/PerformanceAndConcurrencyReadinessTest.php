<?php

namespace Tests\Feature;

use App\Jobs\SendOtpEmailJob;
use App\Mail\OtpMail;
use App\Models\BallotBox;
use App\Models\Candidate;
use App\Models\ElectionCategory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PerformanceAndConcurrencyReadinessTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * UJI 1: Job Queue Asinkron - SendOtpEmailJob Berhasil Dipush ke Antrean Queue
     */
    public function test_send_otp_email_job_is_dispatchable_and_queueable(): void
    {
        Queue::fake();

        $testNim = '120140111';
        $testEmail = 'mahasiswa.async@polbeng.ac.id';
        $testOtp = '654321';

        SendOtpEmailJob::dispatch($testNim, $testEmail, $testOtp);

        Queue::assertPushed(SendOtpEmailJob::class, function ($job) use ($testNim, $testEmail, $testOtp) {
            return $job->nim === $testNim 
                && $job->email === $testEmail 
                && $job->plainOtp === $testOtp
                && $job->tries === 3
                && $job->backoff === [5, 15, 30]
                && $job->timeout === 30;
        });
    }

    /**
     * UJI 2: Eksekusi Worker Job - SendOtpEmailJob Mengirim Email via SMTP / Resend
     */
    public function test_send_otp_email_job_executes_smoothly(): void
    {
        Mail::fake();

        $testNim = '120140222';
        $testEmail = 'mahasiswa.worker@polbeng.ac.id';
        $testOtp = '789123';

        $job = new SendOtpEmailJob($testNim, $testEmail, $testOtp);
        $job->handle();

        Mail::assertSent(OtpMail::class, function ($mail) use ($testEmail, $testOtp) {
            return $mail->hasTo($testEmail) && $mail->otp === $testOtp;
        });
    }

    /**
     * UJI 3: Konfigurasi Redis & High-Concurrency Database Drivers
     */
    public function test_redis_configuration_structure_is_properly_defined(): void
    {
        $redisConfig = config('database.redis');

        $this->assertIsArray($redisConfig, 'Konfigurasi database.redis wajib tersedia.');
        $this->assertArrayHasKey('default', $redisConfig);
        $this->assertArrayHasKey('cache', $redisConfig);
        $this->assertArrayHasKey('session', $redisConfig);
        $this->assertArrayHasKey('queue', $redisConfig);

        $this->assertSame('1', (string) $redisConfig['cache']['database']);
        $this->assertSame('2', (string) $redisConfig['session']['database']);
        $this->assertSame('3', (string) $redisConfig['queue']['database']);
    }

    /**
     * UJI 4: Halaman Maintenance Mode 503 Bebas Ketergantungan Database
     */
    public function test_503_maintenance_view_renders_cleanly_without_db_dependency(): void
    {
        $rendered = view('errors.503')->render();

        $this->assertStringContainsString('Sistem Sedang Dipelihara', $rendered);
        $this->assertStringContainsString('503', $rendered);
        $this->assertStringContainsString('+62 851-8795-3580', $rendered);
        $this->assertStringContainsString('Integritas Suara Dijamin Aman 100%', $rendered);
    }

    /**
     * UJI 5: Integritas Indeks Komposit Kotak Suara untuk Live Quick Count
     */
    public function test_covering_index_query_on_ballot_boxes_is_efficient(): void
    {
        // Siapkan Kategori & Paslon
        $category = ElectionCategory::firstOrCreate(
            ['code' => 'BEM_PERF_TEST'],
            ['name' => 'Kategori Uji Performa', 'pillar' => 'BEM', 'is_active' => true]
        );

        $candidate = Candidate::firstOrCreate(
            ['election_category_id' => $category->id, 'candidate_number' => 1],
            [
                'leader_name' => 'Kandidat Uji Index',
                'vision' => 'Visi Uji Index Performa',
                'mission' => 'Misi Uji Index Performa',
                'is_active' => true,
            ]
        );

        // Masukkan suara dummy
        BallotBox::create([
            'election_category_id' => $category->id,
            'candidate_id' => $candidate->id,
            'ballot_hash' => hash('sha256', 'perf_test_' . microtime(true)),
            'verification_code' => 'PEMIRA-PERF-' . random_int(1000, 9999),
            'created_at' => now(),
        ]);

        // Kueri agregasi Quick Count (Covering index query)
        $counts = BallotBox::where('election_category_id', $category->id)
            ->select('candidate_id', DB::raw('count(*) as total'))
            ->groupBy('candidate_id')
            ->get();

        $this->assertNotEmpty($counts);
        $this->assertSame($candidate->id, $counts->first()->candidate_id);
        $this->assertGreaterThanOrEqual(1, $counts->first()->total);
    }

    /**
     * UJI 6: Verifikasi Keberadaan Indeks Komposit di Skema Fisik MySQL
     */
    public function test_composite_indexes_exist_in_database_schema(): void
    {
        $hasBallotIndex = !empty(DB::select("SHOW INDEX FROM `ballot_boxes` WHERE Key_name = 'idx_ballot_cat_candidate'"));
        $this->assertTrue($hasBallotIndex, 'Indeks idx_ballot_cat_candidate wajib terpasang di ballot_boxes untuk mempercepat Live Quick Count.');

        $hasVoterEmailIndex = !empty(DB::select("SHOW INDEX FROM `voters` WHERE Key_name = 'idx_voters_email_hash'"));
        $this->assertTrue($hasVoterEmailIndex, 'Indeks idx_voters_email_hash wajib terpasang di voters.');

        $hasCandidateIndex = !empty(DB::select("SHOW INDEX FROM `candidates` WHERE Key_name = 'idx_candidates_cat_active_num'"));
        $this->assertTrue($hasCandidateIndex, 'Indeks idx_candidates_cat_active_num wajib terpasang di candidates.');
    }
}
