# PANDUAN FIREWALL & WAF (SECURITY RUNBOOK)
## Sistem E-Voting Kampus Aman & Anti-Kecurangan Berbasis Laravel

Dokumen ini adalah panduan teknis operasional (*runbook*) penerapan pertahanan berlapis (*Defense in Depth*) untuk sistem E-Voting Pemira Kampus, mencakup konfigurasi **Cloudflare WAF (Edge)**, **Server Firewall (UFW Linux)**, dan **Aplikasi Laravel**.

---

## 1. Arsitektur Pertahanan Berlapis (Defense in Depth)

```
[ Internet / Pemilih Mahasiswa ]
              │
              ▼
   ┌──────────────────────────────────────────────┐
   │ 1. CLOUDFLARE EDGE (WAF & DDoS Protection)    │
   │    - Managed Challenge / Turnstile Anti-Bot  │
   │    - Country Restriction (Khusus Indonesia)  │
   │    - Rate Limiting & Bad User-Agent Blocker │
   │    - Full (Strict) SSL/TLS Encryption        │
   └──────────────────────────────────────────────┘
              │ (Hanya port 80/443 terenkripsi)
              ▼
   ┌──────────────────────────────────────────────┐
   │ 2. SERVER FIREWALL (Linux UFW / iptables)     │
   │    - Port 80 & 443 terbuka (Origin Web)      │
   │    - Port 22 (SSH) dibatasi IP Panitia       │
   │    - Port 3306 (MySQL) ditutup (127.0.0.1)   │
   └──────────────────────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────────┐
   │ 3. NGINX WEB SERVER HARDENING                │
   │    - Block .env, .git, .sql, .bak            │
   │    - server_tokens off (sembunyikan versi)   │
   │    - client_max_body_size 5M                 │
   └──────────────────────────────────────────────┘
              │
              ▼
   ┌──────────────────────────────────────────────┐
   │ 4. LARAVEL APPLICATION HARDENING             │
   │    - SecurityHeadersMiddleware (HSTS, CSP)   │
   │    - Route Throttling (Login, OTP, Ballot)   │
   │    - Single Voter Session & Secret Ballot    │
   └──────────────────────────────────────────────┘
```

---

## 2. Aturan Cloudflare WAF (Langkah Konfigurasi Dashboard)

Masuk ke dashboard **Cloudflare** > pilih domain e-voting Anda > menu **Security** > **WAF**.

### A. SSL/TLS Setting (Enkripsi Penuh)
1. Buka menu **SSL/TLS** > **Overview**:
   - Pilih mode: **Full (Strict)**.
2. Buka menu **SSL/TLS** > **Edge Certificates**:
   - **Always Use HTTPS**: `ON`.
   - **Minimum TLS Version**: `TLS 1.2` (Direkomendasikan `TLS 1.3`).
   - **Opportunistic Encryption**: `ON`.
   - **Automatic HTTPS Rewrites**: `ON`.

### B. Custom WAF Rules (Aturan Pencegahan Serangan)

Buat aturan berikut pada tab **Security** > **WAF** > **Custom rules**:

#### Rule 1: Geolocation Fence (Hanya Akses dari Indonesia)
Mencegah serangan bot lintas negara atau hacker luar negeri yang tidak berkepentingan dengan Pemira kampus.
* **Rule Name**: `Pemira - Geo IP Indonesia Only`
* **Field / Expression**:
  ```text
  (ip.geoip.country ne "ID" and not cf.client.bot)
  ```
* **Action**: `Block` atau `Managed Challenge`

#### Rule 2: Blokir Alat Peretas & Malicious User-Agent
Mencegah scanning otomatis menggunakan sqlmap, nmap, nikto, dirbuster, dll.
* **Rule Name**: `Pemira - Block Hacker Scanner Tools`
* **Field / Expression**:
  ```text
  (http.user_agent contains "sqlmap" or http.user_agent contains "nikto" or http.user_agent contains "nmap" or http.user_agent contains "masscan" or http.user_agent contains "dirbuster" or http.user_agent contains "gobuster" or http.user_agent contains "python-requests" or http.user_agent contains "curl")
  ```
* **Action**: `Block`

#### Rule 3: Rate Limiting Route Sensitif (Anti-Brute Force & Flooding)
Pada menu **Security** > **WAF** > **Rate limiting rules**:
* **Rule Name**: `Pemira - Auth & OTP Rate Limiter`
* **If incoming requests match**:
  ```text
  (http.request.uri.path in {"/login" "/admin/login" "/daftar/otp" "/daftar/otp/verifikasi"})
  ```
* **When rate exceeds**:
  - Request count: `10 requests`
  - Period: `10 seconds`
* **Action**: `Managed Challenge` atau `Block`
* **Duration**: `10 minutes`

#### Rule 4: Proteksi Portal Administrator
Membatasi akses direktori admin panitia (`/admin*`):
* **Rule Name**: `Pemira - Admin Workstation Protection`
* **Field / Expression**:
  ```text
  (http.request.uri.path starts_with "/admin")
  ```
