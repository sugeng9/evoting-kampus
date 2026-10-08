<?php

namespace App\Http\Middleware;

use App\Models\ElectionSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVotingIsOpen
{
    /**
     * Mencegah akses ke bilik suara dan submission suara jika sesi pemilihan sedang tutup
     */
    public function handle(Request $request, Closure $next): Response
    {
        $setting = ElectionSetting::current();

        if (!$setting->isOpen()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sesi pemilihan saat ini sedang DITUTUP atau berada di luar batas waktu resmi.',
                ], 403);
            }

            return redirect()->route('voter.dashboard')
                ->with('error', 'Sesi pemilihan sedang DITUTUP oleh Panitia KPU Kampus atau waktu pemungutan suara telah berakhir.');
        }

        return $next($request);
    }
}
