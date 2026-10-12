<?php

namespace App\Services;

use App\Models\AuditLog;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDO;
use Throwable;

class DatabaseBackupService
{
    protected string $localPath;
    protected string $offsitePath;
    protected int $retentionDays;
    protected string $encryptionKey;
    protected string $cipher;

    public function __construct()
    {
        $this->localPath = config('backup.local_path', storage_path('app/backups'));
        $this->offsitePath = config('backup.offsite_path', storage_path('app/offsite_backups'));
        $this->retentionDays = (int) config('backup.retention_days', 7);
        $this->cipher = config('backup.cipher', 'AES-256-CBC');

        $rawKey = config('backup.encryption_key', config('app.key'));
        if (str_starts_with($rawKey, 'base64:')) {
            $rawKey = base64_decode(substr($rawKey, 7));
        }
        $this->encryptionKey = hash('sha256', $rawKey, true); // Pastikan tepat 32 bytes untuk AES-256

        $this->ensureDirectories();
    }

    /**
     * Pastikan direktori backup lokal dan offsite tersedia.
     */
    protected function ensureDirectories(): void
    {
        if (!File::isDirectory($this->localPath)) {
            File::makeDirectory($this->localPath, 0750, true, true);
        }
        if (!File::isDirectory($this->offsitePath)) {
            File::makeDirectory($this->offsitePath, 0750, true, true);
        }
    }

    /**
     * Eksekusi pencadangan database: Dump -> Gzip -> Enkripsi AES-256 -> Simpan -> Retensi 7 Hari
     */
    public function createBackup(): array
    {
        $database = config('database.connections.mysql.database', 'evoting_kampus');
        $timestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d_His');
        $baseFilename = "backup_{$database}_{$timestamp}.sql.gz.enc";
        $localFile = $this->localPath . DIRECTORY_SEPARATOR . $baseFilename;
        $offsiteFile = $this->offsitePath . DIRECTORY_SEPARATOR . $baseFilename;

        // 1. Buat SQL Dump Database
        $dumpResult = $this->dumpDatabase();
        $rawSql = $dumpResult['sql'];
        $rawSize = strlen($rawSql);

        // 2. Kompresi dengan Gzip
        $compressed = gzencode($rawSql, 9);
        if ($compressed === false) {
            throw new Exception("Gagal melakukan kompresi Gzip pada data dump.");
        }
        $compressedSize = strlen($compressed);

        // 3. Enkripsi dengan AES-256-CBC & HMAC-SHA256
        $iv = random_bytes(16);
        $ciphertext = openssl_encrypt($compressed, $this->cipher, $this->encryptionKey, OPENSSL_RAW_DATA, $iv);
        if ($ciphertext === false) {
            throw new Exception("Gagal mengenkripsi backup: " . openssl_error_string());
        }

        // Hitung HMAC untuk mencegah manipulasi/tampering (HMAC-SHA256)
        $hmac = hash_hmac('sha256', $iv . $ciphertext, $this->encryptionKey);

        // Struktur berkas terenkripsi: [64 bytes hex HMAC][16 bytes raw IV][Ciphertext]
        $payload = $hmac . $iv . $ciphertext;

        // 4. Simpan ke direktori lokal
        File::put($localFile, $payload);

        // 5. Salin ke penyimpanan terpisah (Offsite Storage)
        File::put($offsiteFile, $payload);

        $encryptedSize = strlen($payload);

        // 6. Jalankan pembersihan retensi 7 hari
        $cleanedFiles = $this->purgeOldBackups();

        // 7. Catat ke Audit Log
        AuditLog::log('DATABASE_BACKUP_COMPLETED', 'system', null, [
            'filename' => $baseFilename,
            'tables_count' => $dumpResult['tables_count'],
            'records_count' => $dumpResult['records_count'],
            'raw_size_bytes' => $rawSize,
            'encrypted_size_bytes' => $encryptedSize,
            'retention_days' => $this->retentionDays,
            'cleaned_files_count' => count($cleanedFiles),
        ]);

        return [
            'filename' => $baseFilename,
            'local_path' => $localFile,
            'offsite_path' => $offsiteFile,
            'tables_count' => $dumpResult['tables_count'],
            'records_count' => $dumpResult['records_count'],
            'raw_size' => $this->formatBytes($rawSize),
            'compressed_size' => $this->formatBytes($compressedSize),
            'encrypted_size' => $this->formatBytes($encryptedSize),
            'hmac' => $hmac,
            'created_at' => now()->timezone('Asia/Jakarta')->toDateTimeString() . ' WIB',
            'cleaned_files' => $cleanedFiles,
        ];
    }

