<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BallotBox extends Model
{
    use HasUuids;

    protected $table = 'ballot_boxes';
    public $timestamps = false; // Hanya menggunakan created_at untuk menjaga kesederhanaan dan performa

    protected $fillable = [
        'id',
        'election_category_id',
        'candidate_id',
        'ballot_hash',
        'verification_code',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ElectionCategory::class, 'election_category_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}
