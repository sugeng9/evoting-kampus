<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
        ];
    }

    protected static bool $schemaEnsured = false;

    protected static function booted(): void
    {
        static::ensureSchema();
    }

    /**
     * Pastikan skema tabel users mendukung kolom 2FA dan peran secara otomatis
     */
    public static function ensureSchema(): void
    {
        if (static::$schemaEnsured) {
            return;
        }
        static::$schemaEnsured = true;

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
                \Illuminate\Support\Facades\Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'role')) {
                        $table->string('role', 50)->default('panitia');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
                        $table->boolean('is_active')->default(true);
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'two_factor_secret')) {
                        $table->longText('two_factor_secret')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                        $table->longText('two_factor_recovery_codes')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                        $table->timestamp('two_factor_confirmed_at')->nullable();
                    }
                });

                // Perbesar tipe kolom jika sebelumnya terbuat sebagai VARCHAR sempit di MySQL
                try {
                    \Illuminate\Support\Facades\DB::statement('ALTER TABLE users MODIFY COLUMN two_factor_secret LONGTEXT NULL');
                    \Illuminate\Support\Facades\DB::statement('ALTER TABLE users MODIFY COLUMN two_factor_recovery_codes LONGTEXT NULL');
                } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Cek apakah 2FA Google Authenticator sudah aktif & dikonfirmasi
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !empty($this->two_factor_secret) && $this->two_factor_confirmed_at !== null;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isPanitia(): bool
    {
        return in_array($this->role, ['super_admin', 'panitia', 'committee']);
    }

    public function isHelpdesk(): bool
    {
        return in_array($this->role, ['super_admin', 'helpdesk']);
    }

    public function isAuditor(): bool
    {
        return in_array($this->role, ['super_admin', 'auditor']);
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Administrator KPR',
            'panitia' => 'Panitia Pemira',
            'helpdesk' => 'Petugas Helpdesk & Sengketa',
            'auditor' => 'Auditor Pengawas (Read-Only)',
            default => ucfirst($this->role ?? 'Panitia'),
        };
    }
}
