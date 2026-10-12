<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class VerifyAuditChain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:verifikasi {--limit= : Batasi verifikasi hanya N rekaman terakhir} {--show-all : Tampilkan seluruh rincian rekaman}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifikasi keutuhan rantai hash kriptografis SHA-256 pada tabel audit_logs (Anti-Tampering Chain Audit)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('==================================================================');
        $this->info('🔗 VERIFIKASI KEUTUHAN RANTAI LOG AUDIT (BLOCKCHAIN-STYLE AUDIT)');
        $this->info('   Sistem Audit Forensik & Anti-Manipulasi KPR & PanWas');
        $this->info('==================================================================');
        $this->line('Waktu Audit : ' . now()->timezone('Asia/Jakarta')->translatedFormat('l, d F Y - H:i:s') . ' WIB');
        $this->newLine();

        // 1. Periksa ketersediaan kolom rantai hash
        if (!Schema::hasColumn('audit_logs', 'hash_ini') || !Schema::hasColumn('audit_logs', 'hash_sebelumnya')) {
            $this->error('❌ GAGAL: Kolom hash_sebelumnya dan hash_ini belum tersedia di tabel audit_logs.');
            $this->warn('   Silakan jalankan migrasi terlebih dahulu: php artisan migrate');
            return Command::FAILURE;
        }

        $query = AuditLog::query()->orderBy('id', 'asc');
        $limit = $this->option('limit');
        if ($limit && is_numeric($limit)) {
            $query->take((int) $limit);
        }

        $logs = $query->get();
        $totalLogs = $logs->count();

        if ($totalLogs === 0) {
            $this->warn('⚠️ Log audit masih kosong (belum ada aktivitas tercatat).');
            return Command::SUCCESS;
        }

        $this->line("1. Memulai Analisis Verifikasi Rantai Kriptografis ({$totalLogs} rekaman)...");

        $brokenLinkCount = 0;
        $tamperedDataCount = 0;
        $expectedPrevHash = str_repeat('0', 64); // Genesis hash
        $showAll = $this->option('show-all');

        $progressBar = $this->output->createProgressBar($totalLogs);
        $progressBar->start();

        $eventStats = [];

        foreach ($logs as $index => $log) {
            // Hitung statistik aksi
            $eventStats[$log->aksi] = ($eventStats[$log->aksi] ?? 0) + 1;

            // Uji 1: Verifikasi Rantai Hash (Chain Link Verification)
            // Apakah hash_sebelumnya di baris ini sama dengan hash_ini dari baris sebelumnya?
            if ($log->hash_sebelumnya !== $expectedPrevHash) {
                $brokenLinkCount++;
                $this->newLine();
                $this->error("   🚨 RANTAI PUTUS PADA LOG #{$log->id}!");
                $this->error("      - Diharapkan (Prev Hash) : {$expectedPrevHash}");
                $this->error("      - Ditemukan di DB        : {$log->hash_sebelumnya}");
                $this->warn("      Indikasi: Baris log sebelumnya telah dihapus atau disisipkan secara ilegal di database!");
            }

            // Uji 2: Verifikasi Isi Rekaman (Data Tampering Verification)
            // Hitung ulang SHA-256 dari data mentah dan bandingkan dengan hash_ini tersimpan
            $recalculatedHash = AuditLog::calculateHash(
                $log->aktor,
                $log->aksi,
                $log->target,
                $log->ip_address,
                $log->created_at,
                $log->payload,
                $log->hash_sebelumnya
            );

            if ($log->hash_ini !== $recalculatedHash) {
                $tamperedDataCount++;
                $this->newLine();
                $this->error("   🚨 DATA TERMANIPULASI PADA LOG #{$log->id} ({$log->aksi})!");
                $this->error("      - Hash Tersimpan : {$log->hash_ini}");
                $this->error("      - Hash Terhitung : {$recalculatedHash}");
                $this->warn("      Indikasi: Nilai kolom (aktor/aksi/target/payload/waktu) telah diubah secara manual di database!");
            }

            // Update expected hash untuk iterasi baris berikutnya
            $expectedPrevHash = $log->hash_ini;

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // 2. Tampilkan Statistik Rekapitulasi Aktivitas
        $this->line('2. Rekapitulasi Aktivitas yang Tercatat:');
        $statRows = [];
        foreach ($eventStats as $event => $count) {
            $statRows[] = [$event, $count];
        }
        $this->table(['Peristiwa / Aksi (Event Type)', 'Total Terjadi'], $statRows);

        // 3. Tampilkan Informasi Blok Awal & Terakhir
        $firstLog = $logs->first();
        $lastLog = $logs->last();

        $this->newLine();
        $this->line('3. Parameter Kriptografis Rantai Hash:');
        $this->line("   - Blok Genesis (Pertama) : ID #{$firstLog->id} [{$firstLog->created_at->format('Y-m-d H:i:s')} WIB]");
        $this->line("     Hash Sebelumnya (Genesis): " . substr($firstLog->hash_sebelumnya, 0, 16) . '...');
        $this->line("   - Blok Terakhir (Head)   : ID #{$lastLog->id} [{$lastLog->created_at->format('Y-m-d H:i:s')} WIB]");
        $this->line("     Hash Terkini (Head Hash) : " . $lastLog->hash_ini);

        // 4. Kesimpulan Akhir
        $this->newLine();
        $this->info('==================================================================');
        if ($brokenLinkCount > 0 || $tamperedDataCount > 0) {
            $this->error("❌ AUDIT GAGAL: Terdeteksi Pelanggaran Integritas Log Audit!");
            $this->error("   - Rantai Putus (Broken Links)       : {$brokenLinkCount}");
            $this->error("   - Data Termanipulasi (Tampered Data): {$tamperedDataCount}");
            $this->warn("   Pemberitahuan: Sistem mendeteksi adanya manipulasi atau penghapusan data di level database!");
            $this->info('==================================================================');
            return Command::FAILURE;
        }

        $this->info('🎉 AUDIT SELESAI: 100% RANTAI LOG AUDIT UTUH & KEBAL MANIPULASI!');
        $this->info("   Seluruh {$totalLogs} blok aktivitas tersambung dalam rantai SHA-256 sempurna.");
        $this->info('   Tidak ada baris yang disisipkan, dimodifikasi, ataupun dihapus.');
        $this->info('==================================================================');

        return Command::SUCCESS;
    }
}
