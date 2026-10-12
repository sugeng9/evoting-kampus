<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TurnstileRule implements ValidationRule
{
    /**
     * Jalankan validasi token Cloudflare Turnstile di sisi server (Server-Side Verification).
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!config('services.turnstile.enabled', true)) {
            return;
        }

        if (empty($value) || !is_string($value)) {
            $fail('Mohon selesaikan verifikasi keamanan Anti-Bot (Cloudflare Turnstile).');
            return;
        }

        $secretKey = config('services.turnstile.secret_key');
        if (empty($secretKey)) {
            Log::warning('[TURNSTILE] TURNSTILE_SECRET_KEY belum diisi di .env.');
            return;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret'   => $secretKey,
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

            $json = $response->json();

            if (!$response->successful() || empty($json['success']) || $json['success'] !== true) {
                $errorCodes = $json['error-codes'] ?? [];
                Log::warning('[TURNSTILE VERIFICATION FAILED] IP: ' . request()->ip() . ' | Errors: ' . json_encode($errorCodes));
                $fail('Verifikasi keamanan Anti-Bot gagal atau token telah kedaluwarsa. Silakan ulangi.');
            }
        } catch (Throwable $e) {
            Log::error('[TURNSTILE CONNECTION ERROR] ' . $e->getMessage());

            // Toleransi kegagalan koneksi jika menggunakan dummy test key di lingkungan lokal tanpa internet
            if (app()->environment('local') && str_contains($secretKey, '00000000000000000000000000000000AA')) {
                return;
            }

            $fail('Server tidak dapat memvalidasi verifikasi Anti-Bot saat ini. Silakan coba kembali.');
        }
    }
}