    /**
     * Restore database dari berkas backup terenkripsi (.sql.gz.enc)
     */
    public function restoreBackup(string $filepath): array
    {
        if (!File::exists($filepath)) {
            throw new Exception("Berkas backup tidak ditemukan di: {$filepath}");
        }

        $payload = File::get($filepath);
        if (strlen($payload) < 80) { // 64 bytes HMAC + 16 bytes IV
            throw new Exception("Format berkas backup rusak atau tidak valid.");
        }

        // 1. Ekstrak HMAC, IV, dan Ciphertext
        $hmac = substr($payload, 0, 64);
        $iv = substr($payload, 64, 16);
        $ciphertext = substr($payload, 80);

        // 2. Verifikasi Integritas (Anti-Tampering HMAC)
        $expectedHmac = hash_hmac('sha256', $iv . $ciphertext, $this->encryptionKey);
        if (!hash_equals($expectedHmac, $hmac)) {
            throw new Exception("INTEGRITY CHECK FAILED: HMAC berkas backup tidak cocok! Berkas backup mungkin telah diubah, rusak, atau dicurangi.");
        }

        // 3. Dekripsi AES-256-CBC
        $compressed = openssl_decrypt($ciphertext, $this->cipher, $this->encryptionKey, OPENSSL_RAW_DATA, $iv);
        if ($compressed === false) {
            throw new Exception("Gagal mendekripsi berkas backup: Kunci enkripsi tidak valid atau data korup.");
        }

        // 4. Dekompresi Gzip
        $rawSql = @gzdecode($compressed);
        if ($rawSql === false) {
            throw new Exception("Gagal melakukan dekompresi data SQL setelah dekripsi.");
        }

        // 5. Eksekusi Restorasi SQL ke Database
        $pdo = DB::connection()->getPdo();
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

        // Pecah per pernyataan SQL dan eksekusi
        $queries = array_filter(
            array_map('trim', explode(";\n", $rawSql)),
            fn($q) => !empty($q) && !str_starts_with($q, '--')
        );

        $executedCount = 0;
        foreach ($queries as $query) {
            $clean = trim($query);
            if (!empty($clean)) {
                $pdo->exec($clean);
                $executedCount++;
            }
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

        AuditLog::log('DATABASE_RESTORE_COMPLETED', 'admin', auth()->id(), [
            'filepath' => $filepath,
            'queries_executed' => $executedCount,
            'restored_size' => strlen($rawSql),
        ]);

        return [
            'success' => true,
            'filepath' => $filepath,
            'queries_executed' => $executedCount,
            'raw_size' => $this->formatBytes(strlen($rawSql)),
            'restored_at' => now()->timezone('Asia/Jakarta')->toDateTimeString() . ' WIB',
        ];
    }

    /**
     * Dump Skema dan Data Tabel Database (100% Native PHP PDO - Berjalan di semua OS tanpa ketergantungan mysqldump)
     */
    protected function dumpDatabase(): array
    {
        $pdo = DB::connection()->getPdo();
        $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);

        $sql = "-- ====================================================================\n";
        $sql .= "-- CADANGAN DATABASE SISTEM E-VOTING KAMPUS (TERENKRIPSI)\n";
        $sql .= "-- Dibuat pada: " . now()->timezone('Asia/Jakarta')->toDateTimeString() . " WIB\n";
        $sql .= "-- ====================================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";

        $totalRecords = 0;

        foreach ($tables as $table) {
            // 1. Skema Tabel
            $createTableStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            $createSql = $createTableStmt['Create Table'] ?? '';

            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Struktur Tabel: `{$table}`\n";
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createSql . ";\n\n";

            // 2. Data Tabel
            $rowsStmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $rowsStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $sql .= "-- Data Tabel: `{$table}` (" . count($rows) . " baris)\n";
                $columns = array_keys($rows[0]);
                $columnList = implode('`, `', $columns);

                foreach (array_chunk($rows, 100) as $chunk) {
                    $sql .= "INSERT INTO `{$table}` (`{$columnList}`) VALUES\n";
                    $valueLines = [];

                    foreach ($chunk as $row) {
                        $values = [];
                        foreach ($row as $val) {
                            if (is_null($val)) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = $pdo->quote($val);
                            }
                        }
                        $valueLines[] = "(" . implode(', ', $values) . ")";
                        $totalRecords++;
                    }

                    $sql .= implode(",\n", $valueLines) . ";\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return [
            'sql' => $sql,
            'tables_count' => count($tables),
            'records_count' => $totalRecords,
        ];
    }

    /**
     * Hapus berkas backup lokal & offsite yang lebih tua dari retensi 7 hari
     */
    public function purgeOldBackups(): array
    {
        $cleaned = [];
        $cutoff = now()->subDays($this->retentionDays)->getTimestamp();

        $directories = [$this->localPath, $this->offsitePath];

        foreach ($directories as $dir) {
            if (!File::isDirectory($dir)) {
                continue;
            }

            $files = File::files($dir);
            foreach ($files as $file) {
                if (str_ends_with($file->getFilename(), '.sql.gz.enc') && $file->getMTime() < $cutoff) {
                    $cleaned[] = $file->getPathname();
                    File::delete($file->getPathname());
                }
            }
        }

        return $cleaned;
    }

    /**
     * Mengambil daftar berkas backup yang tersedia
     */
    public function listBackups(): array
    {
        $result = [];
        if (File::isDirectory($this->localPath)) {
            foreach (File::files($this->localPath) as $file) {
                if (str_ends_with($file->getFilename(), '.sql.gz.enc')) {
                    $result[] = [
                        'filename' => $file->getFilename(),
                        'path' => $file->getPathname(),
                        'size' => $this->formatBytes($file->getSize()),
                        'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                        'is_offsite_synced' => File::exists($this->offsitePath . DIRECTORY_SEPARATOR . $file->getFilename()),
                    ];
                }
            }
        }

        // Urutkan dari yang paling baru
        usort($result, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $result;
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
