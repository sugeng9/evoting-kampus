<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param string ...$roles Daftar peran yang diizinkan (super_admin, panitia, helpdesk, auditor)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::guard('web')->user();

        // 1. Super Admin selalu memiliki hak akses penuh
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // 2. Proteksi Khusus Peran Auditor: Strictly Read-Only
        if ($user->role === 'auditor') {
            // Tolak semua request yang berpotensi mengubah data (POST, PUT, PATCH, DELETE)
            // Kecuali rute logout
            if (!$request->isMethodSafe() && !$request->routeIs('admin.logout')) {
                AuditLog::record(
                    aksi: 'AUDITOR_WRITE_BLOCKED',
                    target: "route:{$request->path()}",
                    payload: [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'method' => $request->method(),
                    ],
                    aktor: "admin:{$user->id} ({$user->name})"
                );

                abort(403, 'Akses Ditolak: Akun dengan peran Auditor hanya memiliki hak akses Pantau / Baca (Read-Only). Operasi penulisan atau perubahan data dilarang.');
            }
        }

        // 3. Verifikasi apakah peran user ada di dalam daftar roles yang diizinkan
        if (!empty($roles) && !in_array($user->role, $roles)) {
            AuditLog::record(
                aksi: 'ROLE_UNAUTHORIZED_ACCESS',
                target: "route:{$request->path()}",
                payload: [
                    'user_id' => $user->id,
                    'user_role' => $user->role,
                    'required_roles' => $roles,
                ],
                aktor: "admin:{$user->id} ({$user->name})"
            );

            abort(403, "Akses Ditolak: Peran akun Anda ({$user->role_label}) tidak memiliki wewenang untuk mengakses halaman atau fungsi ini.");
        }

        return $next($request);
    }
}
