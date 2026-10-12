<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voter extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'voters';

    protected $fillable = [
        'nim',
        'name',
        'faculty',
        'study_program',
        'angkatan',
        'academic_status',
        'dpt_status',
        'password',
        'token_plain',
        'token_generated_at',
        'plain_token_salt',
        'is_active',
        'last_login_ip',
        'last_login_user_agent',
        'last_login_at',
        'pendaftaran_status',
        'email',
        'no_hp',
        'registered_at',
        'activated_at',
        'login_attempts',
        'locked_until',
        'current_session_id',
        'dob',
        'tanggal_lahir',
        'email_hash',
        'sudah_memilih',
        'voted_at',
    ];

    protected $hidden = [
        'password',
        'plain_token_salt',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sudah_memilih' => 'boolean',
            'voted_at' => 'datetime',
            'last_login_at' => 'datetime',
            'token_generated_at' => 'datetime',
            'password' => 'hashed',
            'email' => 'encrypted',
            'no_hp' => 'encrypted',
            'dob' => 'encrypted',
            'tanggal_lahir' => 'encrypted',
            'registered_at' => 'datetime',
            'activated_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    /**
     * Hitung Blind Index HMAC-SHA256 untuk pencarian/indeks unik data terenkripsi
     */
    public static function hashBlindIndex(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $key = config('app.blind_index_key', env('BLIND_INDEX_KEY', config('app.key')));
        return hash_hmac('sha256', strtolower(trim($value)), (string) $key);
    }

    /**
     * Cari pemilih berdasarkan email menggunakan Blind Index HMAC-SHA256
     */
    public static function findByEmail(string $email): ?self
    {
        return static::where('email_hash', static::hashBlindIndex($email))->first();
    }

    protected static function booted(): void
    {
        static::saving(function (Voter $voter) {
            if ($voter->isDirty('email') && !empty($voter->email)) {
                $voter->email_hash = static::hashBlindIndex($voter->email);
            }
        });
    }

    /**
     * Skema database telah dimigrasikan secara formal melalui migration Laravel.
     * Method ini dipertahankan sebagai no-op demi kompatibilitas pemanggilan lama.
     */
    public static function ensureSchema(): void
    {
        // No-op: DDL tidak boleh dijalankan saat runtime HTTP request untuk mencegah Metadata Lock
    }

    /**
     * Cek apakah mahasiswa memenuhi syarat akademik untuk memilih (Hanya Aktif & Magang)
     */
    public function isEligible(): bool
    {
        $status = strtolower($this->academic_status ?? 'aktif');
        return in_array($status, ['aktif', 'magang']) && $this->is_active;
    }

    /**
     * Label Status Akademik Mahasiswa
     */
    public function getAcademicStatusLabelAttribute(): string
    {
        return match (strtolower($this->academic_status ?? 'aktif')) {
            'magang' => 'Sedang Magang / MBKM',
            'cuti' => 'Cuti Akademik',
            'do' => 'Drop Out (DO)',
            'wisuda' => 'Sudah Wisuda / Alumni',
            'bermasalah' => 'Bermasalah Akademik / Skorsing',
            default => 'Aktif (Reguler)',
        };
    }

    /**
     * Cek apakah pemilih sudah memiliki PIN Token
     */
    public function hasGeneratedToken(): bool
    {
        return !empty($this->token_plain);
    }

    /**
     * Relasi ke presensi partisipasi pemilihan
     */
    public function participations(): HasMany
    {
        return $this->hasMany(VoterParticipation::class, 'voter_id');
    }

    /**
     * Cek apakah pemilih sudah pernah memilih di kategori tertentu
     */
    public function hasVotedForCategory(int $categoryId): bool
    {
        return $this->participations()->where('election_category_id', $categoryId)->exists();
    }

    /**
     * Cek apakah pemilih sudah pernah menggunakan hak suara di sistem (global)
     */
    public function hasVoted(): bool
    {
        return (bool) $this->sudah_memilih || $this->participations()->exists();
    }

    /**
     * Tandai pemilih telah menggunakan hak suaranya
     */
    public function markAsVoted(): void
    {
        $this->sudah_memilih = true;
        $this->voted_at = now();
        $this->save();
    }

    public function isPending(): bool
    {
        return $this->pendaftaran_status === 'pending';
    }

    public function isAktif(): bool
    {
        return $this->pendaftaran_status === 'aktif' && $this->is_active;
    }

    public function isDicabut(): bool
    {
        return $this->pendaftaran_status === 'dicabut';
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function lockoutMinutes(): int
    {
        if ($this->login_attempts >= 7) {
            return 60;
        } elseif ($this->login_attempts >= 5) {
            return 15;
        } elseif ($this->login_attempts >= 3) {
            return 5;
        }
        return 0;
    }

    public function incrementLoginAttempts(): void
    {
        $this->increment('login_attempts');
        $minutes = $this->lockoutMinutes();
        if ($minutes > 0) {
            $this->update(['locked_until' => now()->addMinutes($minutes)]);
        }
    }

    public function clearLoginAttempts(): void
    {
        $this->update([
            'login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    public function sengketa(): HasMany
    {
        return $this->hasMany(Sengketa::class, 'nim', 'nim');
    }
}
