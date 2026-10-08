<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Menerapkan HTTP Security Headers standar industri militer/perbankan
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Anti-Clickjacking: Mencegah halaman disematkan di dalam iframe berbahaya
        $response->headers->set('X-Frame-Options', 'DENY');

        // Anti-MIME Sniffing: Mencegah browser mengeksekusi file selain tipe MIME aslinya
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // XSS Filter bawaan browser
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy: Hanya kirim origin pada HTTPS
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy (CSP): Blokir eksekusi script asing berbahaya
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.tailwindcss.com; " .
               "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdn.tailwindcss.com; " .
               "font-src 'self' https://fonts.gstatic.com; " .
               "img-src 'self' data: https:; " .
               "frame-ancestors 'none';";
        $response->headers->set('Content-Security-Policy', $csp);

        // HSTS (HTTP Strict Transport Security): Wajibkan HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Cache-Control untuk halaman pemilih (Mencegah back button browser menampilkan data sensitif setelah logout)
        if ($request->is('voter*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
