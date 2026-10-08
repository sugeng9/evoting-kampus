<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoterParticipation extends Model
{
    protected $table = 'voter_participations';

    protected $fillable = [
        'voter_id',
        'election_category_id',
        'receipt_hash',
        'ip_address',
        'user_agent',
        'voted_at',
    ];

    protected $casts = [
        'voted_at' => 'datetime',
    ];

    public function voter(): BelongsTo
    {
        return $this->belongsTo(Voter::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ElectionCategory::class, 'election_category_id');
    }
}
