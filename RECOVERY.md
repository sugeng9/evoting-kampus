# PANDUAN PEMULIHAN BENCANA (DISASTER RECOVERY PLAYBOOK)
## Sistem E-Voting Kampus Terenkripsi (Komisi Pemilihan Raya Mahasiswa)

Dokumen ini merupakan panduan resmi tanggap darurat saat terjadi insiden kritis seperti serangan ransomware, kerusakan data, manipulasi suara, atau kegagalan perangkat keras pada server e-voting.

---

## 1. PEMBAGIAN PERAN & TANGGUNG JAWAB (SIAPA MELAKUKAN APA)

Dalam situasi bencana, seluruh tindakan harus terkoordinasi secara ketat untuk menjaga integritas, netralitas, dan kepercayaan seluruh sivitas akademika:

| Peran | Pihak yang Bertanggung Jawab | Tugas Utama & Wewenang |
|---|---|---|
| **Incident Commander (IC)** | Ketua KPU Mahasiswa | - Mengambil keputusan resmi penghentian sementara pemilihan.<br>- Memberikan otorisasi resmi untuk eksekusi *database restore*.<br>- Mengumumkan status darurat dan konferensi pers kepada publik/mahasiswa. |
| **Lead Technical Operator** | Tim IT / DevOps KPU | - Mengeksekusi isolasi server (maintenance mode / firewall block).<br>- Menyiapkan berkas cadangan terenkripsi dari Offsite Storage.<br>- Menjalankan perintah pemulihan (`php artisan backup:restore`).<br>- Memverifikasi integritas checksum HMAC dan jumlah record pasca-restore. |
| **Independent Auditor** | Bawaslu Mahasiswa & Saksi Paslon | - Menyaksikan secara langsung pembukaan brankas kunci enkripsi.<br>- Menandatangani Berita Acara Rekonstruksi Data & Hasil Pemulihan.<br>- Memvalidasi bahwa data yang dipulihkan adalah versi bersih sebelum insiden. |
| **Public & Voter Relations** | Divisi Helpdesk & Humas | - Mengelola kanal pengaduan mahasiswa (WhatsApp & Live Chat).<br>- Menenangkan pemilih dan menyosialisasikan jadwal pembukaan kembali bilik suara. |

---

## 2. ARSITEKTUR & LOKASI CADANGAN (BACKUP STORAGE)

1. **Penyimpanan Lokal:**
   - Direktori: `storage/app/backups/`
   - Berkas: `backup_evoting_kampus_YYYY-MM-DD_HHmmss.sql.gz.enc`
   - Spesifikasi: Dump MySQL + Kompresi Gzip 9 + Enkripsi Simetris AES-256-CBC + Tanda Tangan Integritas HMAC-SHA256 (64-byte).
2. **Penyimpanan Terpisah (Offsite Storage):**
   - Direktori / Cloud Mount: `storage/app/offsite_backups/` (atau disk fisik/NFS/S3 bucket terpisah).
   - Dijadwalkan otomatis setiap hari pukul 02:00 WIB via Laravel Scheduler dengan masa retensi 7 hari.
3. **Kunci Enkripsi (`BACKUP_ENCRYPTION_KEY`):**
   - Disimpan secara fisik di brankas KPU dan di-*password manager* terisolasi milik Incident Commander & Lead IT.

---

## 3. PROSEDUR OPERASIONAL PEMULIHAN (RESTORE STEP-BY-STEP)

### Langkah 1: Karantina Sistem Seketika (Zero Request Inflow)
Aktifkan mode pemeliharaan agar tidak ada transaksi pemilih baru yang masuk:
```bash
php artisan down --message="Sistem E-Voting sedang dalam pemeliharaan darurat panitia. Silakan tunggu beberapa saat."
```

### Langkah 2: Identifikasi & Pemilihan Berkas Cadangan Bersih Terakhir
Jalankan perintah interaktif untuk melihat daftar cadangan yang tersedia beserta waktu pembuatan dan ukuran data:
```bash
php artisan backup:restore
```
Sistem akan menampilkan daftar:
```text
 Pilih berkas backup yang ingin Anda pulihkan:
  [0] backup_evoting_kampus_2026-10-12_002832.sql.gz.enc (Dibuat: 2026-10-12 00:28:32)
  [1] backup_evoting_kampus_2026-10-11_233615.sql.gz.enc (Dibuat: 2026-10-11 23:36:15)
 >
```
Ketik nomor cadangan yang valid dan konfirmasi dengan mengetik `yes`.

