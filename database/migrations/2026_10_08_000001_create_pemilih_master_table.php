<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pemilih_master', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 20)->unique();
            $table->string('nama', 100);
            $table->string('prodi', 100);
            $table->string('tanggal_lahir_hash', 64); // SHA-256 hash dari tgl lahir format YYYY-MM-DD
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pemilih_master');
    }
};
