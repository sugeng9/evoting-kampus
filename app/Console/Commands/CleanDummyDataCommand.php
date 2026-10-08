<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Voter;
use App\Models\PemilihMaster;
use App\Models\OtpCode;
use App\Models\Sengketa;
use App\Models\KodePendaftaran;
use App\Models\BallotBox;
use App\Models\VoterParticipation;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanDummyDataCommand extends Command
{
    /**
     * Nama dan signature perintah artisan
     */
    protected $signature = 'pemira:clean';

    /**
     * Deskripsi perintah artisan
     */
    protected $description = 'Bersihkan seluruh data dummy pemilih, kuitansi suara simulasi, dan histori uji coba untuk persiapan sosialisasi resmi Pemira Polbeng';

    public function handle(): int
    {
        $this->warn('Membersihkan seluruh data dummy & uji coba untuk Sosialisasi Resmi Pemira Polbeng...');

        // 1. Matikan foreign key check sementara agar truncate aman
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 2. Kosongkan tabel voters (pemilih dummy)
        if (Schema::hasTable('voters')) {
            $voterCount = Voter::count();
            Voter::truncate();
            $this->info("✓ Data pemilih ({$voterCount} akun dummy) berhasil dikosongkan.");
        }

        // 3. Kosongkan riwayat suara & kuitansi simulasi
        if (Schema::hasTable('ballot_boxes')) {
            $ballotCount = BallotBox::count();
            BallotBox::truncate();
            $this->info("✓ Kotak suara ({$ballotCount} suara) dibersihkan ke 0.");
        }

        if (Schema::hasTable('voter_participations')) {
            $partCount = VoterParticipation::count();
            VoterParticipation::truncate();
            $this->info("✓ Riwayat coblos ({$partCount} pemilih) dibersihkan ke 0.");
        }

        // 4. Kosongkan OTP, Sengketa & Kode Pendaftaran simulasi
        if (Schema::hasTable('otp_codes')) {
            OtpCode::truncate();
            $this->info("✓ Riwayat kode OTP dibersihkan.");
        }

        if (Schema::hasTable('sengketa')) {
            Sengketa::truncate();
            $this->info("✓ Tiket sengketa simulasi dibersihkan.");
        }

        if (Schema::hasTable('kode_pendaftaran')) {
            KodePendaftaran::truncate();
            $this->info("✓ Kode pemulihan pendaftaran simulasi dibersihkan.");
        }

        // 5. Kosongkan Data Master Mahasiswa simulasi
        if (Schema::hasTable('pemilih_master')) {
            $masterCount = PemilihMaster::count();
            PemilihMaster::truncate();
            $this->info("✓ Data master mahasiswa ({$masterCount} data simulasi) dibersihkan.");
        }

        // 6. Bersihkan log audit pengujian
        if (Schema::hasTable('audit_logs')) {
            AuditLog::truncate();
            $this->info("✓ Log audit uji coba dibersihkan.");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->newLine();
        $this->info('========================================================================');
        $this->info('  SEMUA DATA DUMMY TELAH BERSIH TOTAL!                                 ');
        $this->info('  Website E-Voting Pemira Polbeng Siap Ditampilkan untuk Sosialisasi    ');
        $this->info('========================================================================');
        $this->line('1. Portal Login Pemilih: Bersih, rapi, dan resmi (tanpa kartu demo).');
        $this->line('2. Akun Admin KPU tetap aktif: admin@polbeng.ac.id (Password: Admin@123!)');
        $this->line('3. 10 Kategori Ormawa & Paslon tetap terjaga untuk dipresentasikan.');
        $this->line('4. DPT Siap di-upload via menu: "Upload DPT / DPS BAAK" saat pleno kampus.');

        return Command::SUCCESS;
    }
}
