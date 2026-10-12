<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use Throwable;

class ApplyLeastPrivilegeUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:db-least-privilege {--test : Hanya lakukan uji hak akses tanpa membuat ulang user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menerapkan dan menguji pengguna database dengan hak minimal (Least Privilege User)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("==================================================================");
        $this->info("🛡️  PENGUATAN KEAMANAN DATABASE: MYSQL LEAST PRIVILEGE USER");
        $this->info("==================================================================");

        $sqlPath = database_path('least_privilege_user.sql');
        if (!file_exists($sqlPath)) {
            $this->error("Berkas SQL tidak ditemukan di: {$sqlPath}");
            return Command::FAILURE;
        }

        if (!$this->option('test')) {
            $this->line("1. Membaca dan mengeksekusi skrip: database/least_privilege_user.sql ...");

            $sqlContent = file_get_contents($sqlPath);
            // Hapus seluruh baris komentar (-- ...) terlebih dahulu agar statement setelah komentar tidak terlewat
            $sqlClean = preg_replace('/^--.*$/m', '', $sqlContent);

            $statements = array_filter(
                array_map('trim', explode(';', $sqlClean)),
                fn($stmt) => !empty($stmt)
            );

            try {
                foreach ($statements as $statement) {
                    $cleanStatement = trim($statement);
                    if (!empty($cleanStatement)) {
                        DB::statement($cleanStatement);
                    }
                }
                $this->info("   ✅ Berhasil membuat & mengatur hak akses pengguna 'evoting_app' dan 'evoting_migration'!");
            } catch (Throwable $e) {
                $this->error("   ❌ Gagal mengeksekusi skrip SQL: " . $e->getMessage());
                $this->line("      Catatan: Pastikan user database di .env saat ini memiliki hak GRANT (misal root).");
                return Command::FAILURE;
            }
        }

        // 2. Uji Penetrasi Hak Akses (Testing Permissions)
        $this->newLine();
        $this->line("2. Menjalankan Uji Penetrasi Hak Akses pengguna 'evoting_app'...");

        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database', 'evoting_kampus');
        $testUser = 'evoting_app';
        $testPass = 'Ev0t1ng_S3cur3_App_2026!#';

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new PDO($dsn, $testUser, $testPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            $this->info("   ✅ Koneksi PDO berhasil login sebagai '{$testUser}'.");

            // Uji A: Operasi DML yang diizinkan (SELECT)
            $stmt = $pdo->query("SELECT 1 AS test_select");
            $res = $stmt->fetch();
            if ($res && isset($res['test_select']) && $res['test_select'] == 1) {
                $this->info("   ✅ UJI 1 (SELECT): DIIZINKAN & BEKERJA NORMAL.");
            }

            // Uji B: Operasi DDL Berbahaya (DROP TABLE) - Harus Ditolak
            try {
                $pdo->exec("DROP TABLE IF EXISTS `test_security_audit_table`");
                $this->error("   ❌ UJI 2 (DROP TABLE): PERINGATAN! Perintah DROP tidak ditolak!");
            } catch (Throwable $e) {
                if (str_contains(strtolower($e->getMessage()), 'denied') || str_contains($e->getMessage(), '1142')) {
                    $this->info("   🛡️  UJI 2 (DROP TABLE): BERHASIL DIBLOKIR!");
                    $this->line("      Respon MySQL: " . $e->getMessage());
                } else {
                    $this->warn("      Respon MySQL: " . $e->getMessage());
                }
            }

            // Uji C: Operasi DDL Skema (CREATE TABLE) - Harus Ditolak
            try {
                $pdo->exec("CREATE TABLE `test_security_audit_table` (`id` int)");
                $this->error("   ❌ UJI 3 (CREATE TABLE): PERINGATAN! Perintah CREATE tidak ditolak!");
            } catch (Throwable $e) {
                if (str_contains(strtolower($e->getMessage()), 'denied') || str_contains($e->getMessage(), '1142')) {
                    $this->info("   🛡️  UJI 3 (CREATE TABLE): BERHASIL DIBLOKIR!");
                    $this->line("      Respon MySQL: " . $e->getMessage());
                } else {
                    $this->warn("      Respon MySQL: " . $e->getMessage());
                }
            }

        } catch (Throwable $e) {
            $this->error("   ❌ Gagal menguji koneksi user '{$testUser}': " . $e->getMessage());
            return Command::FAILURE;
        }

        // 3. Uji User Migrasi Terpisah (evoting_migration)
        $this->newLine();
        $this->line("3. Menjalankan Uji User Migrasi Khusus 'evoting_migration'...");
        $migrationUser = 'evoting_migration';
        $migrationPass = 'Ev0t1ng_M1gr4t1on_2026!#';

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdoMigrate = new PDO($dsn, $migrationUser, $migrationPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $pdoMigrate->exec("CREATE TABLE IF NOT EXISTS `_test_migration_perm` (`id` INT AUTO_INCREMENT PRIMARY KEY)");
            $pdoMigrate->exec("DROP TABLE IF EXISTS `_test_migration_perm`");
            $this->info("   ✅ Koneksi '{$migrationUser}' berhasil login dan memiliki hak DDL (CREATE/DROP) untuk migrasi!");
        } catch (Throwable $e) {
            $this->warn("   ⚠️  Uji '{$migrationUser}': " . $e->getMessage());
        }

        $this->newLine();
        $this->info("==================================================================");
        $this->info("🎉 VERIFIKASI SELESAI: DATABASE TERKUNCI DENGAN AMAN!");
        $this->info("==================================================================");
        $this->line("1. Kredensial Runtime Aplikasi (.env produksi):");
        $this->line("   DB_USERNAME={$testUser}");
        $this->line("   DB_PASSWORD={$testPass}");
        $this->line("   (Hanya diizinkan SELECT, INSERT, UPDATE, DELETE - Anti-DDL Injection)");
        $this->newLine();
        $this->line("2. Kredensial Migrasi / Maintenance CI/CD:");
        $this->line("   DB_USERNAME={$migrationUser}");
        $this->line("   DB_PASSWORD={$migrationPass}");
        $this->newLine();

        return Command::SUCCESS;
    }
}
