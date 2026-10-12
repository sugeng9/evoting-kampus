<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * FASE 9: Audit Log Tahan Manipulasi (Cryptographic Hash Chaining)
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'aktor')) {
                $table->string('aktor', 191)->nullable()->after('id');
            }
            if (!Schema::hasColumn('audit_logs', 'aksi')) {
                $table->string('aksi', 100)->nullable()->after('aktor');
            }
            if (!Schema::hasColumn('audit_logs', 'target')) {
                $table->string('target', 191)->nullable()->after('aksi');
            }
            if (!Schema::hasColumn('audit_logs', 'hash_sebelumnya')) {
                $table->string('hash_sebelumnya', 64)->nullable()->index()->after('payload');
            }
            if (!Schema::hasColumn('audit_logs', 'hash_ini')) {
                $table->string('hash_ini', 64)->nullable()->index()->after('hash_sebelumnya');
            }
        });

        // Retroactive Hash Chaining: Inisialisasi rantai kriptografis untuk rekaman lama yang sudah ada
        $existingLogs = DB::table('audit_logs')->orderBy('id', 'asc')->get();
        if ($existingLogs->isNotEmpty()) {
            $prevHash = str_repeat('0', 64);

            foreach ($existingLogs as $log) {
                $aktor = $log->aktor ?: (($log->actor_type ?? 'system') . ($log->actor_id ? ':' . $log->actor_id : ''));
                $aksi = $log->aksi ?: ($log->event_type ?? 'SYSTEM_EVENT');
                $target = $log->target ?: '';
                $ip = $log->ip_address ?: '127.0.0.1';

                $payloadJson = '';
                if (!empty($log->payload)) {
                    $decoded = is_string($log->payload) ? json_decode($log->payload, true) : (array) $log->payload;
                    if (is_array($decoded)) {
                        ksort($decoded);
                        $payloadJson = json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                    } else {
                        $payloadJson = (string) $log->payload;
                    }
                }

                $timestamp = date('Y-m-d H:i:s', strtotime($log->created_at ?? now()));

                $raw = implode('|', [
                    trim((string) $aktor),
                    trim((string) $aksi),
                    trim((string) $target),
                    trim((string) $ip),
                    $timestamp,
                    $payloadJson,
                    trim((string) $prevHash),
                ]);

                $hashThis = hash('sha256', $raw);

                DB::table('audit_logs')
                    ->where('id', $log->id)
                    ->update([
                        'aktor' => $aktor,
                        'aksi' => $aksi,
                        'target' => $target,
                        'hash_sebelumnya' => $prevHash,
                        'hash_ini' => $hashThis,
                    ]);

                $prevHash = $hashThis;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $colsToDrop = [];
            foreach (['hash_ini', 'hash_sebelumnya', 'target', 'aksi', 'aktor'] as $col) {
                if (Schema::hasColumn('audit_logs', $col)) {
                    $colsToDrop[] = $col;
                }
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
