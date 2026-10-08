<?php

namespace App\Http\Middleware;

use App\Models\SystemPhase;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan fase sistem mengizinkan pelaporan sengketa.
 * Sengketa hanya diizinkan di fase 'pendaftaran' dan 'sengketa'.
 */
class EnsureFaseSengketa
{
    public function handle(Request $request, Closure $next): Response
    {
        $phase = SystemPhase::current();

        if (! $phase->allowsSengketa()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pelaporan sengketa tidak tersedia di fase: ' . strtoupper($phase->fase),
                ], 403);
            }

            abort(403, 'Tindakan ini tidak tersedia di fase sistem saat ini (' . strtoupper($phase->fase) . ').');
        }

        return $next($request);
    }
}