* **Action**: `Managed Challenge` (atau izinkan hanya subnet IP Panitia Kampus).

---

## 3. Konfigurasi Firewall Server (Linux UFW - Ubuntu/Debian)

Jalankan perintah berikut di terminal server Linux (VPS) sebelum aplikasi dirilis:

### A. Reset & Set Default Policy
```bash
# Tolak semua koneksi masuk secara default, izinkan koneksi keluar
sudo ufw default deny incoming
sudo ufw default allow outgoing
```

### B. Buka Port yang Diperlukan Saja
```bash
# 1. Buka Port SSH (Ganti port 22 jika memakai port kustom)
sudo ufw allow 22/tcp comment 'SSH Remote Access'

# Jika panitia memiliki IP Statis / VPN Kampus, kunci SSH ke IP tersebut:
# sudo ufw allow from 103.xxx.xxx.xxx to any port 22 proto tcp

# 2. Buka Port Web (HTTP & HTTPS)
sudo ufw allow 80/tcp comment 'HTTP Web'
sudo ufw allow 443/tcp comment 'HTTPS Web'

# 3. PASTIKAN PORT LAIN TERTUTUP:
# MySQL (3306), Redis (6379), PhpMyAdmin TIDAK DIBUKA ke publik.
```

### C. Aktifkan Firewall
```bash
sudo ufw enable
sudo ufw status verbose
```
Hasil output yang diharapkan:
```text
Status: active
Logging: on (low)
Default: deny (incoming), allow (outgoing), disabled (routed)

To                         Action      From
--                         ------      ----
22/tcp                     ALLOW IN    Anywhere
80/tcp                     ALLOW IN    Anywhere
443/tcp                    ALLOW IN    Anywhere
```

### D. Pastikan MySQL Hanya Mendengarkan Localhost (127.0.0.1)
Edit file konfigurasi MySQL server:
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```
Pastikan baris berikut aktif:
```ini
bind-address = 127.0.0.1
```
Restart MySQL:
```bash
sudo systemctl restart mysql
```

---

## 4. Konfigurasi Nginx Web Server Hardening

Tambahkan blok konfigurasi berikut pada server block Nginx (`/etc/nginx/sites-available/evoting`):

```nginx
# 1. Sembunyikan versi Nginx dari scanner
server_tokens off;

# 2. Batasi ukuran upload form mahasiswa (KTM / bukti)
client_max_body_size 5M;
client_body_buffer_size 128k;

# 3. Blokir akses ke file sensitif, backup, dan Git
location ~ /\.(env|git|htaccess|svn) {
    deny all;
    return 404;
}

location ~* \.(sql|bak|log|conf|sh)$ {
    deny all;
    return 404;
}

# 4. Proxy Headers untuk Cloudflare
set_real_ip_from 173.245.48.0/20;
set_real_ip_from 103.21.244.0/22;
set_real_ip_from 103.22.200.0/22;
set_real_ip_from 103.31.4.0/22;
set_real_ip_from 141.101.64.0/18;
set_real_ip_from 108.162.192.0/18;
set_real_ip_from 190.93.240.0/20;
set_real_ip_from 188.114.96.0/20;
set_real_ip_from 197.234.240.0/22;
set_real_ip_from 198.41.128.0/17;
set_real_ip_from 162.158.0.0/15;
set_real_ip_from 104.16.0.0/13;
set_real_ip_from 104.24.0.0/14;
set_real_ip_from 172.64.0.0/13;
set_real_ip_from 131.0.72.0/22;
real_ip_header CF-Connecting-IP;
```

---

## 5. Ringkasan Rate Limiting Aplikasi Laravel (Level Route)

| Endpoint | Method | Batas Frekuensi | Tujuan Keamanan |
| :--- | :--- | :--- | :--- |
| `/login` | `POST` | **6 req / menit** | Mencegah Brute Force PIN/Password Pemilih |
| `/admin/login` | `POST` | **5 req / menit** | Proteksi Kredensial Panitia KPU |
| `/daftar/otp` | `POST` | **5 req / menit** | Mencegah Email Flooding & Habisnya Kuota SMTP |
| `/daftar/otp/verifikasi` | `POST` | **6 req / menit** | Mencegah Brute Force Tebakan 6-Digit OTP |
| `/daftar/otp/resend` | `POST` | **5 req / menit** | Mencegah Abuse Tombol Kirim Ulang OTP |
| `/nim-status` | `POST` | **15 req / menit** | Mencegah Enumerasi NIM Mahasiswa Kampus |
| `/voter/ballot/submit` | `POST` | **15 req / menit** | Mencegah DoS / Race-Condition Pengiriman Suara |
| `/verify-ballot` | `POST` | **20 req / menit** | Menjaga Performa Server Pencarian Hash Kuitansi |
