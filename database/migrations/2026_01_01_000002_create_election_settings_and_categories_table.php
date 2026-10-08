<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengatur Kategori Pemilihan (Kahim/Presma), Kandidat, dan Konfigurasi Sesi Pemira
     */
    public function up(): void
    {
        // 1. Kategori Pemilihan (Presma, Kahim IF, Kahim Mesin, dll.)
        Schema::create('election_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique(); // Misal: 'PRESMA_2026', 'KAHIM_HMTI_2026'
            $table->string('name', 150); // 'Pemilihan Presiden Mahasiswa'
            $table->text('description')->nullable();
            $table->string('faculty_filter', 100)->nullable(); // Null = terbuka untuk seluruh fakultas; Isi = hanya fakultas tertentu
            $table->string('study_program_filter', 100)->nullable(); // Null = semua prodi
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(1);
            $table->timestamps();
        });

        // 2. Data Kandidat (Paslon / Calon Ketua)
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_category_id')->constrained('election_categories')->onDelete('cascade');
            $table->unsignedInteger('candidate_number'); // Nomor Urut (1, 2, 3, dst.)
            $table->string('leader_name'); // Nama Calon Ketua
            $table->string('vice_leader_name')->nullable(); // Nama Calon Wakil (jika ada)
            $table->string('leader_nim', 20)->nullable();
            $table->string('vice_leader_nim', 20)->nullable();
            $table->string('photo_path')->nullable(); // Path foto resmi paslon
            $table->text('vision'); // Visi
            $table->text('mission'); // Misi (Markdown / Bullet points)
            $table->text('programs_work')->nullable(); // Program kerja unggulan
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Nomor urut harus unik per kategori pemilihan
            $table->unique(['election_category_id', 'candidate_number'], 'unique_candidate_number_per_category');
        });

        // 3. Konfigurasi Global Sesi E-Voting (Audit & Switch)
        Schema::create('election_settings', function (Blueprint $table) {
            $table->id();
            $table->string('election_name')->default('PEMIRA UNIVERSITAS 2026');
            $table->enum('status', ['DRAFT', 'OPEN', 'PAUSED', 'CLOSED'])->default('DRAFT');
            $table->timestamp('voting_starts_at')->nullable();
            $table->timestamp('voting_ends_at')->nullable();
            $table->boolean('is_quick_count_public')->default(false); // Quick count hanya dibuka saat panitia mengizinkan
            $table->boolean('require_two_step_confirmation')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_settings');
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('election_categories');
    }
};
