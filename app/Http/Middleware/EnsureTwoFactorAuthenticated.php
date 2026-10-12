<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    /**
     * Memastikan seluruh akun admin/panitia telah mengaktifkan dan memvalidasi 2FA TOTP
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Abaikan rute autentikasi awal & 2FA itu sendiri
        if ($request->routeIs('admin.login', 'admin.login.submit', 'admin.logout', 'admin.2fa.*')) {
            return $next($request);
        }

        if (!Auth::guard('web')->check()) {
            return $next($request);
        }

        $user = Auth::guard('web')->user();

        // 1. Jika akun belum mengaktifkan 2FA sama sekali -> Wajib Setup
        if (!$user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.2fa.setup')
                ->with('warning', 'Demi kepatuhan standar keamanan, Anda WAJIB mengaktifkan Google Authenticator (2FA) sebelum dapat mengakses sistem.');
        }

        // 2. Jika 2FA sudah aktif tapi sesi saat ini belum lolos verifikasi tantangan TOTP
        if (session('admin_2fa_passed') !== true) {
            return redirect()->route('admin.2fa.challenge')
                ->with('info', 'Masukkan kode verifikasi 6-digit dari Google Authenticator untuk melanjutkan sesi Anda.');
        }

        return $next($request);
    }
}
