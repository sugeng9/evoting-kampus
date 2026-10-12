# Panduan Implementasi MySQL Least-Privilege User (E-Voting Kampus)

Dokumen ini menjelaskan implementasi prinsip **Least Privilege** (*Hak Akses Minimal*) pada database MySQL untuk sistem E-Voting Kampus.

---

## 1. Mengapa Prinsip Least Privilege Sangat Penting?

Secara default, instalasi lokal sering kali menggunakan user `root` dengan hak penuh (`ALL PRIVILEGES`). Pada sistem pemilihan umum kampus, hal ini merupakan risiko fatal:

1. **Pencegahan Penghancuran Data (Anti-Destruction):** Bila aplikasi memiliki celah atau kredensial database bocor, penyerang tidak dapat mengeksekusi `DROP TABLE`, `DROP DATABASE`, atau `TRUNCATE`.
2. **Pencegahan Perubahan Skema (Anti-Defacement):** Penyerang tidak bisa menjalankan `ALTER TABLE` untuk mematikan kolom foreign key, menghapus constraint unik, atau menambahkan backdoor.
3. **Pencegahan Remote Code Execution (Anti-RCE):** Tanpa hak `FILE`, perintah injeksi seperti `INTO OUTFILE` (menulis web shell PHP) atau `LOAD_FILE` (membaca `/etc/passwd` atau file `.env`) akan otomatis ditolak oleh mesin MySQL.

---

## 2. Pemisahan Peran Database (Separation of Roles)

| Peran | Pengguna Database | Hak Akses (Privileges) | Digunakan Untuk |
|---|---|---|---|
| **Aplikasi Web (Runtime)** | `evoting_app` | `SELECT, INSERT, UPDATE, DELETE, LOCK TABLES` | Akses web sehari-hari (login, voting, quick count, helpdesk) |
| **Migrasi / DevOps (CLI)** | `evoting_admin` / `root` | `ALL PRIVILEGES` / `CREATE, ALTER, DROP, INDEX` | Dijalankan oleh panitia IT hanya saat `php artisan migrate` via terminal server |

---

## 3. Langkah Instalasi di Server MySQL

### Langkah A: Eksekusi Skrip SQL
Login ke MySQL sebagai `root`:
```bash
mysql -u root -p
```

Jalankan perintah SQL dari berkas `database/least_privilege_user.sql`:
```sql
SOURCE /var/www/evoting-kampus/database/least_privilege_user.sql;
```
*(Atau salin isi skrip ke MySQL prompt).*

### Langkah B: Perbarui Berkas `.env`
Buka berkas `.env` dan arahkan koneksi runtime ke user `evoting_app`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=evoting_kampus
DB_USERNAME=evoting_app
DB_PASSWORD=Ev0t1ng_S3cur3_App_2026!#
```

Kemudian bersihkan config cache:
```bash
php artisan config:clear
```

---

## 4. Prosedur Pengujian Hak Akses (Uji Penetrasi Database)

Uji apakah pembatasan hak akses bekerja dengan benar:

### Uji 1: Operasi DML yang Diizinkan (Harus Berhasil)
```bash
mysql -u evoting_app -p -e "SELECT id, name FROM evoting_kampus.election_categories LIMIT 1;"
```
> **Hasil yang diharapkan:** Query berhasil dieksekusi dan menampilkan data.

### Uji 2: Upaya DROP TABLE (Harus Ditolak / Dilarang)
```bash
mysql -u evoting_app -p -e "DROP TABLE evoting_kampus.audit_logs;"
```
> **Hasil yang diharapkan:**
> ```
> ERROR 1142 (42000): DROP command denied to user 'evoting_app'@'localhost' for table 'audit_logs'
> ```

### Uji 3: Upaya ALTER TABLE (Harus Ditolak / Dilarang)
```bash
mysql -u evoting_app -p -e "ALTER TABLE evoting_kampus.voters DROP COLUMN password;"
```
> **Hasil yang diharapkan:**
> ```
> ERROR 1142 (42000): ALTER command denied to user 'evoting_app'@'localhost' for table 'voters'
> ```

### Uji 4: Upaya Penulisan File / Webshell (Harus Ditolak / Dilarang)
```bash
mysql -u evoting_app -p -e "SELECT '<?php phpinfo(); ?>' INTO OUTFILE '/var/www/html/shell.php';"
```
> **Hasil yang diharapkan:**
> ```
> ERROR 1045 (28000): Access denied for user 'evoting_app'@'localhost' (using password: YES)
> ```

---

## 5. Menjalankan Migrasi Database di Masa Depan

Bila panitia menambahkan migrasi skema baru di masa depan:
Jalankan migrasi menggunakan user administratif via perintah:
```bash
DB_USERNAME=root DB_PASSWORD=PasswordRootServer php artisan migrate
```
Dengan demikian, aplikasi web yang berjalan di browser tetap 100% terkunci di bawah user `evoting_app` yang aman.
