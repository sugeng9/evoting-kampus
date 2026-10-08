<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name & Environment
    |--------------------------------------------------------------------------
    */
    'name' => env('APP_NAME', 'E-Voting Kampus'),
    'env' => env('APP_ENV', 'local'),
    'debug' => (bool) env('APP_DEBUG', true),
    'url' => env('APP_URL', 'http://localhost:8000'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone (Waktu Indonesia Barat - WIB / Asia/Jakarta)
    |--------------------------------------------------------------------------
    | Seluruh pencatatan waktu pemungutan suara, jadwal bilik suara, kuitansi,
    | dan audit log wajib menggunakan zona Waktu Indonesia Barat (WIB).
    */
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    */
    'locale' => env('APP_LOCALE', 'id'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key & Cipher
    |--------------------------------------------------------------------------
    */
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    'maintenance' => [
        'driver' => 'file',
    ],

];
