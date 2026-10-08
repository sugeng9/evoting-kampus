<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table users: Khusus Panitia/Admin Pemira Kampus
     * Table voters: Khusus Mahasiswa Pemilih berbasis NIM
     */
    public function up(): void
    {
        // 1. Tabel Administrator / Panitia KPU Kampus
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['super_admin', 'committee', 'auditor', 'helpdesk'])->default('committee');
            $table->boolean('is_active')->default(true);
            $table->string('two_factor_secret')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Tabel Pemilih Mahasiswa (Daftar Pemilih Tetap - DPT)
        Schema::create('voters', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 20)->unique()->index();
            $table->string('name');
            $table->string('faculty');
            $table->string('study_program');
            $table->string('angkatan', 10)->nullable();
            $table->string('academic_status', 30)->default('aktif');
            $table->string('dpt_status', 20)->default('DPT');
            $table->string('password'); // Hashed Password Akun Mahasiswa
            $table->string('token_plain', 20)->nullable();
            $table->timestamp('token_generated_at')->nullable();
            $table->string('plain_token_salt')->nullable(); // Optional salt for token hash validation
            $table->boolean('is_active')->default(true); // Flag apakah NIM berhak memilih (belum diskors/DO)
            $table->string('last_login_ip', 45)->nullable();
            $table->text('last_login_user_agent')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            // Index performa & integritas
            $table->index(['nim', 'is_active']);
        });

        // 3. Tabel Session & Cache standar Laravel
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('voters');
        Schema::dropIfExists('users');
    }
};
