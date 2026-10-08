<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KodePendaftaran extends Model
{
    protected $table = 'kode_pendaftaran';

    protected $fillable = [
        'nim',
        'kode_hash',
        'dibuat_oleh',
        'disetujui_oleh',
        'expired_at',
        'used_at'
    ];

    protected $casts = [
        'expired_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function isValid(): bool
    {
        return now()->isBefore($this->expired_at) && $this->used_at === null;
    }

    public static function createForNim(string $nim, int $dibuatOleh): array
    {
        $plainKode = Str::random(32);
        
        $model = self::create([
            'nim' => $nim,
            'kode_hash' => hash('sha256', $plainKode),
            'dibuat_oleh' => $dibuatOleh,
            'expired_at' => now()->addHours(24),
        ]);

        return [
            'model' => $model,
            'plain_kode' => $plainKode,
        ];
    }
}
