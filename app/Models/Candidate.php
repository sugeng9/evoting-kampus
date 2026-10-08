<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    protected $fillable = [
        'election_category_id',
        'candidate_number',
        'leader_name',
        'vice_leader_name',
        'leader_nim',
        'vice_leader_nim',
        'photo_path',
        'vision',
        'mission',
        'programs_work',
        'is_active',
    ];

    protected $casts = [
        'candidate_number' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ElectionCategory::class, 'election_category_id');
    }

    public function ballots(): HasMany
    {
        return $this->hasMany(BallotBox::class, 'candidate_id');
    }
}
