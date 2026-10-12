# Panduan Hardening Server & Dependensi Produksi E-Voting Kampus
**Politeknik Negeri Bengkalis (Pemira Mahasiswa)**
*Target Kapasitas: 6.000 Pemilih Bersamaan (Peak Concurrency) & Zero-Trust Compliance*

---

## 1. Hardening Sistem Operasi & Akses SSH (Ubuntu 22.04 / 24.04 LTS)

### A. Konfigurasi SSH Aman (`/etc/ssh/sshd_config`)
Matikan login root dan autentikasi kata sandi. Wajibkan penggunaan pasangan kunci kriptografis Ed25519 / RSA 4096:
```bash
# /etc/ssh/sshd_config
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
X11Forwarding no
MaxAuthTries 3
LoginGraceTime 30
ClientAliveInterval 300
ClientAliveCountMax 2
```
Terapkan perubahan:
```bash
sudo systemctl restart sshd
```

### B. Optimasi Kernel Sysctl untuk Beban Tinggi (`/etc/sysctl.d/99-evoting.conf`)
Tambahkan parameter berikut untuk menampung lonjakan ribuan koneksi konkuren:
```ini
# Proteksi SYN Flood & DDoS
net.ipv4.tcp_syncookies = 1
net.ipv4.tcp_max_syn_backlog = 8192
net.ipv4.tcp_synack_retries = 2

# Tingkatkan batas antrean koneksi socket
net.core.somaxconn = 65535
net.core.netdev_max_backlog = 16384

# Tingkatkan batas file descriptor sistem
fs.file-max = 2097152

# Manajemen port lokal & daur ulang koneksi TIME_WAIT
net.ipv4.ip_local_port_range = 1024 65535
net.ipv4.tcp_fin_timeout = 15
net.ipv4.tcp_tw_reuse = 1
```
Muat konfigurasi:
```bash
sudo sysctl -p /etc/sysctl.d/99-evoting.conf
```

---

## 2. Firewall (UFW) & Pencegahan Intrusi (Fail2ban)

### A. Konfigurasi Firewall UFW
Tutup seluruh port kecuali SSH, HTTP, dan HTTPS:
```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp comment 'SSH Port'
sudo ufw allow 80/tcp comment 'HTTP (Redirect to HTTPS)'
sudo ufw allow 443/tcp comment 'HTTPS Secure Traffic'
sudo ufw enable
```

### B. Fail2ban untuk Proteksi Brute-Force (`/etc/fail2ban/jail.local`)
Pasang fail2ban untuk memblokir IP penyerang secara otomatis setelah percobaan gagal berturut-turut:
```ini
[DEFAULT]
bantime = 1h
findtime = 10m
maxretry = 5

[sshd]
enabled = true
port = 22
maxretry = 3
bantime = 24h

[nginx-http-auth]
enabled = true

[nginx-botsearch]
enabled = true
maxretry = 2
bantime = 12h
```
Jalankan Fail2ban:
```bash
sudo systemctl enable --now fail2ban
sudo fail2ban-client status
```

---

## 3. Optimasi PHP-FPM & OPcache (Peak 6.000 Voters)

### A. Tuning Pool PHP-FPM (`/etc/php/8.2/fpm/pool.d/www.conf`)
```ini
pm = dynamic
pm.max_children = 150
pm.start_servers = 30
pm.min_spare_servers = 20
pm.max_spare_servers = 50
pm.max_requests = 1000
request_terminate_timeout = 60s
```

### B. Tuning OPcache Produksi (`/etc/php/8.2/fpm/conf.d/10-opcache.ini`)
```ini
opcache.enable = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.revalidate_freq = 0
opcache.validate_timestamps = 0
opcache.save_comments = 1
opcache.fast_shutdown = 1
```

---

## 4. Keamanan Database MySQL 8.0 & Isolasi Port

1. **Pastikan MySQL HANYA mendengarkan loopback (Localhost):**
   ```ini
   # /etc/mysql/mysql.conf.d/mysqld.cnf
   bind-address = 127.0.0.1
   mysqlx-bind-address = 127.0.0.1
   ```
2. **Koneksi Pool untuk Konkurensi Tinggi:**
   ```ini
   max_connections = 600
   innodb_buffer_pool_size = 2G # Sesuaikan dengan 50-70% total RAM server
   innodb_log_file_size = 256M
   ```
3. **Prinsip Hak Akses Terkecil (Least Privilege):**
   Pengguna database Laravel (`evoting_app`) tidak boleh memiliki hak `GRANT OPTION`, `SUPER`, atau `SHUTDOWN`.

---

## 5. Izin Berkas (File Permissions) & Isolasi Privasi KTM

### A. Hak Akses File Sistem
```bash
cd /var/www/evoting-kampus

# Kepemilikan berkas web server
sudo chown -R www-data:www-data .

# Default folder 755, default file 644
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;

# Hak akses tulis khusus folder storage dan cache
sudo chmod -R 775 storage bootstrap/cache

# File konfigurasi rahasia (.env) HANYA boleh dibaca aplikasi
sudo chmod 600 .env
```

### B. Isolasi Berkas Identitas Mahasiswa (KTM Uploads)
- Seluruh berkas KTM disimpan di `storage/app/private_ktm/` (di luar root `public/`).
- File diberi nama berbasis **UUID v4 acak** tanpa mencantumkan NIM mahasiswa pada nama file di disk.
- Dilarang membuat symbolic link publik (`php artisan storage:link`) yang mengarah ke `private_ktm`.
- Berkas hanya dapat diakses melalui endpoint controller terenkripsi & lolos autentikasi 2FA:
  `Route::get('/helpdesk/sengketa/{sengketa}/ktm', [HelpdeskController::class, 'downloadKtm'])`
- Dilengkapi mekanisme pembersihan otomatis berkas sengketa kedaluwarsa via Artisan:
  ```bash
  php artisan sengketa:purge-ktm --days=30 --force
  ```

---

## 6. Audit Dependensi & Pembaruan Keamanan Rutin

### A. Pemeriksaan Kerentanan Dependensi (Vulnerability Audit)
Jalankan setiap sebelum rilis dan secara berkala via pipeline CI/CD:
```bash
# Audit paket PHP Composer
composer audit

# Audit paket JavaScript NPM
npm audit
```

### B. Pembaruan Otomatis Patch Keamanan OS (`unattended-upgrades`)
Aktifkan instalasi patch keamanan Ubuntu secara otomatis tanpa downtime:
```bash
sudo apt-get install unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
```
Konfigurasi pada `/etc/apt/apt.conf.d/50unattended-upgrades`:
```
Unattended-Upgrade::Allowed-Origins {
    "${distro_id}:${distro_codename}-security";
};
Unattended-Upgrade::AutoFixInterruptedDpkg "true";
Unattended-Upgrade::Remove-Unused-Kernel-Packages "true";
```
