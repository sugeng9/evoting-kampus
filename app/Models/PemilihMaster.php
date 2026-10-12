<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PemilihMaster extends Model
{
    protected $table = 'pemilih_master';

    protected $fillable = [
        'nim',
        'nama',
        'prodi',
        'angkatan',
        'tanggal_lahir_hash',
    ];

    public function verifyTanggalLahir(string $tanggalLahir): bool
    {
        $clean = trim($tanggalLahir);
        if (hash('sha256', $clean) === $this->tanggal_lahir_hash) {
            return true;
        }

        try {
            $normalized = \Carbon\Carbon::parse($clean)->format('Y-m-d');
            if (hash('sha256', $normalized) === $this->tanggal_lahir_hash) {
                return true;
            }
        } catch (\Throwable) {}

        return false;
    }

    public function sudahTerdaftar(): bool
    {
        $voter = $this->voter;
        if (!$voter) {
            return false;
        }
        return $voter->pendaftaran_status !== 'dicabut';
    }

    public function voter(): HasOne
    {
        return $this->hasOne(Voter::class, 'nim', 'nim');
    }

    public static function findByNim(string $nim): ?self
    {
        return self::where('nim', $nim)->first();
    }

    /**
     * Dataset Mahasiswa Resmi untuk Uji Coba Pendaftaran Mandiri (Berbagai Jurusan)
     */
    public static function getRegistrationDummyList(): array
    {
        return [
            [
                'nim' => '120140901',
                'nama' => 'Rizky Perdana',
                'prodi' => 'Teknik Informatika',
                'angkatan' => '2022',
                'tanggal_lahir' => '2004-05-15',
                'tanggal_lahir_display' => '15 Mei 2004',
                'email_saran' => 'rizky.demo@polbeng.ac.id',
            ],
            [
                'nim' => '120120902',
                'nama' => 'Faisal Maulana',
                'prodi' => 'Teknik Mesin',
                'angkatan' => '2021',
                'tanggal_lahir' => '2003-10-20',
                'tanggal_lahir_display' => '20 Oktober 2003',
                'email_saran' => 'faisal.demo@polbeng.ac.id',
            ],
            [
                'nim' => '120130903',
                'nama' => 'Nadia Safitri',
                'prodi' => 'Teknik Elektro',
                'angkatan' => '2023',
                'tanggal_lahir' => '2005-02-14',
                'tanggal_lahir_display' => '14 Februari 2005',
                'email_saran' => 'nadia.demo@polbeng.ac.id',
            ],
            [
                'nim' => '120170904',
                'nama' => 'Dimas Wahyudi',
                'prodi' => 'Administrasi Niaga',
                'angkatan' => '2022',
                'tanggal_lahir' => '2004-08-08',
                'tanggal_lahir_display' => '08 Agustus 2004',
                'email_saran' => 'dimas.demo@polbeng.ac.id',
            ],
            [
                'nim' => '120190905',
                'nama' => 'Arya Samudra',
                'prodi' => 'Kemaritiman',
                'angkatan' => '2022',
                'tanggal_lahir' => '2004-12-01',
                'tanggal_lahir_display' => '01 Desember 2004',
                'email_saran' => 'arya.demo@polbeng.ac.id',
            ],
        ];
    }

    /**
     * Memastikan data master mahasiswa uji coba tersedia di database BAAK
     */
    public static function ensureRegistrationDummyData(): void
    {
        try {
            foreach (self::getRegistrationDummyList() as $item) {
                self::updateOrCreate(
                    ['nim' => $item['nim']],
                    [
                        'nama' => $item['nama'],
                        'prodi' => $item['prodi'],
                        'angkatan' => $item['angkatan'],
                        'tanggal_lahir_hash' => hash('sha256', $item['tanggal_lahir']),
                    ]
                );
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Reset status pendaftaran akun uji coba agar siap diuji coba ulang
     */
    public static function resetDummyRegistration(string $nim): bool
    {
        $dummyNims = array_column(self::getRegistrationDummyList(), 'nim');
        // Sertakan juga NIM demo lama jika ada
        $dummyNims[] = '120140001';
        $dummyNims[] = '120140002';

        if (!in_array($nim, $dummyNims)) {
            return false;
        }

        try {
            // Hapus voter agar berstatus belum pernah mendaftar
            \App\Models\Voter::where('nim', $nim)->delete();
            // Hapus kode OTP lama
            \App\Models\OtpCode::where('nim', $nim)->delete();
            // Bersihkan batasan rate limit pengujian
            \Illuminate\Support\Facades\RateLimiter::clear('reg-otp-nim:' . \Illuminate\Support\Str::lower($nim));
            \Illuminate\Support\Facades\RateLimiter::clear('reg-fail-nim:' . \Illuminate\Support\Str::lower($nim));
            \Illuminate\Support\Facades\RateLimiter::clear('reg-resend-cooldown:' . \Illuminate\Support\Str::lower($nim));
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
