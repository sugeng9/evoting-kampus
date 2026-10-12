<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PemilihMaster;
use App\Models\User;
use App\Models\SystemPhase;
use Illuminate\Support\Facades\Hash;

class PemilihMasterSeeder extends Seeder
{
    public function run(): void
    {
        // Data master mahasiswa untuk pengujian pendaftaran mandiri (NIM & Tanggal Lahir resmi)
        $sampleStudents = [
            [
                'nim' => '120140001',
                'nama' => 'Syahadan Mahasiswa Uji Coba',
                'prodi' => 'Teknik Informatika',
                'angkatan' => '2022',
                'tanggal_lahir_hash' => hash('sha256', '2004-05-15'), // Tanggal lahir: 2004-05-15
            ],
            [
                'nim' => '120140002',
                'nama' => 'Ahmad Fauzi',
                'prodi' => 'Teknik Informatika',
                'angkatan' => '2022',
                'tanggal_lahir_hash' => hash('sha256', '2004-01-20'), // Tanggal lahir: 2004-01-20
            ],
            [
                'nim' => '120120001',
                'nama' => 'Budi Santoso',
                'prodi' => 'Teknik Mesin',
                'angkatan' => '2021',
                'tanggal_lahir_hash' => hash('sha256', '2003-11-10'), // Tanggal lahir: 2003-11-10
            ],
            [
                'nim' => '120130001',
                'nama' => 'Citra Lestari',
                'prodi' => 'Teknik Elektro',
                'angkatan' => '2023',
                'tanggal_lahir_hash' => hash('sha256', '2005-08-25'), // Tanggal lahir: 2005-08-25
            ],
        ];

        foreach ($sampleStudents as $student) {
            PemilihMaster::updateOrCreate(['nim' => $student['nim']], $student);
        }

        // Muat 5 akun demo registrasi berbagai jurusan
        PemilihMaster::ensureRegistrationDummyData();

        // Akun Helpdesk
        User::updateOrCreate(
            ['email' => 'helpdesk1@polbeng.ac.id'],
            [
                'name' => 'Petugas Helpdesk A',
                'username' => 'helpdesk1',
                'email' => 'helpdesk1@polbeng.ac.id',
                'password' => Hash::make('Helpdesk@123!'),
                'role' => 'helpdesk',
                'is_active' => true,
            ]
        );

        // Fase awal sistem
        SystemPhase::firstOrCreate(
            ['id' => 1],
            [
                'fase' => 'pendaftaran',
                'pendaftaran_terbuka' => true,
                'catatan' => 'Fase awal: pendaftaran pemilih dibuka.',
            ]
        );

        $this->command->info('PemilihMasterSeeder: 4 data master mahasiswa uji coba, staf helpdesk, dan fase awal berhasil disiapkan.');
    }
}
