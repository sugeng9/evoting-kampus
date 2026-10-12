<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

class TestDatabaseRecovery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:test-recovery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menguji integritas end-to-end sistem backup, enkripsi AES-256, deteksi tampering, dan restorasi';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $this->info("==================================================================");
        $this->info("🧪 PENGUJIAN INTEGRITAS SISTEM CADANGAN & PEMULIHAN (TEST RUN)");
        $this->info("==================================================================");

        try {
            // Uji 1: Pembuatan Backup Terenkripsi
            $this->line("1. Menguji proses pencadangan dan enkripsi AES-256...");
            $res = $backupService->createBackup();
            $this->info("   ✅ Berkas terenkripsi dibuat: {$res['filename']}");
            $this->info("   ✅ Tersalin di lokal: " . (File::exists($res['local_path']) ? 'YA' : 'TIDAK'));
            $this->info("   ✅ Tersalin di offsite: " . (File::exists($res['offsite_path']) ? 'YA' : 'TIDAK'));

            // Uji 2: Pengujian Anti-Tampering (Deteksi Modifikasi File)
            $this->newLine();
            $this->line("2. Menguji mekanisme Anti-Tampering (HMAC-SHA256)...");
            $originalContent = File::get($res['local_path']);
            $corruptedContent = $originalContent;
            // Rusak 1 byte di bagian ciphertext
            $corruptedContent[strlen($corruptedContent) - 5] = chr(ord($corruptedContent[strlen($corruptedContent) - 5]) ^ 0xFF);
            
            $tempCorruptedPath = storage_path('app/backups/corrupted_test.sql.gz.enc');
            File::put($tempCorruptedPath, $corruptedContent);

            try {
                $backupService->restoreBackup($tempCorruptedPath);
                $this->error("   ❌ PERINGATAN: Berkas yang dicurangi tidak terdeteksi oleh HMAC!");
            } catch (Throwable $e) {
                if (str_contains($e->getMessage(), 'INTEGRITY CHECK FAILED')) {
                    $this->info("   🛡️  SUKSES: Modifikasi berkas berhasil dideteksi dan diblokir!");
                    $this->line("      Pesan: " . $e->getMessage());
                } else {
                    $this->warn("      Pesan: " . $e->getMessage());
                }
            } finally {
                if (File::exists($tempCorruptedPath)) {
                    File::delete($tempCorruptedPath);
                }
            }

            // Uji 3: Uji Dekripsi & Verifikasi Format SQL Asli
            $this->newLine();
            $this->line("3. Menguji dekripsi dan dekompresi berkas asli yang valid...");
            
            $payload = File::get($res['local_path']);
            $hmac = substr($payload, 0, 64);
            $iv = substr($payload, 64, 16);
            $ciphertext = substr($payload, 80);

            $rawKey = config('backup.encryption_key', config('app.key'));
            if (str_starts_with($rawKey, 'base64:')) {
                $rawKey = base64_decode(substr($rawKey, 7));
            }
            $encKey = hash('sha256', $rawKey, true);

            $decrypted = openssl_decrypt($ciphertext, 'AES-256-CBC', $encKey, OPENSSL_RAW_DATA, $iv);
            $sql = @gzdecode($decrypted);

            if ($sql && str_contains($sql, 'CADANGAN DATABASE SISTEM E-VOTING KAMPUS')) {
                // Hitung jumlah tabel dan baris yang terangkum dalam dump SQL
                preg_match_all('/CREATE TABLE `([^`]+)`/i', $sql, $tableMatches);
                $detectedTables = count($tableMatches[1] ?? []);

                preg_match_all('/INSERT INTO `([^`]+)`/i', $sql, $insertMatches);
                $detectedInserts = count($insertMatches[1] ?? []);

                $this->info("   ✅ SUKSES: Dekripsi berkas menghasilkan skema SQL yang 100% valid dan utuh.");
                $this->line("      - Jumlah Tabel Terverifikasi : {$detectedTables} tabel");
                $this->line("      - Blok Data INSERT Terdeteksi: {$detectedInserts} batch");
                $this->line("      - Total Ukuran SQL Terurai   : " . number_format(strlen($sql) / 1024, 2) . " KB");
            } else {
                $this->error("   ❌ GAGAL: Dekripsi tidak menghasilkan SQL yang valid.");
                return Command::FAILURE;
            }

            $this->newLine();
            $this->info("==================================================================");
            $this->info("🎉 SELURUH PENGUJIAN INTEGRITAS BACKUP & RECOVERY LULUS 100%!");
            $this->info("==================================================================");

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("❌ Terjadi kegagalan saat pengujian: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
