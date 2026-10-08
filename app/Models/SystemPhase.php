<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemPhase extends Model
{
    protected $table = 'system_phases';

    protected $fillable = [
        'fase',
        'pendaftaran_terbuka',
        'catatan',
        'diubah_oleh'
    ];

    protected $casts = [
        'pendaftaran_terbuka' => 'boolean',
    ];

    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            ['fase' => 'pendaftaran', 'pendaftaran_terbuka' => true]
        );
    }

    public function allowsPendaftaran(): bool
    {
        return $this->fase === 'pendaftaran' || ($this->fase === 'sengketa' && $this->pendaftaran_terbuka);
    }

    public function allowsSengketa(): bool
    {
        return $this->fase === 'pendaftaran' || $this->fase === 'sengketa';
    }

    public function allowsLogin(): bool
    {
        return $this->fase === 'aktif' || $this->fase === 'voting';
    }

    public function isFrozen(): bool
    {
        return $this->fase === 'aktif' || $this->fase === 'voting';
    }
}
