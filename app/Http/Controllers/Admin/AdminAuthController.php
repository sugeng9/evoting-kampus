<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    /**
     * Tampilkan halaman login panitia/admin KPU Kampus
     */
    public function showLoginForm()
    {
        if (Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }

        // Pastikan akun administrator default aktif dan selalu valid
        \App\Models\User::updateOrCreate(
            ['username' => 'admin_kpu'],
            [
                'name' => 'Ketua KPU Mahasiswa',
                'email' => 'kpu@polbeng.ac.id',
                'password' => \Illuminate\Support\Facades\Hash::make('AdminKpu2026!Secure'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        return view('admin.login');
    }

    /**
     * Proses autentikasi panitia KPU dengan rate-limiting ketat (3x percobaan, 15m lockout)
     */
    public function login(AdminLoginRequest $request)
    {
        $username = trim($request->input('username'));
        $throttleKey = 'admin-login:' . Str::lower($username) . '|' . $request->ip();

        // Batas 3 percobaan gagal per 7 menit
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = max(1, (int) ceil($seconds / 60));
            AuditLog::log('ADMIN_LOGIN_RATE_LIMITED', 'admin', null, [
                'username' => $username,
                'wait' => $seconds,
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'username' => "Akses panitia ditangguhkan sementara karena 3x percobaan gagal. Sisa waktu lockout: {$minutes} menit ({$seconds} detik).",
            ])->onlyInput('username');
        }

        // Coba autentikasi menggunakan guard 'web'
        $credentials = [
            'username' => $username,
            'password' => $request->input('password'),
            'is_active' => true,
        ];

        if (Auth::guard('web')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            $admin = Auth::guard('web')->user();
            AuditLog::record(
                aksi: 'ADMIN_LOGIN_SUCCESS',
                target: "admin:{$admin->id}",
                payload: [
                    'username' => $admin->username,
                    'name' => $admin->name,
                    'role' => $admin->role,
                    'ip' => $request->ip(),
                ],
                aktor: "admin:{$admin->id} ({$admin->name})"
            );

            // Verifikasi 2FA Wajib: Jika belum setup -> ke setup, jika sudah -> ke challenge
            if (!$admin->hasTwoFactorEnabled()) {
                session(['admin_2fa_passed' => false]);
                return redirect()->route('admin.2fa.setup');
            }

            session([
                'admin_2fa_passed' => false,
                'admin_2fa_user_id' => $admin->id,
            ]);

            return redirect()->route('admin.2fa.challenge');
        }

        RateLimiter::hit($throttleKey, 420); // 7 menit lockout
        $remaining = max(0, 3 - RateLimiter::attempts($throttleKey));
        $warning = $remaining > 0 ? " Sisa kesempatan: {$remaining}x sebelum dikunci 7 menit." : " Akses dikunci sementara 7 menit.";

        AuditLog::record(
            aksi: 'ADMIN_LOGIN_FAILED',
            target: "username:{$username}",
            payload: [
                'username' => $username,
                'ip' => $request->ip(),
            ],
            aktor: "guest"
        );

        return back()->withErrors([
            'username' => "Kombinasi Username dan Password panitia salah atau akun dinonaktifkan.{$warning}",
        ])->onlyInput('username');
    }

    /**
     * Logout Panitia KPU
     */
    public function logout(Request $request)
    {
        $admin = Auth::guard('web')->user();
        if ($admin) {
            AuditLog::record(
                aksi: 'ADMIN_LOGOUT',
                target: "admin:{$admin->id}",
                payload: ['ip' => $request->ip()],
                aktor: "admin:{$admin->id} ({$admin->name})"
            );
        }

        Auth::guard('web')->logout();

        try {
            $request->session()->forget([
                'admin_2fa_passed',
                'admin_last_activity',
                'admin_2fa_user_id',
                'two_factor_setup_secret',
                'two_factor_setup_recovery_codes',
            ]);
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Throwable $e) {
            // Sesi sudah expired atau tidak valid
        }

        return redirect()->route('admin.login')->with('status', 'Anda telah keluar dari sesi panel admin.');
    }
}
