<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\ElectionCategory;
use App\Models\PemilihMaster;
use App\Models\Voter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DelegasiSosialisasiSeeder extends Seeder
{
    /**
     * Seed akun mahasiswa siap coblos untuk 8 Jurusan Politeknik Negeri Bengkalis
     * Seluruh akun berstatus DPT Aktif, siap login langsung dengan NIM + PIN: 123456
     */
    public function run(): void
    {
        Voter::ensureSchema();

        $defaultPin = '123456';
        $hashedPin = Hash::make($defaultPin);

        // Daftar 8 Jurusan Resmi Politeknik Negeri Bengkalis
        $delegates = [
            [
                'prodi' => 'Teknik Informatika',
                'faculty' => 'Jurusan Teknik Informatika',
                'hmj' => 'HMTI (Himpunan Mahasiswa Teknik Informatika)',
                'scenario' => 'Kompetisi (3 Bilik Suara: BEM, MPM, HMTI)',
                'students' => [
                    ['nim' => '120140001', 'name' => 'Budi Pratama', 'dob' => '2004-05-15', 'angkatan' => '2022'],
                    ['nim' => '120140002', 'name' => 'Siti Rahmawati', 'dob' => '2004-08-20', 'angkatan' => '2022'],
                ],
            ],
            [
                'prodi' => 'Teknik Mesin',
                'faculty' => 'Jurusan Teknik Mesin',
                'hmj' => 'HMM (Himpunan Mahasiswa Mesin)',
                'scenario' => 'Kompetisi (3 Bilik Suara: BEM, MPM, HMM)',
                'students' => [
                    ['nim' => '120120001', 'name' => 'Rian Hidayat', 'dob' => '2003-11-10', 'angkatan' => '2021'],
                    ['nim' => '120120002', 'name' => 'Dodi Firmansyah', 'dob' => '2003-12-05', 'angkatan' => '2021'],
                ],
            ],
            [
                'prodi' => 'Teknik Elektro',
                'faculty' => 'Jurusan Teknik Elektro',
                'hmj' => 'HIMATRO (Himpunan Mahasiswa Teknik Elektro)',
                'scenario' => 'Kompetisi (3 Bilik Suara: BEM, MPM, HIMATRO)',
                'students' => [
                    ['nim' => '120130001', 'name' => 'Wahyu Saputra', 'dob' => '2005-01-14', 'angkatan' => '2023'],
                    ['nim' => '120130002', 'name' => 'Fitri Handayani', 'dob' => '2005-03-25', 'angkatan' => '2023'],
                ],
            ],
            [
                'prodi' => 'Teknik Sipil',
                'faculty' => 'Jurusan Teknik Sipil',
                'hmj' => 'HMTS (Himpunan Mahasiswa Teknik Sipil)',
                'scenario' => 'Kompetisi (3 Bilik Suara: BEM, MPM, HMTS)',
                'students' => [
                    ['nim' => '120150001', 'name' => 'Fajar Ramadhan', 'dob' => '2004-09-18', 'angkatan' => '2022'],
                    ['nim' => '120150002', 'name' => 'Annisa Putri', 'dob' => '2004-10-30', 'angkatan' => '2022'],
                ],
            ],
            [
                'prodi' => 'Teknik Perkapalan',
                'faculty' => 'Jurusan Teknik Perkapalan',
                'hmj' => 'HIMKA (Himpunan Mahasiswa Teknik Perkapalan)',
                'scenario' => 'Kompetisi (3 Bilik Suara: BEM, MPM, HIMKA)',
                'students' => [
                    ['nim' => '120160001', 'name' => 'Gilang Perkasa', 'dob' => '2003-04-12', 'angkatan' => '2021'],
                    ['nim' => '120160002', 'name' => 'Nabila Syifa', 'dob' => '2003-06-22', 'angkatan' => '2021'],
                ],
            ],
            [
                'prodi' => 'Administrasi Niaga',
                'faculty' => 'Jurusan Administrasi Niaga',
                'hmj' => 'HIMANIA (Himpunan Mahasiswa Administrasi Niaga)',
                'scenario' => 'Kompetisi (3 Bilik Suara: BEM, MPM, HIMANIA)',
                'students' => [
                    ['nim' => '120170001', 'name' => 'Dimas Ardiansyah', 'dob' => '2004-02-19', 'angkatan' => '2022'],
                    ['nim' => '120170002', 'name' => 'Tiara Anggraini', 'dob' => '2004-07-11', 'angkatan' => '2022'],
                ],
            ],
            [
                'prodi' => 'Bahasa',
                'faculty' => 'Jurusan Bahasa',
                'hmj' => 'LEDSA (Language Department Students Association)',
                'scenario' => 'Aklamasi (2 Bilik: BEM & MPM, HMJ Ditetapkan Aklamasi 1 Calon)',
                'students' => [
                    ['nim' => '120180001', 'name' => 'M. Kevin Syahputra', 'dob' => '2005-04-05', 'angkatan' => '2023'],
                    ['nim' => '120180002', 'name' => 'Zahra Nurul Aini', 'dob' => '2005-09-15', 'angkatan' => '2023'],
                ],
            ],
            [
                'prodi' => 'Kemaritiman',
                'faculty' => 'Jurusan Kemaritiman',
                'hmj' => 'HTK (Himpunan Taruna Kemaritiman)',
                'scenario' => 'Aklamasi (2 Bilik: BEM & MPM, HMJ Ditetapkan Aklamasi 1 Calon)',
                'students' => [
                    ['nim' => '120190001', 'name' => 'Surya Taruna', 'dob' => '2004-06-08', 'angkatan' => '2022'],
                    ['nim' => '120190002', 'name' => 'Citra Maharani', 'dob' => '2004-12-14', 'angkatan' => '2022'],
                ],
            ],
        ];

        // 1. Masukkan akun pemilih ke database
        foreach ($delegates as $d) {
            foreach ($d['students'] as $s) {
                // Buat data di Master Mahasiswa (BAAK / Siakad)
                PemilihMaster::updateOrCreate(
                    ['nim' => $s['nim']],
                    [
                        'nama' => $s['name'],
                        'prodi' => $d['prodi'],
                        'angkatan' => $s['angkatan'],
                        'tanggal_lahir_hash' => hash('sha256', $s['dob']),
                    ]
                );

                // Buat akun Pemilih DPT Resmi yang SIAP LOGIN
                Voter::updateOrCreate(
                    ['nim' => $s['nim']],
                    [
                        'name' => $s['name'],
                        'faculty' => $d['faculty'],
                        'study_program' => $d['prodi'],
                        'angkatan' => $s['angkatan'],
                        'academic_status' => 'aktif',
                        'dpt_status' => 'DPT',
                        'token_plain' => $defaultPin,
                        'token_generated_at' => now(),
                        'password' => $hashedPin,
                        'is_active' => true,
                        'pendaftaran_status' => 'aktif',
                        'login_attempts' => 0,
                    ]
                );
            }
        }

        // 2. Setel skenario demonstrasi OPSI B (Aklamasi):
        // Kita jadikan Bahasa (LEDSA) dan Kemaritiman (HTK) hanya memiliki 1 calon tunggal
        // agar delegasi bisa memperagakan fitur AKLAMASI di hadapan mahasiswa!
        $ledsa = ElectionCategory::where('code', 'KAHIM_LEDSA_2026')->first();
        if ($ledsa) {
            // Sisakan hanya calon nomor 1 (Calon Tunggal Aklamasi)
            Candidate::where('election_category_id', $ledsa->id)->where('candidate_number', '>', 1)->delete();
            $c1 = Candidate::where('election_category_id', $ledsa->id)->where('candidate_number', 1)->first();
            if ($c1) {
                $c1->update([
                    'leader_name' => 'Annisa Fitriani',
                    'leader_nim' => '120180011',
                    'vision' => 'Mewujudkan Mahasiswa Bahasa yang Berwawasan Global & Komunikatif.',
                    'mission' => "1. Meningkatkan literasi bahasa asing terapan.\n2. Diplomasi publik kemahasiswaan.",
                    'is_active' => true,
                ]);
            }
        }

        $htk = ElectionCategory::where('code', 'KAHIM_HTK_2026')->first();
        if ($htk) {
            // Sisakan hanya calon nomor 1 (Calon Tunggal Aklamasi)
            Candidate::where('election_category_id', $htk->id)->where('candidate_number', '>', 1)->delete();
            $c1 = Candidate::where('election_category_id', $htk->id)->where('candidate_number', 1)->first();
            if ($c1) {
                $c1->update([
                    'leader_name' => 'Surya Pratama',
                    'leader_nim' => '120190011',
                    'vision' => 'Jiwa Korsa Taruna Kemaritiman yang Disiplin, Tangguh, dan Berwawasan Samudera.',
                    'mission' => "1. Penguatan keahlian navigasi pelayaran nasional.\n2. Kebersamaan jiwa korsa taruna maritim.",
                    'is_active' => true,
                ]);
            }
        }

        // Kategori lain (Teknik Informatika, Mesin, Elektro, Sipil, Perkapalan, Niaga) tetap 2 Paslon (Kompetisi)
        // BEM Polbeng & MPM Polbeng tetap 2 Paslon (Kompetisi)
    }
}
