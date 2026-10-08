<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        try {
            DB::statement("ALTER TABLE voters MODIFY COLUMN email TEXT NULL");
            DB::statement("ALTER TABLE voters MODIFY COLUMN no_hp TEXT NULL");
        } catch (\Throwable $e) {
            Schema::table('voters', function (Blueprint $table) {
                $table->text('email')->nullable()->change();
                $table->text('no_hp')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('voters', function (Blueprint $table) {
            $table->string('email', 200)->nullable()->change();
            $table->string('no_hp', 50)->nullable()->change();
        });
    }
};
