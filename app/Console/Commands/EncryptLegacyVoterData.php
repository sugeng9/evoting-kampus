<?php

namespace App\Console\Commands;

use App\Models\Voter;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class EncryptLegacyVoterData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:encrypt-legacy-data {--skip-backup : Lewati pencadangan otomatis sebelum enkripsi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enkripsi data sensitif lama (email, nomor HP, tanggal lahir) yang masih plaintext dan buat blind index email_hash (Idempotent & Aman)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("==================================================================");
        $this->info("🔐  ENKRIPSI DATA SENSITIF LAMA & PEMBUATAN BLIND INDEX");
        $this->info("==================================================================");

        // 1. WAJIB BACKUP DATABASE TERLEBIH DAHULU
        if (! $this->option('skip-backup')) {
            $this->line("1. Menjalankan pencadangan database terenkripsi sebelum memproses data...");
            try {
                $backupService = new DatabaseBackupService();
                $backupResult = $backupService->createBackup();
                $this->info("   ✅ Pencadangan otomatis sukses! Berkas: {$backupResult['filename']}");
            } catch (Throwable $e) {
                $this->error("   ❌ GAGAL melakukan backup otomatis: " . $e->getMessage());
                if (! $this->confirm("Apakah Anda ingin tetap melanjutkan enkripsi TANPA backup?", false)) {
                    $this->warn("Operasi dibatalkan demi keamanan data.");
                    return Command::FAILURE;
                }
            }
        } else {
            $this->warn("1. Pencadangan database dilewati (--skip-backup).");
        }

        // 2. Pastikan kolom email_hash tersedia di tabel voters
        $this->line("2. Memeriksa ketersediaan kolom blind index 'email_hash'...");
        try {
            if (! Schema::hasColumn('voters', 'email_hash')) {
                Schema::table('voters', function ($table) {
                    $table->string('email_hash', 64)->nullable()->index()->after('email');
                });
                $this->info("   ✅ Kolom 'email_hash' berhasil ditambahkan ke tabel 'voters'.");
            } else {
                $this->info("   ✅ Kolom 'email_hash' sudah siap.");
            }
        } catch (Throwable $e) {
            $this->warn("   ⚠️ Peringatan skema: " . $e->getMessage());
        }

        // 3. Proses Enkripsi Data Lama secara Idempotent
        $this->line("3. Memindai data pemilih di database...");
        $voters = DB::table('voters')->get();
        $total = $voters->count();

        if ($total === 0) {
            $this->info("   ℹ️  Tidak ada data pemilih di tabel 'voters'.");
            return Command::SUCCESS;
        }

        $encryptedCount = 0;
        $alreadyEncryptedCount = 0;
        $blindIndexCount = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($voters as $row) {
            $updates = [];
            $plainEmail = null;

            // Periksa email
            if (! empty($row->email)) {
                try {
                    $plainEmail = Crypt::decryptString($row->email);
                    $alreadyEncryptedCount++;
                } catch (Throwable $e) {
                    // Masih plaintext -> Enkripsi!
                    $plainEmail = $row->email;
                    $updates['email'] = Crypt::encryptString($plainEmail);
                    $encryptedCount++;
                }
            }

            // Periksa nomor HP
            if (! empty($row->no_hp)) {
                try {
                    Crypt::decryptString($row->no_hp);
                } catch (Throwable $e) {
                    $updates['no_hp'] = Crypt::encryptString($row->no_hp);
                }
            }

            // Periksa tanggal lahir / dob
            if (! empty($row->tanggal_lahir)) {
                try {
                    Crypt::decryptString($row->tanggal_lahir);
                } catch (Throwable $e) {
                    $updates['tanggal_lahir'] = Crypt::encryptString($row->tanggal_lahir);
                }
            }

            if (! empty($row->dob)) {
                try {
                    Crypt::decryptString($row->dob);
                } catch (Throwable $e) {
                    $updates['dob'] = Crypt::encryptString($row->dob);
                }
            }

            // Hitung Blind Index email_hash (HMAC-SHA256)
            if (! empty($plainEmail)) {
                $calculatedHash = Voter::hashBlindIndex($plainEmail);
                if ($row->email_hash !== $calculatedHash) {
                    $updates['email_hash'] = $calculatedHash;
                    $blindIndexCount++;
                }
            }

            if (! empty($updates)) {
                DB::table('voters')->where('id', $row->id)->update($updates);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("==================================================================");
        $this->info("🎉 ENKRIPSI & GENERASI BLIND INDEX SELESAI!");
        $this->info("==================================================================");
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Total Baris Dipindai', $total],
                ['Kolom Plaintext Baru Dienkripsi', $encryptedCount],
                ['Kolom Sudah Terenkripsi (Idempotent)', $alreadyEncryptedCount],
                ['Blind Index (email_hash) Dibuat/Diperbarui', $blindIndexCount],
            ]
        );

        return Command::SUCCESS;
    }
}
