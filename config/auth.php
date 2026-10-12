<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Guard Khusus Mahasiswa Pemilih (Isolated Session)
        'voter' => [
            'driver' => 'session',
            'provider' => 'voters',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // Provider Mahasiswa Pemilih
        'voters' => [
            'driver' => 'eloquent',
            'model' => App\Models\Voter::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

    /*
    |--------------------------------------------------------------------------
    | Sembunyikan URL Login Default (/login -> 404)
    |--------------------------------------------------------------------------
    | Mengambil path URL login pemilih dan admin dari file .env demi mitigasi
    | automated crawler / bot scanning. Akses ke /login default akan di-404.
    */
    'login_path' => env('LOGIN_PATH', 'auth-pemira'),
    'admin_login_path' => env('ADMIN_LOGIN_PATH', 'admin/login'),
    'admin_ip_allowlist' => env('ADMIN_IP_ALLOWLIST', ''),

];
