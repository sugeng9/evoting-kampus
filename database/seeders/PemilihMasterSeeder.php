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
        // Data master mahasiswa bersumber dari import berkas resmi BAAK Kampus di Panel Admin
        // (Data dummy mahasiswa ditiadakan agar sistem bersih 100% untuk sosialisasi resmi)

        // Create helpdesk user 1 (Petugas A)
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

        // Create helpdesk user 2 (Petugas B)
        User::updateOrCreate(
            ['email' => 'helpdesk2@polbeng.ac.id'],
            [
                'name' => 'Petugas Helpdesk B',
                'username' => 'helpdesk2',
                'email' => 'helpdesk2@polbeng.ac.id',
                'password' => Hash::make('Helpdesk@123!'),
                'role' => 'helpdesk',
                'is_active' => true,
            ]
        );

        // Ensure admin exists
        User::updateOrCreate(
            ['email' => 'admin@polbeng.ac.id'],
            [
                'name' => 'Admin KPU-M Polbeng',
                'username' => 'admin',
                'email' => 'admin@polbeng.ac.id',
                'password' => Hash::make('Admin@123!'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // Create default system phase
        SystemPhase::firstOrCreate(
            ['id' => 1],
            [
                'fase' => 'pendaftaran',
                'pendaftaran_terbuka' => true,
                'catatan' => 'Fase awal: pendaftaran pemilih dibuka.',
            ]
        );

        $this->command->info('PemilihMasterSeeder: 10 data master, 2 staf, 1 fase awal berhasil dibuat.');
    }
}
