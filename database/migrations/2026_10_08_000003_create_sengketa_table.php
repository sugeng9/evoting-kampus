<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sengketa', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 20)->index();
            $table->text('alasan');
            $table->string('pelapor_kontak', 200); // email/hp pelapor
            $table->enum('status', ['baru', 'diproses', 'selesai'])->default('baru');
            $table->unsignedBigInteger('ditangani_oleh')->nullable(); // user_id petugas A
            $table->unsignedBigInteger('disetujui_oleh')->nullable(); // user_id petugas B
            $table->text('catatan_petugas')->nullable();
            $table->boolean('ktm_diverifikasi')->default(false);
            $table->timestamp('diselesaikan_at')->nullable();
            $table->timestamps();
            $table->foreign('ditangani_oleh')->references('id')->on('users')->nullOnDelete();
            $table->foreign('disetujui_oleh')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sengketa');
    }
};
