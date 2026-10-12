<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoterLoginRequest;
use App\Models\SystemPhase;
use App\Models\Voter;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PemilihLoginController extends Controller
{
    /** Tampilkan form login pemilih. */
    public function showLoginForm()
    {
        return redirect()->route('voter.login');
    }

    public function login(VoterLoginRequest $request)
    {
        $phase = SystemPhase::current();
        $nim = trim((string) $request->nim);

        // 1. Proteksi Credential Stuffing per-IP (Maks 10 percobaan per menit)
        $ipThrottleKey = 'voter-login-ip:' . $request->ip();
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($ipThrottleKey, 10)) {
            $ipSeconds = \Illuminate\Support\Facades\RateLimiter::availableIn($ipThrottleKey);
            AuditLogger::log('LOGIN_IP_RATE_LIMITED', "IP {$request->ip()} melebihi 10 percobaan login/menit.", null, 'voter');

            return back()->withInput(['nim' => $nim])
                ->with('error', "Terlalu banyak percobaan login dari alamat IP Anda. Silakan tunggu {$ipSeconds} detik sebelum mencoba kembali.");
        }

        $throttleKey = 'voter-login:' . \Illuminate\Support\Str::lower($nim) . '|' . $request->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            $minutes = max(1, (int) ceil($seconds / 60));
            AuditLogger::log('LOGIN_RATE_LIMITED', "NIM {$nim} terkunci karena 3x percobaan login gagal.", $nim, 'voter');

            return back()->withInput(['nim' => $nim])
                ->with('error', "Terlalu banyak percobaan login gagal (3x). Akun Anda dikunci sementara. Sisa waktu lockout: {$minutes} menit ({$seconds} detik).");
        }

        // Cari voter berdasarkan NIM
        $voter = Voter::where('nim', $nim)->first();

        // Pesan error GENERIK — tidak membedakan "NIM tidak ada" vs "password salah"
        $genericError = 'NIM atau password tidak valid.';
        $dummyBcryptHash = '$2y$10$w8gJ5T6L27N6YtEw1eB.Iu8rF8qO8m8h5qT2v6Z1x3w7y9z0a1b2c';

        if (! $voter) {
            // Anti Timing-Attack: Jalankan komputasi bcrypt berbeban identik
            Hash::check($request->password, $dummyBcryptHash);

            \Illuminate\Support\Facades\RateLimiter::hit($ipThrottleKey, 60);
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 420);
            $remaining = max(0, 3 - \Illuminate\Support\Facades\RateLimiter::attempts($throttleKey));
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            $minutes = max(1, (int) ceil($seconds / 60));
            $warning = $remaining > 0 ? " Sisa kesempatan: {$remaining}x." : " Akun dikunci sementara. Sisa waktu penalti: {$minutes} menit ({$seconds} detik).";

            AuditLogger::log('LOGIN_GAGAL_NIM', "NIM {$nim} tidak ditemukan.", null, 'voter');
            return back()->withInput(['nim' => $nim])->with('error', $genericError . $warning);
        }

        // Cek lockout
        if ($voter->isLocked()) {
            $menitSisa = max(1, (int) now()->diffInMinutes($voter->locked_until, false) * -1);
            return back()->withInput(['nim' => $nim])
                ->with('error', "Akun Anda terkunci sementara karena terlalu banyak percobaan. Coba lagi dalam ±{$menitSisa} menit.");
        }

        // Verifikasi password
        if (! Hash::check($request->password, $voter->password)) {
            \Illuminate\Support\Facades\RateLimiter::hit($ipThrottleKey, 60);
            \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 420);
            $voter->incrementLoginAttempts();
            $attempts = $voter->fresh()->login_attempts;
            $remaining = max(0, 3 - \Illuminate\Support\Facades\RateLimiter::attempts($throttleKey));
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            $minutes = max(1, (int) ceil($seconds / 60));
            $warning = $remaining > 0 ? " Sisa kesempatan: {$remaining}x sebelum dikunci 7 menit." : " Akun dikunci sementara. Sisa waktu penalti: {$minutes} menit ({$seconds} detik).";

            AuditLogger::log('LOGIN_GAGAL_PASSWORD', "NIM {$voter->nim} — percobaan gagal ke-$attempts.", $voter->nim, 'voter');

            $showCaptcha = $attempts >= 3;
            $message     = $genericError . $warning;

            if ($voter->fresh()->isLocked()) {
                $message = 'Akun terkunci karena 3+ percobaan gagal. Hubungi helpdesk jika Anda lupa password.';
            }

            return back()->withInput(['nim' => $nim])
                ->with('error', $message)
                ->with('showCaptcha', $showCaptcha);
        }

        // Jika akun masih pending dari pendaftaran sebelumnya, otomatis aktifkan karena sudah lulus OTP
        if ($voter->pendaftaran_status === 'pending') {
            $voter->update([
                'pendaftaran_status' => 'aktif',
                'activated_at'       => now(),
            ]);
        }

        if ($voter->pendaftaran_status === 'dicabut') {
            return back()->withInput(['nim' => $request->nim])
                ->with('error', 'Akun Anda telah dinonaktifkan oleh Helpdesk terkait laporan sengketa. Hubungi helpdesk KPU-M untuk verifikasi identitas.');
        }

        // Login berhasil
        \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
        $voter->clearLoginAttempts();

        // Satu sesi aktif: simpan session ID baru ke DB (sesi lama akan diblokir oleh EnsureSingleVoterSession)
        $request->session()->regenerate();
        $voter->update([
            'current_session_id' => $request->session()->getId(),
            'last_login_at'      => now(),
            'last_login_ip'      => $request->ip(),
            'last_login_user_agent' => $request->userAgent(),
        ]);

        Auth::guard('voter')->login($voter, false); // Tidak remember me — lebih aman

        AuditLogger::log('LOGIN_SUKSES', "Pemilih NIM {$voter->nim} berhasil login.", $voter->nim, 'voter');

        return redirect()->intended(route('voter.dashboard'));
    }

    /** Logout pemilih. */
    public function logout(Request $request)
    {
        $voter = Auth::guard('voter')->user();

        if ($voter) {
            $voter->update(['current_session_id' => null]);
            AuditLogger::log('LOGOUT', "Pemilih NIM {$voter->nim} logout.", $voter->nim, 'voter');
        }

        Auth::guard('voter')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('voter.login');
    }
}
