<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class RunDatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:run-encrypted';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan pencadangan database terenkripsi (AES-256) dengan retensi 7 hari & offsite mirror';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $this->info("==================================================================");
        $this->info("📦 PENCADANGAN DATABASE TERENKRIPSI (E-VOTING KAMPUS)");
        $this->info("==================================================================");

        try {
            $this->line("1. Memulai proses dump, kompresi Gzip, dan enkripsi AES-256-CBC...");
            $res = $backupService->createBackup();

            $this->info("   ✅ Pencadangan selesai dengan sukses!");
            $this->line("   - Berkas       : {$res['filename']}");
            $this->line("   - Jumlah Tabel : {$res['tables_count']} tabel");
            $this->line("   - Total Baris  : {$res['records_count']} baris data");
            $this->line("   - Ukuran Asli  : {$res['raw_size']}");
            $this->line("   - Ukuran Gzip  : {$res['compressed_size']}");
            $this->line("   - Terenkripsi  : {$res['encrypted_size']}");
            $this->line("   - Lokasi Lokal : {$res['local_path']}");
            $this->line("   - Offsite Copy : {$res['offsite_path']}");
            $this->line("   - Checksum HMAC: " . substr($res['hmac'], 0, 16) . "..." . substr($res['hmac'], -16));

            $this->newLine();
            $this->line("2. Menjalankan pemeriksaan retensi 7 hari...");
            if (empty($res['cleaned_files'])) {
                $this->info("   ✅ Retensi aman: Tidak ada berkas backup yang melampaui batas 7 hari.");
            } else {
                $this->info("   🧹 Berhasil menghapus " . count($res['cleaned_files']) . " berkas backup kedaluwarsa (> 7 hari).");
                foreach ($res['cleaned_files'] as $f) {
                    $this->line("      - Terhapus: " . basename($f));
                }
            }

            $this->newLine();
            $this->info("==================================================================");
            $this->info("🎉 DATABASE BERHASIL DICADANGKAN DENGAN AMAN & TERENKRIPSI!");
            $this->info("==================================================================");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("❌ Gagal mencadangkan database: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
