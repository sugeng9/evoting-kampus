# Standar Operasional Prosedur (SOP): Backup & Disaster Recovery
## Sistem E-Voting Kampus Terenkripsi (Komisi Pemilihan Raya Mahasiswa)

Dokumen ini memuat panduan komprehensif terkait jadwal pencadangan otomatis, enkripsi berkas cadangan, kebijakan retensi 7 hari, prosedur restorasi, dan rencana penanggulangan bencana (Disaster Recovery Plan) saat terjadi insiden keamanan atau kerusakan data.

---

## 1. Arsitektur & Kebijakan Cadangan (Backup Architecture)

Sistem cadangan e-voting dirancang dengan prinsip **Zero Trust** dan **Resiliensi Tinggi**:

| Komponen | Spesifikasi / Aturan | Penjelasan Keamanan |
|---|---|---|
| **Frekuensi Cadangan** | Otomatis Harian (02:00 WIB) | Dijalankan oleh Laravel Scheduler (`routes/console.php`). |
| **Algoritma Enkripsi** | AES-256-CBC | Menggunakan kunci 256-bit rahasia (`BACKUP_ENCRYPTION_KEY` atau `APP_KEY`). |
| **Integritas Berkas** | HMAC-SHA256 (64-byte tag) | Mencegah modifikasi data oleh penyerang (*tamper-evident*). |
| **Kompresi Data** | Gzip Level 9 | Mengurangi ukuran berkas hingga 85-90% sebelum dienkripsi. |
| **Masa Retensi** | 7 Hari | Berkas cadangan yang lebih tua dari 7 hari otomatis dihapus (*purged*). |
| **Offsite Storage** | Direktori / Volume Terpisah | Salinan otomatis disimpan di penyimpanan terpisah dari server utama. |

Format penamaan berkas backup:
```text
backup_evoting_kampus_YYYY-MM-DD_HHmmss.sql.gz.enc
```

---

## 2. Perintah Operasional Cadangan & Pemulihan (CLI)

Semua perintah dapat dijalankan langsung di terminal proyek:

### A. Eksekusi Pencadangan Manual
```bash
php artisan backup:run-encrypted
```
**Tindakan:**
1. Mengambil dump seluruh tabel skema & data MySQL.
2. Mengompresi berkas dengan Gzip.
3. Mengenkrpsi data dengan AES-256-CBC dan menyematkan tag HMAC-SHA256.
4. Menyimpan berkas di penyimpanan lokal (`storage/app/backups/`).
5. Menyalin berkas ke lokasi terpisah (*offsite* di `storage/app/offsite_backups/`).
6. Menghapus berkas lokal dan offsite yang telah melewati masa retensi 7 hari.
7. Mencatat log keberhasilan ke `audit_logs`.

---

### B. Uji Integritas & Deteksi Tampering (Recovery Test)
```bash
php artisan backup:test-recovery
```
**Tindakan:**
1. Menguji proses pencadangan dan enkripsi AES-256.
2. Menguji deteksi manipulasi berkas (merusak 1 byte pada berkas dan memverifikasi bahwa HMAC menolak pemulihan dengan `INTEGRITY CHECK FAILED`).
3. Menguji dekripsi dan dekompresi berkas asli yang valid.

---

### C. Pemulihan Database (Restorasi Interaktif)
```bash
# Menampilkan daftar backup yang tersedia untuk dipilih:
php artisan backup:restore

# Atau tentukan berkas secara langsung:
php artisan backup:restore backup_evoting_kampus_2026-10-11_020000.sql.gz.enc --force
```

---

## 3. Konfigurasi Penjadwalan Otomatis (Cron Job Server)

Pada server Linux produksi, daftarkan Cron Job Laravel agar pencadangan harian pukul 02:00 WIB berjalan secara otomatis:

Buka crontab:
```bash
crontab -e
```
Tambahkan satu baris berikut:
```cron
* * * * * cd /var/www/evoting-kampus && php artisan schedule:run >> /dev/null 2>&1
```

*(Pada lingkungan Windows lokal untuk demonstrasi, pengujian dapat dilakukan dengan menjalankan `php artisan schedule:test` atau langsung `php artisan backup:run-encrypted`).*

---

## 4. Konfigurasi Penyimpanan Offsite (Cloud / Terpisah)

