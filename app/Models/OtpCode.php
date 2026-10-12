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
        return $this->percobaan >= 3;
    }

    public function verify(string $plainOtp): bool
    {
        if ($this->isExpired() || $this->isUsed() || $this->attemptsExceeded()) {
            return false;
        }

        $this->increment('percobaan');

        if (hash('sha256', $plainOtp) === $this->kode_hash) {
            // Sekali pakai: catat waktu pakai dan hanguskan expired_at agar tidak bisa dipakai ulang
            $this->update([
                'used_at' => now(),
                'expired_at' => now(),
            ]);
            return true;
        }

        // Jika salah hingga 3 kali, otomatis hanguskan OTP
        if ($this->percobaan >= 3) {
            $this->update(['expired_at' => now()]);
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
            'expired_at' => now()->addMinutes(5),
        ]);

        $otpCode->plain_otp = $plainOtp;

        return $otpCode;
    }
}
