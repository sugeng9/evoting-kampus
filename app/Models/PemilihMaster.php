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
}
