<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cloudflare Turnstile (Anti-Bot CAPTCHA)
    |--------------------------------------------------------------------------
    | Kunci default menggunakan Official Dummy Keys dari Cloudflare untuk testing:
    | - Always Pass Sitekey  : 1x00000000000000000000AA
    | - Always Pass SecretKey: 1x00000000000000000000000000000000AA
    | Ganti dengan kunci asli dari Cloudflare Dashboard di berkas .env untuk produksi.
    */
    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY', '1x00000000000000000000AA'),
        'secret_key' => env('TURNSTILE_SECRET_KEY', '1x00000000000000000000000000000000AA'),
        'enabled' => env('TURNSTILE_ENABLED', true),
    ],

];
