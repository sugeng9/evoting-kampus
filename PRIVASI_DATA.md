# Kebijakan Privasi Data & Perlindungan Identitas Mahasiswa Pemilih
**Sistem E-Voting Pemilihan Raya Mahasiswa (PEMIRA)**
**Politeknik Negeri Bengkalis**
*Kepatuhan Terhadap UU No. 27 Tahun 2022 (Undang-Undang Perlindungan Data Pribadi / UU PDP)*

---

## 1. Landasan Hukum & Prinsip Dasar

Sistem E-Voting Pemira Politeknik Negeri Bengkalis dirancang dengan memadukan prinsip konstitusional pemilu Indonesia (**Langsung, Umum, Bebas, Rahasia, Jujur, dan Adil / LUBER JURDIL**) serta kepatuhan penuh terhadap **Undang-Undang Republik Indonesia Nomor 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP)**.

Prinsip-prinsip pemrosesan data pribadi dalam sistem ini:
1. **Prinsip Minimasi Data (*Data Minimization*):** Sistem hanya mengumpulkan data yang benar-benar esensial untuk memverifikasi hak suara mahasiswa aktif Politeknik Negeri Bengkalis.
2. **Prinsip Batasan Tujuan (*Purpose Limitation*):** Seluruh data pemilih hanya digunakan secara khusus untuk keperluan Pemira dan dilarang diserahkan kepada pihak ketiga di luar kampus.
3. **Prinsip Kerahasiaan Suara (*Secret Ballot & Zero-Knowledge*):** Pilihan kandidat di bilik suara bersifat rahasia mutlak dan tidak dapat ditautkan kembali ke identitas mahasiswa pemilih.
4. **Prinsip Pembatasan Penyimpanan (*Storage Limitation*):** Berkas sensitif (seperti bukti foto KTM) dimusnahkan secara berkala pasca-pemilihan setelah sengketa diselesaikan.

---

## 2. Klasifikasi & Pengamanan Data Pemilih

| Kategori Data | Contoh Elemen Data | Metode Penyimpanan & Pengamanan |
| :--- | :--- | :--- |
| **Identitas Akademik** | NIM, Nama Mahasiswa, Jurusan, Program Studi | Disimpan dalam tabel master DPT untuk validasi hak suara. Akses administratif dibatasi otorisasi berbasis peran (RBAC). |
| **Kredensial Autentikasi** | Kata Sandi / PIN Pemilih | Dihash satu arah menggunakan **Bcrypt (Cost Factor 12)** dengan salt kriptografis unik. Sistem tidak pernah menyimpan atau mengetahui PIN dalam bentuk plaintext. |
| **Data Verifikasi DPT** | Tanggal Lahir Mahasiswa | **TIDAK PERNAH disimpan dalam bentuk plaintext**. Tanggal lahir pemilih hanya disimpan dalam bentuk *one-way cryptographic hash* **SHA-256** untuk mencocokkan input verifikasi saat pendaftaran. |
| **Kode Autentikasi Sementara** | Kode OTP 6-Digit & Token Coblosan | Dihash dengan SHA-256, memiliki masa kedaluwarsa singkat (5 menit), batas percobaan maksimal 3 kali, dan langsung hangus setelah digunakan (*single-use burn*). |
| **Informasi Kontak** | Alamat Email Mahasiswa | Ditampilkan secara termaskir (*masked*) pada layar Helpdesk (contoh: `s***@polbeng.ac.id`) demi mencegah kebocoran kontak pribadi. |
| **Bukti Identitas Sengketa** | Foto / Scan Kartu Tanda Mahasiswa (KTM) | Disimpan di media penyimpanan privat server (`storage/app/private_ktm/`) dengan penamaan **UUID v4 acak** tanpa memuat NIM mahasiswa pada nama file. Akses hanya diberikan kepada petugas terautentikasi dan lolos 2FA. |

---

## 3. Arsitektur Kerahasiaan Surat Suara (Secret Ballot Separation)

