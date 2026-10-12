<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Menerapkan HTTP Security Headers standar industri militer/perbankan
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Deteksi HTTPS dari direct connection, Cloudflare Tunnel, Ngrok, atau Reverse Proxy
        $isHttps = $request->isSecure()
            || strtolower($request->header('x-forwarded-proto', '')) === 'https'
            || str_contains($request->header('cf-visitor', ''), 'https')
            || str_contains($request->header('host', ''), 'trycloudflare.com')
            || str_contains($request->header('host', ''), 'ngrok');

        if ($isHttps || app()->environment('production')) {
            URL::forceScheme('https');
            $request->server->set('HTTPS', 'on');
        }

        $response = $next($request);

        // Anti-Clickjacking: Mencegah halaman disematkan di dalam iframe berbahaya
        $response->headers->set('X-Frame-Options', 'DENY');

        // Anti-MIME Sniffing: Mencegah browser mengeksekusi file selain tipe MIME aslinya
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // XSS Filter bawaan browser
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy: Hanya kirim origin pada HTTPS
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy: Nonaktifkan sensor perangkat keras yang tidak dibutuhkan sistem e-voting
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');

        // Cross-Domain Policies
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // Strict-Transport-Security (HSTS): Paksa browser selalu menggunakan HTTPS selama 1 tahun
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // Content Security Policy (CSP): Blokir script asing, izinkan CDN resmi, Tawk.to & Cloudflare Turnstile
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdn.tailwindcss.com https://embed.tawk.to https://*.tawk.to https://challenges.cloudflare.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdn.tailwindcss.com https://*.tawk.to; " .
               "font-src 'self' https://fonts.gstatic.com https://*.tawk.to data:; " .
               "img-src 'self' data: https: blob:; " .
               "connect-src 'self' https://*.tawk.to wss://*.tawk.to https://cdn.jsdelivr.net https://challenges.cloudflare.com; " .
               "frame-src 'self' https://*.tawk.to https://challenges.cloudflare.com; " .
               "frame-ancestors 'none';";

        if ($isHttps || app()->environment('production')) {
            $csp .= " upgrade-insecure-requests;";
        }

        $response->headers->set('Content-Security-Policy', $csp);

        // Cache-Control untuk halaman sensitif (Mencegah back button browser menampilkan data sensitif setelah logout)
        if ($request->is('voter*') || $request->is('admin*') || $request->is('daftar*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
