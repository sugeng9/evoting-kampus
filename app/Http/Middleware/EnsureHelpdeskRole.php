<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memastikan pengguna yang login adalah helpdesk atau admin.
 * Menggunakan guard 'web' (tabel users).
 */
class EnsureHelpdeskRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->guard('web')->user();

        if (! $user || ! $user->isHelpdesk()) {
            return redirect()->route('admin.login')
                ->with('error', 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}
