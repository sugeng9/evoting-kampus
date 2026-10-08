# E-Voting Kampus - High Security & Secret Ballot Architecture

Sistem E-Voting Kampus berbasis **Laravel & MySQL** dengan arsitektur keamanan tingkat tinggi, mitigasi ancaman STRIDE, serta pemisahan identitas pemilih dan suara kriptografis (*Secret Ballot Protocol*).

---

## 🚀 Fitur Utama

- **Secret Ballot Protocol:** Suara pemilih di Kotak Suara Digital (`ballot_boxes`) disimpan dengan UUID acak tanpa relasi identitas NIM, diamankan dengan HMAC SHA-256.
- **Pessimistic Locking (`lockForUpdate`):** Transaksi atomik database level tinggi untuk mencegah coblos ganda (*Double Voting / Race Condition*).
- **Multi-Guard Separation:** Pemisahan ketat antara sesi Mahasiswa (`guard: voter`) dan Panitia KPU/Helpdesk (`guard: web`).
- **Single Active Session Protection:** Mencegah satu akun digunakan bersamaan di banyak perangkat.
- **Audit Trail & Kuitansi Elektronik:** Kode verifikasi unik (`PEMIRA-XXXX-XXXX`) untuk audit independen suara di kotak suara digital.
- **Real-Time Live Quick Count:** Caching adaptif untuk menahan ribuan request live count tanpa membebani database.
- **Helpdesk Sengketa (Two-Officer Approval):** Pencabutan akun hanya bisa disetujui jika disahkan oleh 2 petugas berbeda demi netralitas.

---

## 🛠️ Persyaratan Sistem

- PHP >= 8.2
- Composer >= 2.0
- MySQL >= 8.0 / MariaDB
- Ekstensi PHP: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`

---

## 📦 Panduan Instalasi

1. **Clone repositori:**
   ```bash
   git clone <URL_REPOSITORY_ANDA>
   cd evoting-kampus
   ```

2. **Install Dependensi Composer:**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Sesuaikan konfigurasi database (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) pada berkas `.env`.

4. **Jalankan Migrasi & Seeder:**
   ```bash
   php artisan migrate --seed
   ```

5. **Jalankan Web Server Lokal:**
   ```bash
   php artisan serve
   ```

Akses portal pemilih di `http://127.0.0.1:8000` dan panel admin di `http://127.0.0.1:8000/admin/login`.
