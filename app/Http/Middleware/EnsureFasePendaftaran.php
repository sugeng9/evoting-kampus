<?php

namespace App\Http\Middleware;

use App\Models\SystemPhase;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan fase sistem mengizinkan pendaftaran pemilih baru.
 * Fase yang mengizinkan: 'pendaftaran', atau 'sengketa' jika pendaftaran_terbuka = true.
 */
class EnsureFasePendaftaran
{
    public function handle(Request $request, Closure $next): Response
    {
        $phase = SystemPhase::current();

        if (! $phase->allowsPendaftaran()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Fase pendaftaran telah ditutup. Sistem saat ini dalam fase: ' . strtoupper($phase->fase),
                ], 403);
            }

            return redirect()->route('nim.status.form')
                ->with('error', 'Pendaftaran telah ditutup. Sistem saat ini dalam fase ' . strtoupper($phase->fase) . '.');
        }

        return $next($request);
    }
}
