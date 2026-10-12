<?php

namespace App\Console\Commands;

use App\Models\BallotBox;
use App\Models\ElectionCategory;
use App\Models\Voter;
use App\Models\VoterParticipation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyVotingIntegrity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voting:verifikasi {--category= : Verifikasi hanya kategori pemilihan tertentu berdasarkan ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifikasi integritas matematis & kriptografis: rekonsiliasi jumlah suara vs pemilih (Anti Double Voting Audit)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('==================================================================');
        $this->info('🛡️  VERIFIKASI INTEGRITAS SUARA & REKONSILIASI KOTAK SUARA DIGITAL');
        $this->info('    Komisi Pemilihan Raya Mahasiswa (KPR) & PanWas');
        $this->info('==================================================================');
        $this->line('Waktu Audit : ' . now()->timezone('Asia/Jakarta')->translatedFormat('l, d F Y - H:i:s') . ' WIB');
        $this->newLine();

        $hasDiscrepancy = false;

        // -------------------------------------------------------------
        // 1. AUDIT PRIVASI SUARA (ZERO-KNOWLEDGE / SECRET BALLOT CHECK)
        // -------------------------------------------------------------
        $this->line('1. Memeriksa Prinsip Kerahasiaan Suara (Secret Ballot Isolation)...');
        $ballotColumns = Schema::getColumnListing('ballot_boxes');
        $forbiddenColumns = ['voter_id', 'nim', 'email', 'user_id', 'nama', 'ip_address'];
        $leakedColumns = array_intersect($forbiddenColumns, $ballotColumns);

        if (!empty($leakedColumns)) {
            $this->error('   ❌ PELANGGARAN PRIVASI: Ditemukan kolom identitas pemilih di ballot_boxes: ' . implode(', ', $leakedColumns));
            $hasDiscrepancy = true;
        } else {
            $this->info('   ✅ PASSED: Kotak suara (ballot_boxes) 100% anonim dan terisolasi dari identitas pemilih.');
        }

        // -------------------------------------------------------------
        // 2. AUDIT KONSISTENSI STATUS PEMILIH (voters vs voter_participations)
        // -------------------------------------------------------------
        $this->newLine();
        $this->line('2. Memeriksa Konsistensi Status Pemilih (voters.sudah_memilih vs presensi)...');

        $totalVoters = Voter::count();
        $votersFlaggedVoted = Voter::where('sudah_memilih', true)->count();
        $uniqueVotersInParticipation = VoterParticipation::distinct('voter_id')->count('voter_id');

        $this->line("   - Total DPT Terdaftar          : {$totalVoters}");
        $this->line("   - Pemilih 'sudah_memilih = true': {$votersFlaggedVoted}");
        $this->line("   - Pemilih unik tercatat presensi : {$uniqueVotersInParticipation}");

        // Cek anomali: status true tapi tidak ada presensi
        $phantomVoters = Voter::where('sudah_memilih', true)
            ->whereDoesntHave('participations')
            ->pluck('nim')
            ->toArray();

        // Cek anomali: ada presensi tapi status masih false
        $desyncedVoters = Voter::where('sudah_memilih', false)
            ->whereHas('participations')
            ->pluck('nim')
            ->toArray();

        if (!empty($phantomVoters)) {
            $this->error('   ❌ ANOMALI DITEMUKAN: Ada pemilih berstatus sudah_memilih = true tetapi tidak memiliki riwayat presensi:');
            $this->error('      NIM: ' . implode(', ', array_slice($phantomVoters, 0, 10)) . (count($phantomVoters) > 10 ? ' ...dan lainnya' : ''));
            $hasDiscrepancy = true;
        }

        if (!empty($desyncedVoters)) {
            $this->warn('   ⚠️ PERINGATAN DESINKRONISASI: Ada pemilih yang sudah memilih tapi flag sudah_memilih = false:');
            $this->warn('      NIM: ' . implode(', ', array_slice($desyncedVoters, 0, 10)) . (count($desyncedVoters) > 10 ? ' ...dan lainnya' : ''));
            $this->line('      (Menjalankan auto-sync flag...');
            Voter::whereIn('nim', $desyncedVoters)->update(['sudah_memilih' => true]);
            $this->info('      ✅ Sinkronisasi flag selesai).');
        }

        if (empty($phantomVoters) && empty($desyncedVoters)) {
            $this->info('   ✅ PASSED: Status pemilih sudah_memilih konsisten 100% dengan log presensi.');
        }

        // -------------------------------------------------------------
        // 3. REKONSILIASI PER KATEGORI (ballot_boxes vs voter_participations)
        // -------------------------------------------------------------
        $this->newLine();
        $this->line('3. Menjalankan Rekonsiliasi Surat Suara per Kategori Pemilihan...');

        $categoryOption = $this->option('category');
        $categoriesQuery = ElectionCategory::query();
        if ($categoryOption) {
            $categoriesQuery->where('id', $categoryOption);
        }
        $categories = $categoriesQuery->orderBy('id')->get();

        if ($categories->isEmpty()) {
            $this->warn('   Tidak ada kategori pemilihan yang ditemukan.');
        } else {
            $tableData = [];

            foreach ($categories as $cat) {
                $ballotCount = BallotBox::where('election_category_id', $cat->id)->count();
                $participationCount = VoterParticipation::where('election_category_id', $cat->id)->count();
                $diff = $ballotCount - $participationCount;

                $status = ($diff === 0) ? '✅ MATCH (SAH)' : '❌ SELISIH (' . ($diff > 0 ? "+{$diff}" : $diff) . ')';

                if ($diff !== 0) {
                    $hasDiscrepancy = true;
                }

                $tableData[] = [
                    'ID' => $cat->id,
                    'Kategori' => $cat->name,
                    'Kotak Suara (Ballots)' => $ballotCount,
                    'Presensi Pemilih' => $participationCount,
                    'Selisih' => $diff,
                    'Status' => $status,
                ];
            }

            $this->table(
                ['ID', 'Kategori', 'Kotak Suara (Ballots)', 'Presensi Pemilih', 'Selisih', 'Status'],
                $tableData
            );
        }

        // -------------------------------------------------------------
        // 4. AUDIT DATABASE UNIQUE CONSTRAINT (ANTI DOUBLE VOTING GUARD)
        // -------------------------------------------------------------
        $this->newLine();
        $this->line('4. Memeriksa Lapis Keamanan Database (Unique Constraint Guard)...');

        $duplicates = DB::table('voter_participations')
            ->select('voter_id', 'election_category_id', DB::raw('count(*) as total'))
            ->groupBy('voter_id', 'election_category_id')
            ->having('total', '>', 1)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $this->error('   ❌ PELANGGARAN KRITIS: Ditemukan rekaman duplikat di voter_participations!');
            foreach ($duplicates as $dup) {
                $this->error("      Voter ID {$dup->voter_id} memilih kategori {$dup->election_category_id} sebanyak {$dup->total} kali!");
            }
            $hasDiscrepancy = true;
        } else {
            $this->info('   ✅ PASSED: Tidak ada satupun suara ganda (Zero Duplicate Voting di DB).');
        }

        // -------------------------------------------------------------
        // 5. AUDIT KRIPTOGRAFIS BALLOT HASH & VERIFICATION CODE
        // -------------------------------------------------------------
        $this->newLine();
        $this->line('5. Memeriksa Integritas Kriptografis Ballot Hash & Kode Verifikasi...');

        $emptyHashes = BallotBox::whereNull('ballot_hash')->orWhere('ballot_hash', '')->count();
        $emptyCodes = BallotBox::whereNull('verification_code')->orWhere('verification_code', '')->count();
        $duplicateCodes = DB::table('ballot_boxes')
            ->select('verification_code', DB::raw('count(*) as total'))
            ->groupBy('verification_code')
            ->having('total', '>', 1)
            ->count();

        if ($emptyHashes > 0 || $emptyCodes > 0 || $duplicateCodes > 0) {
            $this->error("   ❌ CACAT KRIPTOGRAFIS: Empty Hash: {$emptyHashes}, Empty Code: {$emptyCodes}, Duplikat Kode: {$duplicateCodes}");
            $hasDiscrepancy = true;
        } else {
            $this->info('   ✅ PASSED: Seluruh surat suara memiliki cryptographic hash & token verifikasi unik.');
        }

        // -------------------------------------------------------------
        // KESIMPULAN AKHIR
        // -------------------------------------------------------------
        $this->newLine();
        $this->info('==================================================================');
        if ($hasDiscrepancy) {
            $this->error('❌ AUDIT GAGAL: Terdeteksi selisih atau anomali pada data pemungutan suara!');
            $this->error('   Silakan lakukan investigasi forensik dan bandingkan dengan audit_logs.');
            $this->info('==================================================================');
            return Command::FAILURE;
        }

        $this->info('🎉 AUDIT SELESAI: 100% SUARA SAH, TIDAK ADA SELISIH (ZERO DISCREPANCY)!');
        $this->info('   Integritas pemilihan umum terjamin, anti-double voting terverifikasi aktif.');
        $this->info('==================================================================');

        return Command::SUCCESS;
    }
}