Agar berkas cadangan selamat bila fisik server terbakar atau terkena *ransomware* total:
1. Pasang volume penyimpanan jaringan terpisah (misal: AWS S3, Google Cloud Storage, atau disk NFS/SFTP kampus).
2. Atur lokasi *mount* di `.env`:
   ```env
   BACKUP_OFFSITE_PATH=/mnt/remote_backup_storage
   BACKUP_RETENTION_DAYS=7
   ```

---

## 5. Prosedur Tanggap Darurat (Incident Response & Disaster Recovery)

### Skenario 1: Serangan Siber / Manipulasi Data Pemilih
**Gejala:** Terdeteksi ketidakwajaran pada total perolehan suara atau anomali pada tabel `voters` / `ballot_boxes`.

**Langkah Penanganan Darurat:**
1. **Karantina Sistem (Isolasi):**
   Segera aktifkan mode pemeliharaan agar tidak ada request baru yang masuk:
   ```bash
   php artisan down --message="Pemeliharaan sistem darurat KPU Kampus. Silakan tunggu beberapa saat."
   ```
2. **Investigasi Titik Aman (Forensik):**
   Periksa log audit untuk mencari waktu tepat terjadinya serangan:
   ```bash
   php artisan tinker --execute="App\Models\AuditLog::latest()->take(20)->get(['event', 'created_at', 'details']);"
   ```
3. **Pilih Cadangan Bersih Terakhir:**
   Pilih berkas cadangan yang dibuat sebelum waktu serangan terdeteksi.
4. **Eksekusi Pemulihan Database:**
   ```bash
   php artisan backup:restore nama_berkas_bersih.sql.gz.enc --force
   ```
5. **Verifikasi Integritas Pasca-Restorasi:**
   Pastikan jumlah pemilih terdaftar, suara masuk, dan lencana partisipasi sinkron:
   ```bash
   php artisan tinker --execute="echo 'Total DPT: ' . App\Models\Voter::count() . ' | Total Suara: ' . App\Models\BallotBox::count();"
   ```
6. **Buka Kembali Layanan:**
   ```bash
   php artisan up
   ```

---

### Skenario 2: Kerusakan Total Server (Hardware Failure / Rebuild Baru)
**Gejala:** Server fisik rusak, sistem operasi hangus, atau mesin virtual hilang.

**Langkah Penanganan Darurat:**
1. Siapkan mesin server baru (Linux Ubuntu / Debian).
2. *Clone* kode sumber repositori dari Git.
3. Siapkan file `.env` baru dan pastikan nilai `APP_KEY` sama persis dengan server lama (atau masukkan `BACKUP_ENCRYPTION_KEY` yang disimpan panitia di tempat aman).
4. Ambil berkas `.sql.gz.enc` terbaru dari Penyimpanan Offsite (S3 / Flashdisk Panitia KPU).
5. Letakkan berkas di folder `storage/app/backups/`.
6. Jalankan migrasi dasar dan restorasi:
   ```bash
   php artisan migrate
   php artisan backup:restore
   ```
7. Sistem kembali online dengan data utuh dalam waktu kurang dari 5 menit.

---

### Skenario 3: Pemulihan Manual Darurat (Tanpa Framework Laravel)

Jika aplikasi Laravel mengalami kendala kode dan panitia harus membuka berkas SQL mentah menggunakan alat CLI standar Linux/macOS/Git Bash:

1. Ambil berkas terenkripsi (misal: `backup.sql.gz.enc`).
2. Pisahkan IV (16 bytes) dan Ciphertext (mulai byte ke-81):
   ```bash
   # Simpan IV (byte 65 s.d 80)
   tail -c +65 backup.sql.gz.enc | head -c 16 > iv.bin

   # Simpan Ciphertext (mulai byte 81 sampai selesai)
   tail -c +81 backup.sql.gz.enc > cipher.bin
   ```
3. Dekripsi dengan OpenSSL menggunakan kunci SHA-256 dari `APP_KEY`:
   ```bash
   openssl enc -d -aes-256-cbc -in cipher.bin -out dump.sql.gz -K <HEX_KEY_32BYTES> -iv $(xxd -p -c 16 iv.bin)
   ```
4. Dekompresi dan impor langsung ke MySQL:
   ```bash
   gunzip -c dump.sql.gz | mysql -u root -p evoting_kampus
   ```
