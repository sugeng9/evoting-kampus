<?php

use App\Models\ElectionSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah default kolom menjadi true
        Schema::table('election_settings', function (Blueprint $table) {
            $table->boolean('is_quick_count_public')->default(true)->change();
        });

        // Update data sesi saat ini agar hasil langsung terbuka untuk publik
        ElectionSetting::query()->update(['is_quick_count_public' => true]);
    }

    public function down(): void
    {
        Schema::table('election_settings', function (Blueprint $table) {
            $table->boolean('is_quick_count_public')->default(false)->change();
        });
    }
};
