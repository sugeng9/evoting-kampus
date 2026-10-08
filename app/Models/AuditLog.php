<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'event_type',
        'ip_address',
        'user_agent',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public static function log(string $eventType, string $actorType = 'system', ?int $actorId = null, array $payload = []): self
    {
        return self::create([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'event_type' => $eventType,
            'ip_address' => request()->ip(),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
