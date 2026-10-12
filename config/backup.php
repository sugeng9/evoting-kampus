<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Lokasi Penyimpanan Backup
    |--------------------------------------------------------------------------
    | 'local_path' adalah direktori lokal server.
    | 'offsite_path' adalah direktori terpisah / volume mount offsite untuk
    | menjamin backup selamat jika server utama hangus / diserang.
    */
    'local_path' => storage_path('app/backups'),
    'offsite_path' => env('BACKUP_OFFSITE_PATH', storage_path('app/offsite_backups')),

    /*
    |--------------------------------------------------------------------------
    | Retensi Backup (Hari)
    |--------------------------------------------------------------------------
    | Berkas backup yang lebih tua dari retensi ini akan dihapus secara otomatis.
    */
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Enkripsi Backup (AES-256-CBC)
    |--------------------------------------------------------------------------
    | Kunci enkripsi wajib dirahasiakan. Jika BACKUP_ENCRYPTION_KEY tidak diisi,
    | sistem akan menggunakan APP_KEY Laravel sebagai default.
    */
    'encryption_key' => env('BACKUP_ENCRYPTION_KEY', env('APP_KEY')),
    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Kompresi Gzip
    |--------------------------------------------------------------------------
    */
    'compression_level' => 9,
];
