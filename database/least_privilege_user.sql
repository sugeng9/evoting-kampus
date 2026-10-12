-- ============================================================================
-- SKRIP PENGUATAN KEAMANAN MYSQL: PRINSIP HAK AKSES MINIMAL (LEAST PRIVILEGE)
-- SISTEM E-VOTING KAMPUS - KOMISI PEMILIHAN RAYA MAHASISWA
-- ============================================================================
--
-- TUJUAN:
-- Memastikan bahwa akun database yang digunakan oleh aplikasi web Laravel
-- HANYA memiliki hak Data Manipulation Language (DML) yang esensial.
--
-- JIKA TERJADI CELAH PADA APLIKASI (ZERO-DAY / INJECTION):
-- Penyerang TETAP TIDAK BISA:
-- 1. Menghapus tabel atau database (DILARANG: DROP)
-- 2. Mengubah struktur kolom/skema (DILARANG: ALTER)
-- 3. Membuat tabel backdoor/temp (DILARANG: CREATE)
-- 4. Membaca file arbitrer sistem operasi server (DILARANG: FILE / LOAD_FILE)
-- 5. Menulis webshell ke disk (DILARANG: INTO OUTFILE / DUMPFILE)
-- 6. Memberikan hak ke user lain (DILARANG: GRANT OPTION / SUPER)
-- ============================================================================

-- Langkah 1: Buat Database jika belum ada (gunakan utf8mb4)
CREATE DATABASE IF NOT EXISTS `evoting_kampus` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Langkah 2: Buat User Khusus Aplikasi (Ganti password sebelum deploy ke produksi!)
CREATE USER IF NOT EXISTS 'evoting_app'@'localhost' IDENTIFIED BY 'Ev0t1ng_S3cur3_App_2026!#';
CREATE USER IF NOT EXISTS 'evoting_app'@'127.0.0.1' IDENTIFIED BY 'Ev0t1ng_S3cur3_App_2026!#';

-- Langkah 3: Cabut Semua Hak Terlebih Dahulu (Sanitasi Total)
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'evoting_app'@'localhost';
REVOKE ALL PRIVILEGES, GRANT OPTION FROM 'evoting_app'@'127.0.0.1';

-- Langkah 4: Berikan HANYA Hak DML Esensial ke User Aplikasi (SELECT, INSERT, UPDATE, DELETE, LOCK TABLES)
GRANT SELECT, INSERT, UPDATE, DELETE, LOCK TABLES 
ON `evoting_kampus`.* 
TO 'evoting_app'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE, LOCK TABLES 
ON `evoting_kampus`.* 
TO 'evoting_app'@'127.0.0.1';

-- Langkah 5: Buat User Khusus Migrasi & Maintenance Skema (DDL + DML)
-- User ini hanya digunakan saat deployment / menjalankan 'php artisan migrate' oleh DevOps
CREATE USER IF NOT EXISTS 'evoting_migration'@'localhost' IDENTIFIED BY 'Ev0t1ng_M1gr4t1on_2026!#';
CREATE USER IF NOT EXISTS 'evoting_migration'@'127.0.0.1' IDENTIFIED BY 'Ev0t1ng_M1gr4t1on_2026!#';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX, REFERENCES, CREATE TEMPORARY TABLES, LOCK TABLES 
ON `evoting_kampus`.* 
TO 'evoting_migration'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX, REFERENCES, CREATE TEMPORARY TABLES, LOCK TABLES 
ON `evoting_kampus`.* 
TO 'evoting_migration'@'127.0.0.1';

-- Langkah 6: Terapkan Perubahan Hak Akses ke Memori MySQL
FLUSH PRIVILEGES;

-- ============================================================================
-- CARA VERIFIKASI HAK AKSES (Jalankan di MySQL Client):
-- ============================================================================
-- SHOW GRANTS FOR 'evoting_app'@'localhost';
-- SHOW GRANTS FOR 'evoting_migration'@'localhost';
--
-- Hasil yang diharapkan:
-- 1. 'evoting_app' HANYA memiliki SELECT, INSERT, UPDATE, DELETE, LOCK TABLES (Aman dari DDL injection).
-- 2. 'evoting_migration' memiliki hak DDL (CREATE, DROP, ALTER, dll.) untuk migrasi skema.
-- ============================================================================
