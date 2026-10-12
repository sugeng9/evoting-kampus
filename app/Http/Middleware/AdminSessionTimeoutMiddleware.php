<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminSessionTimeoutMiddleware
{
    /**
     * Durasi timeout sesi tidak aktif (15 menit = 900 detik)
     */
    private const INACTIVITY_TIMEOUT = 900;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('admin.logout', 'admin.login', 'admin.login.submit')) {
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            $lastActivity = session('admin_last_activity');
            $currentTime = time();

            if ($lastActivity && ($currentTime - $lastActivity > self::INACTIVITY_TIMEOUT)) {
                $user = Auth::guard('web')->user();

                AuditLog::record(
                    aksi: 'ADMIN_SESSION_TIMEOUT',
                    target: "admin:{$user->id}",
                    payload: [
                        'username' => $user->username,
                        'name' => $user->name,
                        'inactive_seconds' => $currentTime - $lastActivity,
                        'ip' => $request->ip(),
                    ],
                    aktor: "admin:{$user->id} ({$user->name})"
                );

                Auth::guard('web')->logout();
                session()->forget(['admin_2fa_passed', 'admin_last_activity', 'admin_2fa_user_id']);
                session()->invalidate();
                session()->regenerateToken();

                return redirect()->route('admin.login')
                    ->with('warning', 'Sesi panitia Anda telah berakhir karena tidak ada aktivitas selama 15 menit demi keamanan sistem. Silakan login kembali.');
            }

            // Perbarui waktu aktivitas terakhir
            session(['admin_last_activity' => $currentTime]);
        }

        return $next($request);
    }
}
