<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class RestoreDatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:restore {file? : Nama berkas atau path berkas backup terenkripsi} {--force : Lewati konfirmasi interaktif}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memulihkan (Restore) database dari berkas backup terenkripsi (.sql.gz.enc)';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $this->info("==================================================================");
        $this->info("♻️  PEMULIHAN (RESTORE) DATABASE E-VOTING KAMPUS");
        $this->info("==================================================================");

        $fileArg = $this->argument('file');
        $targetPath = null;

        if ($fileArg) {
            if (file_exists($fileArg)) {
                $targetPath = $fileArg;
            } else {
                $localCandidate = config('backup.local_path') . DIRECTORY_SEPARATOR . $fileArg;
                $offsiteCandidate = config('backup.offsite_path') . DIRECTORY_SEPARATOR . $fileArg;

                if (file_exists($localCandidate)) {
                    $targetPath = $localCandidate;
                } elseif (file_exists($offsiteCandidate)) {
                    $targetPath = $offsiteCandidate;
                }
            }

            if (!$targetPath) {
                $this->error("Berkas backup tidak ditemukan: {$fileArg}");
                return Command::FAILURE;
            }
        } else {
            $backups = $backupService->listBackups();
            if (empty($backups)) {
                $this->error("Tidak ditemukan berkas backup di direktori lokal (" . config('backup.local_path') . ").");
                return Command::FAILURE;
            }

            $choices = [];
            foreach ($backups as $b) {
                $choices[$b['path']] = "{$b['filename']} ({$b['size']}, dibuat: {$b['created_at']})";
            }

            $selected = $this->choice("Pilih berkas backup yang ingin Anda pulihkan:", array_values($choices), 0);
            $targetPath = array_search($selected, $choices);
        }

        $this->warn("⚠️  PERINGATAN KRITIKAL:");
        $this->warn("Proses ini akan menimpa seluruh skema dan data database saat ini dengan data dari cadangan:");
        $this->line("Target Berkas: {$targetPath}");
        $this->newLine();

        if (!$this->option('force') && !$this->confirm("Apakah Anda YAKIN ingin melanjutkan pemulihan database sekarang?", false)) {
            $this->info("Operasi pemulihan dibatalkan.");
            return Command::SUCCESS;
        }

        try {
            $this->line("1. Memverifikasi HMAC integritas, mendekripsi AES-256-CBC, dan dekompresi...");
            $res = $backupService->restoreBackup($targetPath);

            $this->info("   ✅ Dekripsi dan restorasi query berhasil dieksekusi!");
            $this->line("   - Kueri Dijalankan : {$res['queries_executed']} pernyataan SQL");
            $this->line("   - Ukuran SQL Terurai: {$res['raw_size']}");
            $this->line("   - Waktu Selesai    : {$res['restored_at']}");

            $this->newLine();
            $this->info("==================================================================");
            $this->info("🎉 RESTORASI DATABASE BERHASIL 100%!");
            $this->info("==================================================================");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("❌ Gagal memulihkan database: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
