<?php

use App\Services\SecurityLogScrubber;

return [

    /*
    |--------------------------------------------------------------------------
    | Sentry DSN (Data Source Name)
    |--------------------------------------------------------------------------
    | DSN untuk pelaporan exception dan performa sistem ke Sentry / GlitchTip.
    | Jika kosong, pelaporan otomatis dinonaktifkan (default aman).
    */
    'dsn' => env('SENTRY_LARAVEL_DSN', env('SENTRY_DSN')),

    // Matikan pengiriman PII (Personally Identifiable Information) otomatis
    'send_default_pii' => false,

    // Sample rate untuk monitoring error & tracing performa
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.2),

    // Lingkungan aplikasi
    'environment' => env('APP_ENV', 'production'),

    // Versi rilis aplikasi untuk pelacakan regresi bug
    'release' => env('APP_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Pembersihan Kredensial & PII Sebelum Terkirim (Before Send Callback)
    |--------------------------------------------------------------------------
    | Menjamin bahwa tidak ada kata sandi, PIN, OTP, recovery code, atau data
    | identitas rahasia mahasiswa yang terkirim ke platform monitoring pihak ketiga.
    */
    'before_send' => function (\Sentry\Event $event, ?\Sentry\EventHint $hint): ?\Sentry\Event {
        // Sensor breadcrumbs
        $breadcrumbs = $event->getBreadcrumbs();
        foreach ($breadcrumbs as $crumb) {
            $metadata = $crumb->getMetadata();
            if (!empty($metadata)) {
                $crumb->setMetadata(SecurityLogScrubber::scrubArray($metadata));
            }
        }

        // Sensor data request
        $request = $event->getRequest();
        if (!empty($request['data']) && is_array($request['data'])) {
            $request['data'] = SecurityLogScrubber::scrubArray($request['data']);
            $event->setRequest($request);
        }

        return $event;
    },

    // Abaikan exception yang bersifat operasional normal
    'ignore_exceptions' => [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Validation\ValidationException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
    ],

];
