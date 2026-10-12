<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * FASE 12: Indeks Komposit MySQL Performa Tinggi (Target 6.000 Pemilih Serentak)
     */
    public function up(): void
    {
        // 1. Indeks Komposit Kotak Suara Digital (Covering Index untuk Live Quick Count & Rekonsiliasi)
        Schema::table('ballot_boxes', function (Blueprint $table) {
            if (!$this->hasIndex('ballot_boxes', 'idx_ballot_cat_candidate')) {
                $table->index(['election_category_id', 'candidate_id'], 'idx_ballot_cat_candidate');
            }
        });

        // 2. Indeks Komposit Tabel Voters (Blind Index Email & Filter Status DPT Aktif)
        Schema::table('voters', function (Blueprint $table) {
            if (!$this->hasIndex('voters', 'idx_voters_email_hash')) {
                $table->index('email_hash', 'idx_voters_email_hash');
            }
            if (!$this->hasIndex('voters', 'idx_voters_status_active')) {
                $table->index(['pendaftaran_status', 'is_active'], 'idx_voters_status_active');
            }
            if (!$this->hasIndex('voters', 'idx_voters_prodi_status')) {
                $table->index(['study_program', 'pendaftaran_status'], 'idx_voters_prodi_status');
            }
        });

        // 3. Indeks Komposit Kandidat Paslon (Pengurutan Cepat Bilik Suara)
        Schema::table('candidates', function (Blueprint $table) {
            if (!$this->hasIndex('candidates', 'idx_candidates_cat_active_num')) {
                $table->index(['election_category_id', 'is_active', 'candidate_number'], 'idx_candidates_cat_active_num');
            }
        });

        // 4. Indeks Komposit Partisipasi Pemilih
        Schema::table('voter_participations', function (Blueprint $table) {
            if (!$this->hasIndex('voter_participations', 'idx_vp_cat')) {
                $table->index('election_category_id', 'idx_vp_cat');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ballot_boxes', function (Blueprint $table) {
            if ($this->hasIndex('ballot_boxes', 'idx_ballot_cat_candidate')) {
                $table->dropIndex('idx_ballot_cat_candidate');
            }
        });

        Schema::table('voters', function (Blueprint $table) {
            if ($this->hasIndex('voters', 'idx_voters_email_hash')) {
                $table->dropIndex('idx_voters_email_hash');
            }
            if ($this->hasIndex('voters', 'idx_voters_status_active')) {
                $table->dropIndex('idx_voters_status_active');
            }
            if ($this->hasIndex('voters', 'idx_voters_prodi_status')) {
                $table->dropIndex('idx_voters_prodi_status');
            }
        });

        Schema::table('candidates', function (Blueprint $table) {
            if ($this->hasIndex('candidates', 'idx_candidates_cat_active_num')) {
                $table->dropIndex('idx_candidates_cat_active_num');
            }
        });

        Schema::table('voter_participations', function (Blueprint $table) {
            if ($this->hasIndex('voter_participations', 'idx_vp_cat')) {
                $table->dropIndex('idx_vp_cat');
            }
        });
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return !empty($indexes);
        } catch (\Throwable $e) {
            return false;
        }
    }
};
