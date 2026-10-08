<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_phases', function (Blueprint $table) {
            $table->id();
            $table->enum('fase', ['pendaftaran', 'sengketa', 'aktif', 'voting'])->default('pendaftaran');
            $table->boolean('pendaftaran_terbuka')->default(true)
                  ->comment('Apakah pendaftaran juga dibuka di fase sengketa');
            $table->string('catatan', 500)->nullable();
            $table->unsignedBigInteger('diubah_oleh')->nullable();
            $table->timestamps();
            $table->foreign('diubah_oleh')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_phases');
    }
};
