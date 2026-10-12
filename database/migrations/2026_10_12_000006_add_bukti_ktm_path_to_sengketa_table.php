<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * FASE 13: Hardening Server & Berkas Identitas Privat (KTM & Bukti Sengketa)
     */
    public function up(): void
    {
        Schema::table('sengketa', function (Blueprint $table) {
            if (!Schema::hasColumn('sengketa', 'bukti_ktm_path')) {
                $table->string('bukti_ktm_path', 255)->nullable()->after('ktm_diverifikasi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sengketa', function (Blueprint $table) {
            if (Schema::hasColumn('sengketa', 'bukti_ktm_path')) {
                $table->dropColumn('bukti_ktm_path');
            }
        });
    }
};
