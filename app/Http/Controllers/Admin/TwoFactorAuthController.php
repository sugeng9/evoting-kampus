<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class TwoFactorAuthController extends Controller
{
    /**
     * Tampilkan Halaman Aktivasi Wajib 2FA (Setup Google Authenticator)
     */
    public function showSetup(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('admin.login');
        }

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('admin.dashboard');
        }

        // Ambil atau buat secret baru di sesi pendaftaran
        $secret = session('two_factor_setup_secret');
        $recoveryCodes = session('two_factor_setup_recovery_codes');

        if (!$secret || !$recoveryCodes) {
            $secret = TotpService::generateSecret(20);
            $recoveryCodes = TotpService::generateRecoveryCodes(8);
            session([
                'two_factor_setup_secret' => $secret,
                'two_factor_setup_recovery_codes' => $recoveryCodes,
            ]);
        }

        $issuer = 'KPR Politeknik Negeri Bengkalis';
        $qrUri = TotpService::getOtpAuthUri($issuer, $user->username, $secret);

        return view('admin.auth.two_factor_setup', compact('user', 'secret', 'qrUri', 'recoveryCodes'));
    }

    /**
     * Konfirmasi dan Simpan Kunci 2FA setelah kode awal terverifikasi
     */
    public function confirmSetup(Request $request)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('admin.login');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Masukkan kode 6-digit dari aplikasi Google Authenticator.',
            'code.size' => 'Kode harus terdiri dari tepat 6 digit angka.',
        ]);

        $secret = session('two_factor_setup_secret');
        $recoveryCodes = session('two_factor_setup_recovery_codes');

        if (!$secret || !$recoveryCodes) {
            return redirect()->route('admin.2fa.setup')->with('error', 'Sesi inisialisasi 2FA telah kedaluwarsa. Silakan coba kembali.');
        }

        if (!TotpService::verify($secret, $request->input('code'), 1)) {
            return back()->withErrors([
                'code' => 'Kode verifikasi TOTP tidak cocok atau kedaluwarsa. Pastikan jam pada perangkat Anda telah sinkron.',
            ]);
        }

        // Simpan 2FA ke akun admin (terenkripsi otomatis via cast Model User)
        $user->two_factor_secret = $secret;
        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->two_factor_confirmed_at = now();
        $user->save();

        session()->forget(['two_factor_setup_secret', 'two_factor_setup_recovery_codes']);
        session([
            'admin_2fa_passed' => true,
            'admin_last_activity' => time(),
        ]);

        AuditLog::record(
            aksi: 'ADMIN_2FA_ACTIVATED',
            target: "admin:{$user->id}",
            payload: [
                'username' => $user->username,
                'name' => $user->name,
                'ip' => $request->ip(),
            ],
            aktor: "admin:{$user->id} ({$user->name})"
        );

        return redirect()->route('admin.dashboard')
            ->with('success', 'Autentikasi Dua Faktor (2FA Google Authenticator) berhasil diaktifkan! Akun Anda kini terlindungi secara maksimal.');
    }

    /**
     * Tampilkan Halaman Tantangan 2FA saat Login
     */
    public function showChallenge(Request $request)
    {
        if (session('admin_2fa_passed') === true && Auth::guard('web')->check()) {
            return redirect()->route('admin.dashboard');
        }

        $userId = session('admin_2fa_user_id') ?? Auth::guard('web')->id();
        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.two_factor_challenge', compact('user'));
    }

    /**
     * Verifikasi Tantangan 2FA (Kode TOTP atau Recovery Code)
     */
    public function verifyChallenge(Request $request)
    {
        $userId = session('admin_2fa_user_id') ?? Auth::guard('web')->id();
        if (!$userId) {
            return redirect()->route('admin.login');
        }

        $user = User::findOrFail($userId);
        $throttleKey = 'admin-2fa:' . $user->id . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'code' => "Terlalu banyak percobaan 2FA gagal. Silakan tunggu {$seconds} detik.",
            ]);
        }

        // Pilihan A: Menggunakan Kode Pemulihan (Recovery Code)
        if ($request->filled('recovery_code')) {
            $recoveryInput = trim($request->input('recovery_code'));
            $storedCodes = (array) ($user->two_factor_recovery_codes ?? []);

            $remainingCodes = TotpService::consumeRecoveryCode($storedCodes, $recoveryInput);

            if ($remainingCodes !== null) {
                RateLimiter::clear($throttleKey);

                $user->two_factor_recovery_codes = $remainingCodes;
                $user->save();

                Auth::guard('web')->login($user, session('admin_2fa_remember', false));
                session()->forget(['admin_2fa_user_id', 'admin_2fa_remember']);
                session([
                    'admin_2fa_passed' => true,
                    'admin_last_activity' => time(),
                ]);

                AuditLog::record(
                    aksi: 'ADMIN_2FA_RECOVERY_CODE_USED',
                    target: "admin:{$user->id}",
                    payload: [
                        'username' => $user->username,
                        'remaining_codes_count' => count($remainingCodes),
                        'ip' => $request->ip(),
                    ],
                    aktor: "admin:{$user->id} ({$user->name})"
                );

                return redirect()->intended(route('admin.dashboard'))
                    ->with('warning', 'Anda berhasil masuk menggunakan Kode Pemulihan. Sisa kode pemulihan Anda: ' . count($remainingCodes) . ' kode.');
            }

            RateLimiter::hit($throttleKey, 300);
            return back()->withErrors([
                'recovery_code' => 'Kode pemulihan tidak valid atau sudah pernah digunakan sebelumnya.',
            ]);
        }

        // Pilihan B: Menggunakan Kode 6-Digit TOTP Standar
        $code = trim(str_replace(' ', '', (string) $request->input('code')));
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            return back()->withErrors(['code' => 'Masukkan 6 digit angka kode TOTP.']);
        }

        if (TotpService::verify($user->two_factor_secret, $code, 1)) {
            RateLimiter::clear($throttleKey);

            Auth::guard('web')->login($user, session('admin_2fa_remember', false));
            session()->forget(['admin_2fa_user_id', 'admin_2fa_remember']);
            session([
                'admin_2fa_passed' => true,
                'admin_last_activity' => time(),
            ]);

            AuditLog::record(
                aksi: 'ADMIN_2FA_SUCCESS',
                target: "admin:{$user->id}",
                payload: [
                    'username' => $user->username,
                    'name' => $user->name,
                    'ip' => $request->ip(),
                ],
                aktor: "admin:{$user->id} ({$user->name})"
            );

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, 300);
        $attemptsLeft = max(0, 5 - RateLimiter::attempts($throttleKey));

        AuditLog::record(
            aksi: 'ADMIN_2FA_FAILED',
            target: "admin:{$user->id}",
            payload: [
                'username' => $user->username,
                'ip' => $request->ip(),
            ],
            aktor: "admin:{$user->id} ({$user->name})"
        );

        return back()->withErrors([
            'code' => "Kode verifikasi salah atau kedaluwarsa. Sisa percobaan: {$attemptsLeft}x.",
        ]);
    }
}
