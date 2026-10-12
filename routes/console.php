<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Jadwal Backup Otomatis Harian Terenkripsi (Retensi 7 Hari & Offsite)
|--------------------------------------------------------------------------
| Dijalankan setiap hari pukul 02:00 WIB untuk mencadangkan database evoting,
| mengenkripsi dengan AES-256, menyalin ke offsite, dan menghapus cadangan > 7 hari.
*/
Schedule::command('backup:run-encrypted')
    ->dailyAt('02:00')
    ->runInBackground();

/*
|--------------------------------------------------------------------------
| Jadwal Pembersihan Berkas KTM Sengketa Kedaluwarsa (UU PDP Retention)
|--------------------------------------------------------------------------
*/
Schedule::command('sengketa:purge-ktm --force --days=30')
    ->weekly()
    ->runInBackground();

