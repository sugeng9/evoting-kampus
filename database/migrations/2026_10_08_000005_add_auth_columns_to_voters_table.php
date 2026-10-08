<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voters', function (Blueprint $table) {
            if (!Schema::hasColumn('voters', 'academic_status')) {
                $table->string('academic_status', 30)->default('aktif');
            }
            if (!Schema::hasColumn('voters', 'dpt_status')) {
                $table->string('dpt_status', 20)->default('DPT');
            }
            // Status pendaftaran mandiri
            if (!Schema::hasColumn('voters', 'pendaftaran_status')) {
                $table->enum('pendaftaran_status', ['pending', 'aktif', 'dicabut'])
                      ->default('aktif')
                      ->comment('Status akun pendaftaran mandiri: pending=menunggu verifikasi, aktif=boleh login, dicabut=dinonaktifkan helpdesk');
            }
            if (!Schema::hasColumn('voters', 'email')) {
                $table->text('email')->nullable();
            }
            if (!Schema::hasColumn('voters', 'no_hp')) {
                $table->text('no_hp')->nullable();
            }
            if (!Schema::hasColumn('voters', 'registered_at')) {
                $table->timestamp('registered_at')->nullable();
            }
            if (!Schema::hasColumn('voters', 'activated_at')) {
                $table->timestamp('activated_at')->nullable();
            }
            if (!Schema::hasColumn('voters', 'login_attempts')) {
                $table->integer('login_attempts')->default(0);
            }
            if (!Schema::hasColumn('voters', 'locked_until')) {
                $table->timestamp('locked_until')->nullable();
            }
            if (!Schema::hasColumn('voters', 'current_session_id')) {
                $table->string('current_session_id', 100)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('voters', function (Blueprint $table) {
            $table->dropColumn([
                'pendaftaran_status', 'email', 'no_hp', 'registered_at',
                'activated_at', 'login_attempts', 'locked_until', 'current_session_id'
            ]);
        });
    }
};
