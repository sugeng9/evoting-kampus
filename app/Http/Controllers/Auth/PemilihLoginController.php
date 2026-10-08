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

    /** Proses login pemilih dengan NIM + password. */
    public function login(VoterLoginRequest $request)
    {
        $phase = SystemPhase::current();

        // Cari voter berdasarkan NIM
        $voter = Voter::where('nim', $request->nim)->first();

        // Pesan error GENERIK — tidak membedakan "NIM tidak ada" vs "password salah"
        $genericError = 'NIM atau password tidak valid.';

        if (! $voter) {
            AuditLogger::log('LOGIN_GAGAL_NIM', "NIM {$request->nim} tidak ditemukan.", null, 'voter');
            return back()->withInput(['nim' => $request->nim])->with('error', $genericError);
        }

        // Cek lockout
        if ($voter->isLocked()) {
            $menitSisa = max(1, (int) now()->diffInMinutes($voter->locked_until, false) * -1);
            return back()->withInput(['nim' => $request->nim])
                ->with('error', "Akun Anda terkunci sementara karena terlalu banyak percobaan. Coba lagi dalam ±{$menitSisa} menit.");
        }

        // Verifikasi password
        if (! Hash::check($request->password, $voter->password)) {
            $voter->incrementLoginAttempts();
            $attempts = $voter->fresh()->login_attempts;

            AuditLogger::log('LOGIN_GAGAL_PASSWORD', "NIM {$voter->nim} — percobaan gagal ke-$attempts.", $voter->nim, 'voter');

            $showCaptcha = $attempts >= 3;
            $message     = $genericError;

            if ($voter->fresh()->isLocked()) {
                $message = 'Akun terkunci karena 3+ percobaan gagal. Hubungi helpdesk jika Anda lupa password.';
            }

            return back()->withInput(['nim' => $request->nim])
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
