<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kode_pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->string('nim', 20)->index();
            $table->string('kode_hash', 64); // SHA-256 hash dari kode
            $table->unsignedBigInteger('dibuat_oleh'); // user_id helpdesk
            $table->unsignedBigInteger('disetujui_oleh')->nullable(); // user_id petugas B
            $table->timestamp('expired_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->foreign('dibuat_oleh')->references('id')->on('users');
            $table->foreign('disetujui_oleh')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kode_pendaftaran');
    }
};
