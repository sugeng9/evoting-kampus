<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sengketa extends Model
{
    protected $table = 'sengketa';

    protected $fillable = [
        'nim',
        'alasan',
        'pelapor_kontak',
        'status',
        'ditangani_oleh',
        'disetujui_oleh',
        'catatan_petugas',
        'ktm_diverifikasi',
        'bukti_ktm_path',
        'diselesaikan_at',
    ];

    protected $casts = [
        'diselesaikan_at' => 'datetime',
        'ktm_diverifikasi' => 'boolean',
        'pelapor_kontak' => 'encrypted',
    ];

    public function petugas_a(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }

    public function petugas_b(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    // Alias camelCase untuk kompatibilitas with() di controller
    public function petugasA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }

    public function petugasB(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function bisaDicabut(): bool
    {
        return $this->ktm_diverifikasi && $this->ditangani_oleh !== null;
    }

    public function bisaDisetujui(int $userId): bool
    {
        return $this->ditangani_oleh !== null && 
               $this->disetujui_oleh === null && 
               $userId !== $this->ditangani_oleh;
    }

    public function hasBuktiKtm(): bool
    {
        return !empty($this->bukti_ktm_path) && \Illuminate\Support\Facades\Storage::disk('local')->exists($this->bukti_ktm_path);
    }

    public function deleteBuktiKtmFile(): void
    {
        if (!empty($this->bukti_ktm_path)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($this->bukti_ktm_path);
            $this->bukti_ktm_path = null;
            $this->save();
        }
    }
}
