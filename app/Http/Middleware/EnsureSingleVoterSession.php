<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan hanya ada satu sesi aktif per pemilih.
 * Jika session ID berubah (login dari perangkat lain), sesi lama dihancurkan.
 */
class EnsureSingleVoterSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $voter = auth()->guard('voter')->user();

        if ($voter && $voter->current_session_id) {
            $currentSessionId = session()->getId();

            if ($voter->current_session_id !== $currentSessionId) {
                // Sesi tidak cocok — kemungkinan login dari perangkat lain
                auth()->guard('voter')->logout();
                session()->invalidate();
                session()->regenerateToken();

                return redirect()->route('voter.login')
                    ->with('error', 'Sesi Anda telah berakhir karena ada login dari perangkat lain. Silakan login kembali.');
            }
        }

        return $next($request);
    }
}
