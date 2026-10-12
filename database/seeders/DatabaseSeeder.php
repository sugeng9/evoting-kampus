<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\ElectionCategory;
use App\Models\ElectionSetting;
use App\Models\User;
use App\Models\Voter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Inisialisasi Administrator / Panitia KPU Kampus
        User::updateOrCreate(
            ['username' => 'admin_kpu'],
            [
                'name' => 'Ketua KPU Mahasiswa',
                'email' => 'kpu@universitas.ac.id',
                'password' => Hash::make('AdminKpu2026!Secure'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // 2. Setting Sesi Pemira
        ElectionSetting::updateOrCreate(
            ['id' => 1],
            [
                'election_name' => 'PEMIRA UNIVERSITAS 2026',
                'status' => 'OPEN',
                'voting_starts_at' => now()->startOfDay(),
                'voting_ends_at' => now()->addDays(2)->endOfDay(),
                'is_quick_count_public' => true,
                'require_two_step_confirmation' => true,
            ]
        );

        // 3. Pilar 1: Pemilihan Presiden & Wakil Presiden Mahasiswa (BEM KM)
        $bemCat = ElectionCategory::updateOrCreate(
            ['code' => 'BEM_KM_2026'],
            [
                'name' => 'Pemilihan Presiden & Wakil Presiden Mahasiswa (BEM KM)',
                'description' => '[HAK SUARA 1] Memilih nahkoda Badan Eksekutif Mahasiswa (BEM) tingkat Universitas.',
                'faculty_filter' => null,
                'study_program_filter' => null,
                'start_at' => now()->subHours(2),
                'end_at' => now()->addHours(6),
                'status_override' => 'AUTO',
                'is_active' => true,
                'display_order' => 1,
            ]
        );

        Candidate::updateOrCreate(
            ['election_category_id' => $bemCat->id, 'candidate_number' => 1],
            [
                'leader_name' => 'Arya Dimas Pratama',
                'leader_nim' => '120140012',
                'vice_leader_name' => 'Annisa Zahra',
                'vice_leader_nim' => '120130045',
                'vision' => 'Mewujudkan BEM Kampus yang Progresif, Kolaboratif, dan Solutif Berbasis Teknologi.',
                'mission' => "1. Digitalisasi advokasi keluhan dan transparansi keuangan ormawa.\n2. Mengakselerasi inkubasi karir & riset mahasiswa antar-fakultas.\n3. Mewujudkan ruang kampus yang aman, inklusif, dan bebas kekerasan.",
                'is_active' => true,
            ]
        );

        Candidate::updateOrCreate(
            ['election_category_id' => $bemCat->id, 'candidate_number' => 2],
            [
                'leader_name' => 'Fikri Haikal',
                'leader_nim' => '120110088',
                'vice_leader_name' => 'Nabila Putri Santoso',
                'vice_leader_nim' => '120150021',
                'vision' => 'Sinergi Aksi Nyata Mahasiswa untuk Integritas Akademik dan Pengabdian Masyarakat.',
                'mission' => "1. Memperkuat gerakan sosial kemasyarakatan dan tanggap bencana.\n2. Optimalisasi sarana prasarana penunjang prestasi minat & bakat mahasiswa.\n3. Diplomasi terbuka dengan jajaran rektorat dan alumni nasional.",
                'is_active' => true,
            ]
        );

        // 4. Pilar 2: Pemilihan Ketua Umum Majelis Permusyawaratan Mahasiswa (MPM Polbeng - Tanpa Wakil)
        $mpmCat = ElectionCategory::updateOrCreate(
            ['code' => 'MPM_KM_2026'],
            [
                'name' => 'Pemilihan Ketua Umum Majelis Permusyawaratan Mahasiswa (MPM)',
                'description' => '[HAK SUARA 2] Lembaga legislatif & permusyawaratan tertinggi mahasiswa Politeknik Negeri Bengkalis (Hanya Ketua Umum, Tanpa Wakil).',
                'faculty_filter' => null,
                'study_program_filter' => null,
                'start_at' => now()->subHours(2),
                'end_at' => now()->addHours(6),
                'status_override' => 'AUTO',
                'is_active' => true,
                'display_order' => 2,
            ]
        );

        Candidate::updateOrCreate(
            ['election_category_id' => $mpmCat->id, 'candidate_number' => 1],
            [
                'leader_name' => 'Raka Aditya Pratama',
                'leader_nim' => '120140025',
                'vice_leader_name' => null,
                'vice_leader_nim' => null,
                'vision' => 'MPM Kritis, Solutif, dan Berintegritas Mengawal Aspirasi Seluruh Mahasiswa Polbeng.',
                'mission' => "1. Transparansi legislasi dan anggaran kemahasiswaan Polbeng.\n2. Optimalisasi kanal dengar pendapat terbuka mahasiswa.",
                'is_active' => true,
            ]
        );

        Candidate::updateOrCreate(
            ['election_category_id' => $mpmCat->id, 'candidate_number' => 2],
            [
                'leader_name' => 'Farah Hanifah',
                'leader_nim' => '120130060',
                'vice_leader_name' => null,
                'vice_leader_nim' => null,
                'vision' => 'Sinergi Legislasi Mahasiswa yang Inklusif, Independen, dan Berdaya Dampak.',
                'mission' => "1. Reformasi tata kelola peraturan kemahasiswaan yang berkeadilan.\n2. Advokasi aktif hak-hak akademik dan fasilitas kampus.",
                'is_active' => true,
            ]
        );

        // 5. Pilar 3: Himpunan Mahasiswa Jurusan (HMJ Polbeng - 7 Jurusan Resmi)
        $hmjList = [
            [
                'code' => 'KAHIM_HMM_2026',
                'name' => 'Pemilihan Ketua Himpunan Mahasiswa Mesin (HMM)',
                'study_program_filter' => 'Teknik Mesin',
                'display_order' => 3,
                'c1' => ['leader_name' => 'Danang Surya', 'leader_nim' => '120120033', 'vision' => 'Solidaritas Mesin Kuat dan Kompeten dalam Rekayasa Manufaktur.', 'mission' => 'Pengembangan inovasi mesin hemat energi dan pelatihan CAD.'],
                'c2' => ['leader_name' => 'Bagas Satria', 'leader_nim' => '120120044', 'vision' => 'HMM Solutif dan Berdaya Saing Industri Manufaktur 4.0.', 'mission' => 'Sertifikasi keahlian mekanik dan pengabdian masyarakat.'],
            ],
            [
                'code' => 'KAHIM_HIMKA_2026',
                'name' => 'Pemilihan Ketua Himpunan Mahasiswa Teknik Perkapalan (HIMKA)',
                'study_program_filter' => 'Teknik Perkapalan',
                'display_order' => 4,
                'c1' => ['leader_name' => 'Rian Hidayat', 'leader_nim' => '120160011', 'vision' => 'HIMKA Maritim Unggul dan Berintegritas dalam Desain Perkapalan.', 'mission' => 'Lomba desain kapal nasional & riset teknologi galangan kapal.'],
                'c2' => ['leader_name' => 'Siti Nur Aini', 'leader_nim' => '120160022', 'vision' => 'Keluarga Besar Perkapalan yang Solid, Inovatif, dan Profesional.', 'mission' => 'Peningkatan kompetensi maritim dan pengabdian pesisir.'],
            ],
            [
                'code' => 'KAHIM_HIMATRO_2026',
                'name' => 'Pemilihan Ketua Himpunan Mahasiswa Teknik Elektro (HIMATRO)',
                'study_program_filter' => 'Teknik Elektro',
                'display_order' => 5,
                'c1' => ['leader_name' => 'Bambang Setiawan', 'leader_nim' => '120130055', 'vision' => 'HIMATRO Inovatif Menjawab Tantangan Energi Terbarukan & IoT.', 'mission' => 'Riset robotika dan otomatisasi kelistrikan kampus.'],
                'c2' => ['leader_name' => 'Aulia Rahma', 'leader_nim' => '120130066', 'vision' => 'HIMATRO Bersatu, Berintegritas, dan Berprestasi Nasional.', 'mission' => 'Peningkatan lab hardware & mentoring perlombaan teknologi.'],
            ],
            [
                'code' => 'KAHIM_HIMANIA_2026',
                'name' => 'Pemilihan Ketua Himpunan Mahasiswa Administrasi Niaga (HIMANIA)',
                'study_program_filter' => 'Administrasi Niaga',
                'display_order' => 6,
                'c1' => ['leader_name' => 'Dwi Handayani', 'leader_nim' => '120170011', 'vision' => 'HIMANIA Kreatif, Mandiri, dan Unggul dalam Tata Kelola Bisnis.', 'mission' => 'Inkubasi wirausaha mahasiswa dan sertifikasi administrasi perkantoran modern.'],
                'c2' => ['leader_name' => 'Reza Fahlevi', 'leader_nim' => '120170022', 'vision' => 'Mewujudkan Mahasiswa Niaga yang Adaptif dan Berwawasan Global.', 'mission' => 'Kolaborasi dunia industri ritel dan penguatan literasi keuangan.'],
            ],
            [
                'code' => 'KAHIM_HMTI_2026',
                'name' => 'Pemilihan Ketua Himpunan Mahasiswa Teknik Informatika (HMTI)',
                'study_program_filter' => 'Teknik Informatika',
                'display_order' => 7,
                'c1' => ['leader_name' => 'Rizky Alamsyah', 'leader_nim' => '120140050', 'vision' => 'Informatika Berdaya Saing Global dengan Semangat Kolaborasi.', 'mission' => 'Budaya open-source, bootcamp kecerdasan buatan & web development.'],
                'c2' => ['leader_name' => 'Dewi Lestari', 'leader_nim' => '120140077', 'vision' => 'HMTI Inklusif, Kreatif, dan Menjadi Wadah Inovasi Digital.', 'mission' => 'Fasilitasi hackathon nasional dan tata kelola transparan.'],
            ],
            [
                'code' => 'KAHIM_HMTS_2026',
                'name' => 'Pemilihan Ketua Himpunan Mahasiswa Teknik Sipil (HMTS)',
                'study_program_filter' => 'Teknik Sipil',
                'display_order' => 8,
                'c1' => ['leader_name' => 'Fajar Pratama', 'leader_nim' => '120150011', 'vision' => 'Mewujudkan HMTS yang Kokoh, Unggul, dan Berdaya Saing.', 'mission' => 'Penguatan keahlian konstruksi, struktur terapan, dan survei lapangan.'],
                'c2' => ['leader_name' => 'Wahyu Ramadhan', 'leader_nim' => '120150022', 'vision' => 'Sinergi Mahasiswa Teknik Sipil yang Inovatif dan Berkelanjutan.', 'mission' => 'Pelatihan software teknik sipil modern (AutoCAD, SAP2000, BIM) dan riset infrastruktur.'],
            ],
            [
                'code' => 'KAHIM_LEDSA_2026',
                'name' => 'Pemilihan Ketua Language Department Students Association (LEDSA)',
                'study_program_filter' => 'Bahasa',
                'display_order' => 9,
                'c1' => ['leader_name' => 'Annisa Fitriani', 'leader_nim' => '120180011', 'vision' => 'LEDSA Inspiratif: Mengembangkan Literasi Bahasa & Komunikasi Global.', 'mission' => 'Public speaking bootcamp, festival bahasa internasional, dan TOEFL clinic.'],
                'c2' => ['leader_name' => 'M. Iqbal Syahputra', 'leader_nim' => '120180022', 'vision' => 'Membangun Mahasiswa Bahasa yang Berkarakter dan Percaya Diri.', 'mission' => 'Debat bahasa Inggris tingkat nasional dan program pertukaran budaya.'],
            ],
            [
                'code' => 'KAHIM_HTK_2026',
                'name' => 'Pemilihan Ketua Himpunan Taruna Kemaritiman (HTK)',
                'study_program_filter' => 'Kemaritiman',
                'display_order' => 10,
                'c1' => ['leader_name' => 'Surya Pratama', 'leader_nim' => '120190011', 'vision' => 'Jiwa Korsa Taruna Kemaritiman yang Disiplin, Tangguh, dan Berwawasan Samudera.', 'mission' => 'Pelatihan navigasi pelayaran, simulasi keselamatan laut, dan kepemimpinan taruna.'],
                'c2' => ['leader_name' => 'Aldi Kurniawan', 'leader_nim' => '120190022', 'vision' => 'Kemaritiman Polbeng Berjaya di Tingkat Nasional dan Internasional.', 'mission' => 'Penguatan jejaring pelaut perwira dan pengabdian konservasi kelautan.'],
            ],
        ];

        foreach ($hmjList as $h) {
            $cat = ElectionCategory::updateOrCreate(
                ['code' => $h['code']],
                [
                    'name' => $h['name'],
                    'description' => "[HAK SUARA 3] Himpunan Mahasiswa Jurusan {$h['study_program_filter']} Politeknik Negeri Bengkalis.",
                    'faculty_filter' => null,
                    'study_program_filter' => $h['study_program_filter'],
                    'display_order' => $h['display_order'],
                    'is_active' => true,
                    'status_override' => 'AUTO',
                ]
            );

            Candidate::updateOrCreate(
                ['election_category_id' => $cat->id, 'candidate_number' => 1],
                [
                    'leader_name' => $h['c1']['leader_name'],
                    'leader_nim' => $h['c1']['leader_nim'],
                    'vice_leader_name' => null,
                    'vice_leader_nim' => null,
                    'vision' => $h['c1']['vision'],
                    'mission' => $h['c1']['mission'],
                    'is_active' => true,
                ]
            );

            Candidate::updateOrCreate(
                ['election_category_id' => $cat->id, 'candidate_number' => 2],
                [
                    'leader_name' => $h['c2']['leader_name'],
                    'leader_nim' => $h['c2']['leader_nim'],
                    'vice_leader_name' => null,
                    'vice_leader_nim' => null,
                    'vision' => $h['c2']['vision'],
                    'mission' => $h['c2']['mission'],
                    'is_active' => true,
                ]
            );
        }

        // Jalankan pembersihan & nonaktifkan kategori duplikat (PRESMA, DPM, Fakultas)
        ElectionCategory::cleanupLegacyDuplicates();

        // 5. Inisialisasi Autentikasi Pemilih (Staff Helpdesk, Akun Admin, & Fase Awal)
        $this->call(PemilihMasterSeeder::class);

        // 6. Muat Akun Delegasi Siap Login per Jurusan untuk Sosialisasi Mahasiswa
        $this->call(DelegasiSosialisasiSeeder::class);
    }
}
