<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoterPinLoginRequest;
use App\Models\AuditLog;
use App\Models\Voter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class VoterAuthController extends Controller
{
    public const POLBENG_PREFIXES = ['120110', '120120', '120130', '120140', '120150', '120160', '120170', '120180', '120190', '120100'];

    /**
     * Tampilkan form login pemilih
     */
    public function showLoginForm()
    {
        if (Auth::guard('voter')->check()) {
            return redirect()->route('voter.dashboard');
        }



        $phase = \App\Models\SystemPhase::current();

        // Siapkan data mahasiswa demo untuk pengujian pendaftaran
        \App\Models\PemilihMaster::ensureRegistrationDummyData();
        $dummyStudents = \App\Models\PemilihMaster::getRegistrationDummyList();
        foreach ($dummyStudents as &$student) {
            $existing = \App\Models\Voter::where('nim', $student['nim'])->first();
            $student['is_registered'] = ($existing && $existing->pendaftaran_status === 'aktif' && $existing->registered_at !== null && $existing->email !== null);
        }
        unset($student);

        return view('voter.login', [
            'phase' => $phase,
            'fase' => $phase->fase,
            'allowsPendaftaran' => $phase->allowsPendaftaran(),
            'dummyStudents' => $dummyStudents,
        ]);
    }

    /**
     * Proses Login Mahasiswa dengan Anti-Brute Force Rate Limiting
     */
    public function login(VoterPinLoginRequest $request)
    {
        $nim = trim($request->input('nim'));
        $pin = trim($request->input('pin'));

        // 1. Proteksi Credential Stuffing: Batasi maksimal 10 percobaan per menit per alamat IP
        $ipThrottleKey = 'voter-login-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipThrottleKey, 10)) {
            $ipSeconds = RateLimiter::availableIn($ipThrottleKey);
            AuditLog::log('LOGIN_IP_RATE_LIMITED', 'voter', null, [
                'ip' => $request->ip(),
                'retry_after' => $ipSeconds,
            ]);

            return back()->withErrors([
                'nim' => "Terlalu banyak percobaan login dari alamat IP Anda (indikasi brute force/credential stuffing). Silakan tunggu {$ipSeconds} detik sebelum mencoba kembali.",
            ])->onlyInput('nim');
        }

        // 2. Proteksi Brute Force: Batasi 3 percobaan gagal per 7 menit per kombinasi NIM + IP
        $throttleKey = 'voter-login:' . Str::lower($nim) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = max(1, (int) ceil($seconds / 60));
            
            AuditLog::log('LOGIN_RATE_LIMIT_TRIGGERED', 'voter', null, [
                'nim' => $nim,
                'retry_after' => $seconds,
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'nim' => "Terlalu banyak percobaan login gagal (3x). Akses login Anda dikunci sementara. Sisa waktu lockout: {$minutes} menit ({$seconds} detik).",
            ])->onlyInput('nim');
        }

        // 3. Cari Data Mahasiswa di DPT
        $voter = Voter::where('nim', $nim)->first();

        // Anti Timing-Attack: Hash bcrypt valid untuk menyerap perbedaan waktu eksekusi saat NIM tidak ditemukan
        $dummyBcryptHash = '$2y$10$w8gJ5T6L27N6YtEw1eB.Iu8rF8qO8m8h5qT2v6Z1x3w7y9z0a1b2c';
        $genericLoginError = 'NIM atau kata sandi/PIN tidak cocok atau belum terdaftar.';

        if (!$voter) {
            // Anti Timing-Attack: Jalankan komputasi bcrypt berbeban identik
            Hash::check($pin, $dummyBcryptHash);

            RateLimiter::hit($ipThrottleKey, 60);
            RateLimiter::hit($throttleKey, 420); // 7 menit penalti lockout
            $remaining = max(0, 3 - RateLimiter::attempts($throttleKey));
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = max(1, (int) ceil($seconds / 60));
            $warning = $remaining > 0 ? " Sisa kesempatan: {$remaining}x sebelum dikunci 7 menit." : " Akun dikunci sementara. Sisa waktu penalti: {$minutes} menit ({$seconds} detik).";

            AuditLog::log('LOGIN_FAILED_NIM_NOT_FOUND', 'voter', null, [
                'nim' => $nim,
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'nim' => "{$genericLoginError}{$warning}",
            ])->onlyInput('nim');
        }

        // Verifikasi Kata Sandi / PIN Akun
        if (!Hash::check($pin, $voter->password)) {
            RateLimiter::hit($ipThrottleKey, 60);
            RateLimiter::hit($throttleKey, 420); // 7 menit penalti lockout
            $remaining = max(0, 3 - RateLimiter::attempts($throttleKey));
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = max(1, (int) ceil($seconds / 60));
            $warning = $remaining > 0 ? " Sisa kesempatan: {$remaining}x sebelum dikunci 7 menit." : " Akun dikunci sementara. Sisa waktu penalti: {$minutes} menit ({$seconds} detik).";

            AuditLog::log('LOGIN_FAILED_INVALID_PIN', 'voter', $voter->id, [
                'nim' => $nim,
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'nim' => "{$genericLoginError}{$warning}",
            ])->onlyInput('nim');
        }

        // 4. Verifikasi Status Pendaftaran Akun (Pending / Dicabut)
        if ($voter->pendaftaran_status === 'dicabut') {
            RateLimiter::hit($throttleKey, 300);

            AuditLog::log('LOGIN_FAILED_ACCOUNT_REVOKED', 'voter', $voter->id, [
                'nim' => $nim,
            ]);

            return back()->withErrors([
                'nim' => 'Akun pemilih Anda telah dinonaktifkan / dicabut. Silakan hubungi Helpdesk KPU-M untuk verifikasi.',
            ])->onlyInput('nim');
        }

        if ($voter->pendaftaran_status === 'pending') {
            $phase = \App\Models\SystemPhase::current();
            if (!$phase->allowsLogin()) {
                return back()->withErrors([
                    'nim' => "Pendaftaran akun Anda berhasil dicatat, namun sistem saat ini dalam Fase " . strtoupper($phase->fase) . ". Akses bilik suara akan dibuka saat penetapan DPT / pemilihan.",
                ])->onlyInput('nim');
            }
        }

        // 5. Verifikasi Status Kelayakan Akademik & Keaktifan Hak Suara (Regulasi Pemira & Ormawa)
        if (!$voter->isEligible()) {
            AuditLog::log('LOGIN_ATTEMPT_INELIGIBLE_VOTER', 'voter', $voter->id, [
                'nim' => $voter->nim,
                'academic_status' => $voter->academic_status,
            ]);

            $reason = match (strtolower($voter->academic_status ?? '')) {
                'cuti' => 'tercatat sedang Cuti Akademik',
                'do' => 'tercatat Drop Out (DO)',
                'wisuda' => 'tercatat telah Lulus/Wisuda (Alumni)',
                'bermasalah' => 'tercatat memiliki sanksi Skorsing / Kasus Akademik',
                default => 'berstatus Tidak Memenuhi Syarat (TMS)',
            };

            return back()->withErrors([
                'nim' => "Hak suara Anda tidak memenuhi syarat (TMS) karena {$reason}. Berdasarkan Regulasi Pemira yang disahkan bersama Ormawa, hanya mahasiswa Aktif dan Magang yang berhak memilih.",
            ])->onlyInput('nim');
        }

        // 6. Berhasil Login: Bersihkan Throttle & Regenerate Session (Mencegah Session Fixation)
        RateLimiter::clear($throttleKey);

        Auth::guard('voter')->login($voter);
        $request->session()->regenerate();

        // Update Jejak Login Terakhir & Session ID untuk Isolasi Single Session
        $voter->update([
            'current_session_id' => $request->session()->getId(),
            'last_login_ip' => $request->ip(),
            'last_login_user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'last_login_at' => now(),
        ]);

        AuditLog::log('LOGIN_SUCCESS', 'voter', $voter->id, [
            'nim' => $voter->nim,
        ]);

        return redirect()->intended(route('voter.dashboard'));
    }

    /**
     * Logout Pemilih dan Invalidate Session
     */
    public function logout(Request $request)
    {
        $voterId = Auth::guard('voter')->id();
        if ($voterId) {
            AuditLog::log('LOGOUT', 'voter', $voterId);
        }

        Auth::guard('voter')->logout();

        try {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } catch (\Throwable $e) {
            // Sesi sudah expired atau tidak valid
        }

        return redirect()->route('voter.login')->with('status', 'Anda telah berhasil keluar dari bilik suara.');
    }
}