*Atau eksekusi non-interaktif secara langsung bila menggunakan skrip darurat:*
```bash
php artisan backup:restore backup_evoting_kampus_2026-10-12_002832.sql.gz.enc --force
```

### Langkah 3: Verifikasi Integritas Data Pasca-Restorasi
Periksa keselarasan data DPT, surat suara di kotak suara, dan log audit:
```bash
php artisan tinker --execute="echo 'Total DPT: ' . App\Models\Voter::count() . ' | Total Suara: ' . App\Models\BallotBox::count();"
```

### Langkah 4: Buka Kembali Bilik Suara
Setelah saksi paslon dan Bawaslu menandatangani Berita Acara:
```bash
php artisan up
```

---

## 4. SKENARIO BENCANA & RENCANA TINDAKAN (DISASTER SCENARIOS)

### Skenario A: Serangan Ransomware (Server Utama Terkunci Malware)
*Kondisi:* Server produksi terinfeksi ransomware, sistem file lokal terkunci.
1. **Pencegahan Penyebaran:** Cabut kabel jaringan / nonaktifkan interface jaringan server utama sesegera mungkin.
2. **Penyediaan Server Baru:** Nyalakan server cadangan bersih (Clean VPS Ubuntu/Debian).
3. **Deploy Source Code:** Clone repositori Git dari branch stabil (`security-hardening`).
4. **Konfigurasi Kunci:** Salin file `.env` dan masukkan `APP_KEY` serta `BACKUP_ENCRYPTION_KEY` resmi dari brankas panitia.
5. **Ambil Cadangan dari Offsite Storage:** Unduh berkas `.sql.gz.enc` terbaru dari Cloud S3 / Flashdisk fisik cadangan panitia, letakkan di `storage/app/backups/`.
6. **Eksekusi Pemulihan:**
   ```bash
   php artisan migrate
   php artisan backup:restore --force
   ```
7. RTO (Recovery Time Objective): **< 15 menit**. RPO (Recovery Point Objective): **< 24 jam**.

---

### Skenario B: Kerusakan Data / Manipulasi Suara oleh Penyerang Tersembunyi
*Kondisi:* Muncul anomali data pemilih atau selisih data audit trail.
1. Buka audit log untuk melacak waktu tepat pertama kali anomali terjadi:
   ```bash
   php artisan tinker --execute="App\Models\AuditLog::latest()->take(30)->get(['event', 'created_at', 'ip', 'user_id']);"
   ```
2. Pilih titik restorasi (Point-in-Time Backup) tepat sebelum insiden terjadi.
3. Jalankan `php artisan backup:restore <nama_file_sebelum_insiden> --force`.
4. Ubah kredensial database `DB_PASSWORD` di `.env` dan jalankan `php artisan security:db-least-privilege` untuk memastikan akses ilegal diblokir.

---

### Skenario C: Pemulihan Manual Darurat (Tanpa Menggunakan Framework Laravel)
Bila aplikasi Laravel rusak parah dan data harus diekstrak langsung via Linux CLI standar:
```bash
# 1. Ekstrak IV (byte 65 s.d 80)
tail -c +65 backup_file.sql.gz.enc | head -c 16 > iv.bin

# 2. Ekstrak Ciphertext (mulai byte 81)
tail -c +81 backup_file.sql.gz.enc > cipher.bin

# 3. Dekripsi dengan OpenSSL (AES-256-CBC)
openssl enc -d -aes-256-cbc -in cipher.bin -out dump.sql.gz -K <HEX_KEY_32BYTES> -iv $(xxd -p -c 16 iv.bin)

# 4. Dekompresi dan Impor langsung ke MySQL
gunzip -c dump.sql.gz | mysql -u evoting_migration -p evoting_kampus
```

---

## 5. PENGUJIAN INTEGRITAS RUTIN (DRILL & RECOVERY TEST)
Untuk menguji kehandalan sistem anti-tampering dan enkripsi tanpa memengaruhi data produksi, panitia dapat menjalankan simulasi pemulihan:
```bash
php artisan backup:test-recovery
```
Perintah ini akan secara otomatis:
- Membuat backup terenkripsi baru.
- Memverifikasi sinkronisasi lokal dan offsite.
- Sengaja merusak 1 byte berkas ciphertext untuk membuktikan penolakan otomatis oleh validasi HMAC-SHA256.
- Menjamin sistem aman dari pemalsuan data cadangan.
