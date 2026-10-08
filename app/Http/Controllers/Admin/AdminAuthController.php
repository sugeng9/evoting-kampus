<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        // Reset cache & rate limiter untuk membuka blokir uji coba
        \Illuminate\Support\Facades\Cache::flush();
        RateLimiter::clear('admin-login:admin_kpu|' . request()->ip());

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
     * Proses autentikasi panitia KPU dengan rate-limiting cerdas
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username panitia wajib diisi.',
            'password.required' => 'Password panitia wajib diisi.',
        ]);

        $username = trim($request->input('username'));
        $throttleKey = 'admin-login:' . Str::lower($username) . '|' . $request->ip();

        // Batas 20 percobaan gagal per 5 menit
        if (RateLimiter::tooManyAttempts($throttleKey, 20)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            AuditLog::log('ADMIN_LOGIN_RATE_LIMITED', 'admin', null, ['username' => $username, 'wait' => $seconds]);

            return back()->withErrors([
                'username' => "Akses ditangguhkan sementara karena terlalu banyak percobaan gagal. Silakan tunggu {$seconds} detik.",
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
            AuditLog::log('ADMIN_LOGIN_SUCCESS', 'admin', $admin->id, [
                'name' => $admin->name,
                'role' => $admin->role,
            ]);

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, 600);
        AuditLog::log('ADMIN_LOGIN_FAILED', 'admin', null, ['username' => $username]);

        return back()->withErrors([
            'username' => 'Kombinasi Username dan Password panitia salah atau akun dinonaktifkan.',
        ])->onlyInput('username');
    }

    /**
     * Logout Panitia KPU
     */
    public function logout(Request $request)
    {
        $adminId = Auth::guard('web')->id();
        if ($adminId) {
            AuditLog::log('ADMIN_LOGOUT', 'admin', $adminId);
        }

        Auth::guard('web')->logout();

        try {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Throwable $e) {
            // Sesi sudah expired atau tidak valid
        }

        return redirect()->route('admin.login')->with('status', 'Anda telah keluar dari sesi panel admin.');
    }
}
