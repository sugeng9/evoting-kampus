<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectionSetting extends Model
{
    protected $table = 'election_settings';

    protected $fillable = [
        'election_name',
        'status',
        'voting_starts_at',
        'voting_ends_at',
        'is_quick_count_public',
        'require_two_step_confirmation',
    ];

    protected $casts = [
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime',
        'is_quick_count_public' => 'boolean',
        'require_two_step_confirmation' => 'boolean',
    ];

    public static function current(): self
    {
        $setting = self::firstOrCreate(
            ['id' => 1],
            [
                'election_name' => 'PEMIRA POLITEKNIK NEGERI BENGKALIS 2026',
                'status' => 'OPEN',
                'voting_starts_at' => now()->startOfDay(),
                'voting_ends_at' => now()->addDays(2)->endOfDay(),
                'is_quick_count_public' => true,
                'require_two_step_confirmation' => true,
            ]
        );

        return $setting;
    }

    public function isOpen(): bool
    {
        if ($this->status !== 'OPEN') {
            return false;
        }

        $now = now();
        if ($this->voting_starts_at && $now->lt($this->voting_starts_at)) {
            return false;
        }

        if ($this->voting_ends_at && $now->gt($this->voting_ends_at)) {
            return false;
        }

        return true;
    }
}
