<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * FASE 8: Database-Level Guard & Unique Constraint Anti-Double Voting
     */
    public function up(): void
    {
        // 1. Tambah kolom sudah_memilih dan voted_at pada tabel voters
        Schema::table('voters', function (Blueprint $table) {
            if (!Schema::hasColumn('voters', 'sudah_memilih')) {
                $table->boolean('sudah_memilih')->default(false)->index()->after('is_active');
            }
            if (!Schema::hasColumn('voters', 'voted_at')) {
                $table->timestamp('voted_at')->nullable()->after('sudah_memilih');
            }
        });

        // Sinkronisasi status sudah_memilih awal berdasarkan data partisipasi yang sudah ada
        DB::table('voters')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('voter_participations')
                    ->whereColumn('voter_participations.voter_id', 'voters.id');
            })
            ->update(['sudah_memilih' => true]);

        // 2. Tambah Unique Constraint pada voter_participations (voter_id, election_category_id)
        // Sebelum membuat unique index, bersihkan potensi duplikasi riwayat lama jika ada
        Schema::table('voter_participations', function (Blueprint $table) {
            // Cek apakah unique index sudah ada
            $indexName = 'voter_cat_unique_vote';
            $indexes = DB::select("SHOW INDEX FROM `voter_participations` WHERE Key_name = ?", [$indexName]);
            if (empty($indexes)) {
                $table->unique(['voter_id', 'election_category_id'], $indexName);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voter_participations', function (Blueprint $table) {
            $table->dropUnique('voter_cat_unique_vote');
        });

        Schema::table('voters', function (Blueprint $table) {
            if (Schema::hasColumn('voters', 'sudah_memilih')) {
                $table->dropIndex(['sudah_memilih']);
                $table->dropColumn('sudah_memilih');
            }
            if (Schema::hasColumn('voters', 'voted_at')) {
                $table->dropColumn('voted_at');
            }
        });
    }
};
