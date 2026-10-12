<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * FASE 10: 2FA TOTP (Google Authenticator) & Peran Admin
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role', 50)->default('panitia');
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->longText('two_factor_secret')->nullable();
            }
            if (!Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->longText('two_factor_recovery_codes')->nullable();
            }
            if (!Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->nullable();
            }
        });

        // Pastikan tipe kolom di MySQL adalah LONGTEXT jika sebelumnya terbuat sebagai VARCHAR
        try {
            DB::statement('ALTER TABLE users MODIFY COLUMN two_factor_secret LONGTEXT NULL');
            DB::statement('ALTER TABLE users MODIFY COLUMN two_factor_recovery_codes LONGTEXT NULL');
        } catch (\Throwable $e) {
            // Abaikan jika bukan MySQL
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = ['two_factor_confirmed_at', 'two_factor_recovery_codes', 'two_factor_secret'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
