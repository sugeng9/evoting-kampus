<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tawk.to Free Live Chat Configuration
    |--------------------------------------------------------------------------
    | Tawk.to adalah penyedia live chat 100% GRATIS selamanya:
    | - Unlimited agents (petugas KPU tidak terbatas)
    | - Unlimited chats & history
    | - Aplikasi mobile Android & iOS gratis untuk menjawab chat di mana saja
    | - Real-time visitor monitoring
    |
    | Dapatkan Property ID & Widget ID secara gratis di:
    | https://dashboard.tawk.to -> Administration -> Chat Widget
    */
    'tawkto' => [
        'property_id' => env('TAWKTO_PROPERTY_ID', null),
        'widget_id'   => env('TAWKTO_WIDGET_ID', 'default'),
        'direct_url'  => env('TAWKTO_DIRECT_URL', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Panitia Helpdesk
    |--------------------------------------------------------------------------
    | Alternatif bantuan instan via WhatsApp panitia Pemira Polbeng
    */
    'whatsapp' => [
        'number'         => env('HELPDESK_WA_NUMBER', '6285187953580'),
        'display_number' => env('HELPDESK_WA_DISPLAY', '+62 851-8795-3580'),
        'name'           => env('HELPDESK_WA_NAME', 'Panitia Helpdesk KPR Politeknik Negeri Bengkalis'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Informasi Layanan Helpdesk
    |--------------------------------------------------------------------------
    */
    'operating_hours' => env('HELPDESK_HOURS', '08.00 - 17.00 WIB'),
    'email'           => env('HELPDESK_EMAIL', env('MAIL_FROM_ADDRESS', 'kpr@polbeng.ac.id')),

];
