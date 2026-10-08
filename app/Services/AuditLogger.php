<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Voter;
use Illuminate\Support\Facades\Log;

class AuditLogger
{
    /**
     * Catat peristiwa audit log ke tabel audit_logs dengan skema resmi:
     * event_type, actor_type, actor_id, ip_address, user_agent, payload, created_at
     */
    public static function log(string $aksi, string|array $keterangan = '', $aktor = null, ?string $aktorTipe = null): void
    {
        try {
            if (!$aktor && auth()->check()) {
                $aktor = auth()->id();
                $aktorTipe = auth()->getDefaultDriver();
            }

            // Tentukan actor_id jika numerik atau cari id voter jika berupa NIM
            $actorId = null;
            if (is_numeric($aktor)) {
                $actorId = (int) $aktor;
            } elseif (!empty($aktor)) {
                $voter = Voter::where('nim', $aktor)->first();
                if ($voter) {
                    $actorId = $voter->id;
                }
            }

            $payload = is_array($keterangan) ? $keterangan : ['keterangan' => $keterangan];
            if ($aktor) {
                $payload['aktor'] = (string) $aktor;
            }

            AuditLog::create([
                'event_type' => $aksi,
                'actor_type' => $aktorTipe ?: 'system',
                'actor_id'   => $actorId,
                'ip_address' => request()->ip(),
                'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
                'payload'    => $payload,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Fail gracefully agar pencatatan log tidak menghentikan alur aplikasi pengguna
            Log::warning("Gagal menulis audit log ({$aksi}): " . $e->getMessage());
        }
    }
}