Prinsip kerahasiaan pilihan suara (*secret ballot*) dijamin secara struktural pada skema basis data dengan memisahkan secara fisik pencatatan **partisipasi pemilih** dan **pilihan kandidat**:

```
+------------------------------------+          +------------------------------------+
|        voter_participations        |          |            ballot_boxes            |
+------------------------------------+          +------------------------------------+
| id                                 |          | id                                 |
| voter_id  (ID Pemilih Terdaftar)   |          | election_category_id (Kategori)    |
| election_category_id (Bilik Suara) |          | candidate_id (Paslon Terpilih)     |
| has_voted = true                   |          | idempotency_key (Hash Acak)        |
| voted_at (Waktu Partisipasi)       |          | created_at                         |
+------------------------------------+          +------------------------------------+
                  │                                                │
                  └──────────── TIDAK ADA RELASI / FK ─────────────┘
                          (SECURE ZERO-KNOWLEDGE AIR-GAP)
```

### Bukti Matematis & Teknis Pemisahan:
1. **Tidak Ada Kolom Identitas:** Tabel `ballot_boxes` sama sekali **TIDAK MEMILIKI** kolom `voter_id`, `nim`, `email`, atau foreign key yang mengarah ke tabel `voters`.
2. **Ketiadaan Korelasi Rekonstruksi:** Tidak ada query SQL, join relasional, ataupun fungsi enkripsi yang memungkinkan panitia, administrator, maupun penyerang merekonstruksi suara tertentu ke mahasiswa tertentu.
3. **Pencatatan Idempotensi:** Kolom `idempotency_key` pada `ballot_boxes` merupakan hash acak yang berdiri sendiri untuk mencegah *double voting* tanpa membuka identitas pemilih.

---

## 4. Retensi & Pemusnahan Data Pasca-Pemira

Sesuai Pasal 16 & Pasal 44 UU PDP terkait kewajiban penghapusan data pribadi:

1. **Pemusnahan Berkas Bukti KTM Sengketa:**
   - Seluruh berkas foto KTM sengketa yang telah berstatus `selesai` atau `ditolak` dimusnahkan secara permanen dari server setelah batas waktu retensi 30 hari melalui perintah:
     ```bash
     php artisan sengketa:purge-ktm --days=30 --force
     ```
   - Perintah ini dijalankan secara otomatis setiap minggu oleh cron job sistem.
2. **Penghapusan Sesi & Kredensial Sementara:**
   - Seluruh kode OTP, token coblosan sementara, dan sesi aktif bilik suara dihanguskan secara instan pasca-tahapan pemungutan suara berakhir.
3. **Penyimpanan Arsip Log Audit & Hasil Pemira:**
   - Rekaman log audit berantai kriptografis (*blockchain-style hash chaining*) dan agregat hasil suara disimpan dalam status *read-only* selama 1 (satu) tahun akademik sebagai arsip Berita Acara resmi PanWas, sebelum dipindahkan ke media penyimpanan luring (*offline cold storage*).

---

## 5. Hak-Hak Subjek Data (Mahasiswa Pemilih)

Mahasiswa Politeknik Negeri Bengkalis memiliki hak perlindungan data yang diakui penuh:
- **Hak Mengetahui Status:** Mahasiswa berhak memeriksa status pendaftaran DPT secara mandiri melalui menu `/nim-status`.
- **Hak Koreksi Data & Sengketa:** Mahasiswa yang NIM-nya disalahgunakan berhak mengajukan sengketa resmi dan meminta pencabutan akun palsu melalui Helpdesk KPR.
- **Hak Kerahasiaan Pilihan:** Pilihan bilik suara mahasiswa dilindungi secara hukum dan teknis; tidak ada pihak mana pun (termasuk dosen, pimpinan jurusan, maupun pimpinan kampus) yang dapat mengetahui paslon pilihan mahasiswa.
- **Hak Pelaporan:** Setiap dugaan pelanggaran privasi data dapat dilaporkan secara tertulis kepada **Panitia Pengawas (PanWas) Pemira**.
