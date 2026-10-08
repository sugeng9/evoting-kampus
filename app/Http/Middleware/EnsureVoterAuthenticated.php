<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureVoterAuthenticated
{
    /**
     * Pastikan mahasiswa telah login melalui guard voter dan akunnya masih aktif
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('voter')->check()) {
            return redirect()->route('voter.login')
                ->with('error', 'Silakan login terlebih dahulu menggunakan NIM dan PIN Anda.');
        }

        $voter = Auth::guard('voter')->user();
        if (!$voter->is_active) {
            Auth::guard('voter')->logout();
            return redirect()->route('voter.login')
                ->with('error', 'Hak suara NIM Anda telah dinonaktifkan oleh panitia.');
        }

        return $next($request);
    }
}
