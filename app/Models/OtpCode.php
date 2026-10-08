<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $table = 'otp_codes';

    protected $fillable = [
        'nim',
        'kode_hash',
        'email',
        'percobaan',
        'expired_at',
        'used_at'
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public ?string $plain_otp = null;

    public function isExpired(): bool
    {
        return now()->isAfter($this->expired_at);
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function attemptsExceeded(): bool
    {
        return $this->percobaan >= 5;
    }

    public function verify(string $plainOtp): bool
    {
        $this->increment('percobaan');

        if (hash('sha256', $plainOtp) === $this->kode_hash) {
            $this->update(['used_at' => now()]);
            return true;
        }

        return false;
    }

    public static function createForNim(string $nim, string $email): self
    {
        $plainOtp = (string) random_int(100000, 999999);
        
        $otpCode = self::create([
            'nim' => $nim,
            'email' => $email,
            'kode_hash' => hash('sha256', $plainOtp),
            'expired_at' => now()->addMinutes(10),
        ]);

        $otpCode->plain_otp = $plainOtp;

        return $otpCode;
    }
}
