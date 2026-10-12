<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    */
    'driver' => env('SESSION_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime & Auto-Timeout (Security Hardened)
    |--------------------------------------------------------------------------
    | Masa aktif sesi disetel 15 menit untuk meminimalisir risiko session hijacking
    | pada workstation bilik suara umum kampus.
    */
    'lifetime' => (int) env('SESSION_LIFETIME', 15),

    'expire_on_close' => (bool) env('SESSION_EXPIRE_ON_CLOSE', true),

    /*
    |--------------------------------------------------------------------------
    | Session Encryption
    |--------------------------------------------------------------------------
    */
    'encrypt' => (bool) env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Session File Location & Connection
    |--------------------------------------------------------------------------
    */
    'files' => storage_path('framework/sessions'),
    'connection' => env('SESSION_CONNECTION'),
    'table' => env('SESSION_TABLE', 'sessions'),
    'store' => env('SESSION_STORE'),
    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    */
    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'evoting_kampus'), '_').'_session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path & Domain
    |--------------------------------------------------------------------------
    */
    'path' => env('SESSION_PATH', '/'),
    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies & HTTP-Only Flag (Security Hardened)
    |--------------------------------------------------------------------------
    | Cookie hanya ditransmisikan via HTTPS (pada production/secure environment),
    | dan httpOnly diaktifkan agar tidak dapat dibaca via JavaScript / XSS.
    */
    'secure' => env('SESSION_SECURE_COOKIE', env('APP_ENV') === 'production'),

    'http_only' => (bool) env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies (Anti-CSRF Protection)
    |--------------------------------------------------------------------------
    */
    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    'partitioned' => (bool) env('SESSION_PARTITIONED_COOKIE', false),

];
