<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'aktor',
        'aksi',
        'target',
        'actor_type',
        'actor_id',
        'event_type',
        'ip_address',
        'user_agent',
        'payload',
        'hash_sebelumnya',
        'hash_ini',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Booted: Proteksi Immutability (Append-Only Enforcement)
     * Dilarang keras melakukan update atau delete catatan audit dari aplikasi!
     */
    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('SECURITY_VIOLATION: AuditLog bersifat append-only (immutable). Mengubah rekaman dilarang keras demi integritas sistem!');
        });

        static::deleting(function () {
            throw new RuntimeException('SECURITY_VIOLATION: AuditLog bersifat append-only (immutable). Menghapus rekaman log audit dilarang keras demi integritas sistem!');
        });
    }

    /**
     * Blokir pemanggilan method delete langsung
     */
    public function delete(): ?bool
    {
        throw new RuntimeException('SECURITY_VIOLATION: Catatan AuditLog bersifat immutable (append-only) dan tidak dapat dihapus melalui aplikasi.');
    }

    /**
     * Blokir pemanggilan method update langsung
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new RuntimeException('SECURITY_VIOLATION: Catatan AuditLog bersifat immutable (append-only) dan tidak dapat diubah melalui aplikasi.');
    }

    /**
     * Hitung SHA-256 Kriptografis untuk Hash Berantai (Blockchain-style Chaining)
     */
    public static function calculateHash(
        ?string $aktor,
        ?string $aksi,
        ?string $target,
        ?string $ipAddress,
        mixed $createdAt,
        mixed $payload,
        string $hashSebelumnya
    ): string {
        $payloadJson = '';
        if (is_array($payload)) {
            $sortedPayload = $payload;
            ksort($sortedPayload);
            $payloadJson = json_encode($sortedPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } elseif (is_string($payload)) {
            $payloadJson = $payload;
        }

        $timestamp = '';
        if ($createdAt instanceof CarbonInterface) {
            $timestamp = $createdAt->format('Y-m-d H:i:s');
        } elseif (!empty($createdAt)) {
            $timestamp = date('Y-m-d H:i:s', strtotime((string) $createdAt));
        } else {
            $timestamp = date('Y-m-d H:i:s');
        }

        $raw = implode('|', [
            trim((string) $aktor),
            trim((string) $aksi),
            trim((string) $target),
            trim((string) $ipAddress),
            $timestamp,
            $payloadJson,
            trim($hashSebelumnya),
        ]);

        return hash('sha256', $raw);
    }

    /**
     * Catat peristiwa audit log baru dengan Rantai Hash Otomatis & Concurrency Lock
     */
    public static function record(string $aksi, ?string $target = null, array $payload = [], ?string $aktor = null): self
    {
        // 1. Tentukan identitas aktor secara otomatis bila tidak diberikan
        if ($aktor === null) {
            if (Auth::guard('web')->check()) {
                $user = Auth::guard('web')->user();
                $aktor = "admin:{$user->id} ({$user->name})";
            } elseif (Auth::guard('voter')->check()) {
                $voter = Auth::guard('voter')->user();
                $aktor = "voter:{$voter->nim} ({$voter->name})";
            } else {
                $aktor = 'system';
            }
        }

        $ip = request()->ip() ?? '127.0.0.1';
        $userAgent = substr(request()->userAgent() ?? 'CLI/System', 0, 500);
        $createdAt = now();

        // 2. Transaksi Atomik dengan Pessimistic Lock untuk menjamin rantai hash tidak pernah bercabang (no race condition)
        return DB::transaction(function () use ($aksi, $target, $payload, $aktor, $ip, $userAgent, $createdAt) {
            $lastLog = static::orderBy('id', 'desc')->lockForUpdate()->first();
            $prevHash = ($lastLog && !empty($lastLog->hash_ini))
                ? $lastLog->hash_ini
                : str_repeat('0', 64);

            $hashThis = static::calculateHash(
                $aktor,
                $aksi,
                $target,
                $ip,
                $createdAt,
                $payload,
                $prevHash
            );

            $log = new static();
            $log->aktor = $aktor;
            $log->aksi = $aksi;
            $log->target = $target;
            $log->event_type = $aksi;
            $log->actor_type = str_contains($aktor, 'admin:') ? 'admin' : (str_contains($aktor, 'voter:') ? 'voter' : 'system');
            $log->ip_address = $ip;
            $log->user_agent = $userAgent;
            $log->payload = $payload;
            $log->hash_sebelumnya = $prevHash;
            $log->hash_ini = $hashThis;
            $log->created_at = $createdAt;

            $log->saveQuietly();

            return $log;
        }, 5);
    }

    /**
     * Backward-Compatible Wrapper untuk pemanggilan `AuditLog::log(...)` lama di seluruh sistem
     */
    public static function log(string $eventType, string $actorType = 'system', ?int $actorId = null, array $payload = []): self
    {
        $aktor = $actorType;
        if ($actorId) {
            $aktor .= ":{$actorId}";
        }

        $target = null;
        if (isset($payload['target'])) {
            $target = (string) $payload['target'];
        } elseif (isset($payload['nim'])) {
            $target = "voter:{$payload['nim']}";
        } elseif (isset($payload['candidate_id'])) {
            $target = "candidate:{$payload['candidate_id']}";
        } elseif (isset($payload['category_id'])) {
            $target = "category:{$payload['category_id']}";
        }

        return self::record(
            aksi: $eventType,
            target: $target,
            payload: $payload,
            aktor: $aktor
        );
    }
}
