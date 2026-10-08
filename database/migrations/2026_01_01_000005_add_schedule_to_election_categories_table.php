<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan jadwal waktu independen untuk setiap kategori pemilihan (Presma vs HMJ)
     */
    public function up(): void
    {
        Schema::table('election_categories', function (Blueprint $table) {
            $table->timestamp('start_at')->nullable()->after('study_program_filter');
            $table->timestamp('end_at')->nullable()->after('start_at');
            $table->enum('status_override', ['AUTO', 'FORCE_OPEN', 'FORCE_CLOSED'])->default('AUTO')->after('end_at');
        });
    }

    public function down(): void
    {
        Schema::table('election_categories', function (Blueprint $table) {
            $table->dropColumn(['start_at', 'end_at', 'status_override']);
        });
    }
};
