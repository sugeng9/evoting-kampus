<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Core Security: Pemisahan Mutlak Identitas Pemilih vs Kotak Suara Anonim (Secret Ballot Architecture)
     */
    public function up(): void
    {
        // 1. Buku Presensi Pemilih (Partisipasi Pemilih)
        // HANYA mencatat BAHWA mahasiswa telah menggunakan hak suaranya pada kategori tertentu.
        // TIDAK MENYIMPAN siapa kandidat yang dipilih!
        Schema::create('voter_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voter_id')->constrained('voters')->onDelete('cascade');
            $table->foreignId('election_category_id')->constrained('election_categories')->onDelete('cascade');
            $table->string('receipt_hash', 64)->unique(); // Hash kuitansi kriptografis bukti pemilihan
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->timestamp('voted_at');
            $table->timestamps();

            // INTEGRITAS KRUSIAL: Mencegah race-condition di level storage engine InnoDB
            // 1 NIM hanya dapat tercatat TEPAT SATU KALI per kategori pemilihan
            $table->unique(['voter_id', 'election_category_id'], 'unique_voter_category_vote');
        });

        // 2. Kotak Suara Digital Anonim (Ballot Box)
        // Berisi suara sah yang dimasukkan ke bilik suara.
        // SAMA SEKALI TIDAK ADA KOLOM voter_id ATAU nim!
        // Menggunakan UUIDv4 sebagai Primary Key agar urutan insert acak dan tidak bisa dicocokkan dengan auto-increment voter_id.
        Schema::create('ballot_boxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('election_category_id')->constrained('election_categories')->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained('candidates')->onDelete('cascade');
            
            // Kuitansi Verifikasi Suara (Zero-Knowledge Audit Trail)
            // Mahasiswa memegang Token Rahasia yang di-hash ke kolom ini.
            // Mahasiswa dapat memverifikasi suaranya sudah terhitung tanpa membocorkan pilihannya ke orang lain.
            $table->string('ballot_hash', 64)->unique()->index(); 
            $table->string('verification_code', 32)->unique()->index(); // Contoh: 'PEMIRA-A98B-72XC'

            $table->timestamp('created_at')->useCurrent();

            // Index performa query Quick Count & Rekapitulasi Suara
            $table->index(['election_category_id', 'candidate_id']);
        });

        // 3. Audit Trail & Log Keamanan Sistem (Immutable Logging)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type'); // 'voter' / 'admin' / 'system'
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('event_type', 100); // 'VOTE_SUBMITTED', 'VOTING_OPENED', 'LOGIN_FAILED_RATE_LIMIT'
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable(); // Metadata non-sensitif (tanpa pilihan suara!)
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('ballot_boxes');
        Schema::dropIfExists('voter_participations');
    }
};
