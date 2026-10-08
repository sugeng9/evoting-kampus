<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PendaftaranRequest;
use App\Http\Requests\OtpVerifyRequest;
use App\Models\KodePendaftaran;
use App\Models\OtpCode;
use App\Models\PemilihMaster;
use App\Models\SystemPhase;
use App\Models\Voter;
use App\Mail\OtpMail;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PemilihRegistrationController extends Controller
{
    /** Tampilkan form pendaftaran (dialihkan ke halaman login tab registrasi). */
    public function showForm(Request $request)
    {
        $phase = SystemPhase::current();
        if (! $phase->allowsPendaftaran()) {
            return redirect()->route('nim.status.form')
                ->with('error', 'Pendaftaran telah ditutup. Sistem dalam fase ' . strtoupper($phase->fase) . '.');
        }

        return redirect()->route('voter.login', array_filter([
            'tab' => 'register',
            'kode' => $request->query('kode'),
        ]));
    }

    /** Validasi data master, kirim OTP ke email. */
    public function sendOtp(PendaftaranRequest $request): RedirectResponse
    {
        $phase = SystemPhase::current();
        if (! $phase->allowsPendaftaran()) {
            return back()->with('error', 'Fase pendaftaran telah ditutup.');
        }

        if (app()->environment('local')) {
            try {
                \Illuminate\Support\Facades\Cache::flush();
            } catch (\Throwable $e) {}
        }

        // 1. Verifikasi NIM + tanggal lahir terhadap data master resmi kampus (BAAK)
        $master = PemilihMaster::findByNim($request->nim);

        if (! $master || ! $master->verifyTanggalLahir(trim($request->tanggal_lahir))) {
            AuditLogger::log('PENDAFTARAN_GAGAL_VERIFIKASI',
                "NIM {$request->nim} — data tidak cocok.", $request->nim, 'voter');
            return back()->withInput()->with('error', 'Data NIM dan Tanggal Lahir tidak cocok dengan data resmi mahasiswa kampus. Pastikan tanggal lahir sesuai format (YYYY-MM-DD).');
        }

        // 2. Cek apakah NIM sudah pernah registrasi mandiri dan akunnya sudah aktif
        $existingVoter = Voter::where('nim', $request->nim)->first();
        if ($existingVoter && $existingVoter->pendaftaran_status === 'aktif' && $existingVoter->registered_at !== null && $existingVoter->email !== null) {
            return back()->withInput()->with('error', "NIM {$request->nim} sudah pernah mendaftar dan akun telah aktif. Silakan langsung masuk pada tab 'Masuk (Login)'. Jika Anda lupa kata sandi, mintalah reset sandi ke panitia di bilik suara.");
        }

        // 3. Jika voter pernah dicabut, wajib pakai kode pendaftaran dari helpdesk
        if ($existingVoter && $existingVoter->pendaftaran_status === 'dicabut') {
            if (! $request->filled('kode_pendaftaran')) {
                return back()->withInput()
                    ->with('error', 'Akun telah dinonaktifkan / dicabut. Masukkan kode pemulihan pendaftaran dari helpdesk.');
            }

            // Validasi kode: bandingkan hash
            $kodeModel = KodePendaftaran::where('nim', $request->nim)
                ->whereNull('used_at')
                ->where('expired_at', '>', now())
                ->get()
                ->first(fn($k) => hash('sha256', $request->kode_pendaftaran) === $k->kode_hash);

            if (! $kodeModel) {
                return back()->withInput()
                    ->with('error', 'Kode pemulihan pendaftaran tidak valid atau sudah kedaluwarsa.');
            }

            // Simpan kode yang valid ke session untuk dipakai saat verifikasi OTP
            session(['pendaftaran_kode_id' => $kodeModel->id]);
        }

        // 4. Buat OTP dan kirim email
        $otpModel = OtpCode::createForNim($request->nim, $request->email);

        \Illuminate\Support\Facades\Log::info("==========================================");
        \Illuminate\Support\Facades\Log::info("[OTP KPR POLBENG] NIM: {$request->nim} | OTP: {$otpModel->plain_otp} | Ke: {$request->email}");
        \Illuminate\Support\Facades\Log::info("==========================================");

        $errorMsg = $this->deliverOtpMail($request->nim, $request->email, $otpModel->plain_otp);

        // 5. Simpan data ke session untuk langkah verifikasi
        session([
            'pendaftaran_nim'   => $request->nim,
            'pendaftaran_email' => $request->email,
            'pendaftaran_data'  => $request->only(['email', 'no_hp', 'password']),
        ]);

        AuditLogger::log('OTP_DIKIRIM', "OTP dikirim ke {$request->email} untuk NIM {$request->nim}.", $request->nim, 'voter');

        if ($errorMsg) {
            return redirect()->route('daftar.otp.form')
                ->with('warning', 'Kode OTP berhasil dibuat. Namun pengiriman email ke ' . $request->email . ' mengalami kendala: ' . $errorMsg);
        }

        return redirect()->route('daftar.otp.form')
            ->with('success', 'Kode OTP telah dikirimkan ke email ' . $request->email . '. Silakan periksa Kotak Masuk atau folder Spam.');
    }

    /**
     * Kirim ulang kode OTP jika belum diterima pemilih.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $nim = session('pendaftaran_nim');
        $email = session('pendaftaran_email');

        if (!$nim || !$email) {
            return redirect()->route('voter.login', ['tab' => 'register'])
                ->with('error', 'Sesi pendaftaran telah berakhir. Silakan isi form kembali.');
        }

        $otpModel = OtpCode::createForNim($nim, $email);

        \Illuminate\Support\Facades\Log::info("==========================================");
        \Illuminate\Support\Facades\Log::info("[RESEND OTP KPR POLBENG] NIM: {$nim} | OTP: {$otpModel->plain_otp} | Ke: {$email}");
        \Illuminate\Support\Facades\Log::info("==========================================");

        $errorMsg = $this->deliverOtpMail($nim, $email, $otpModel->plain_otp);

        AuditLogger::log('OTP_DIKIRIM_ULANG', "OTP dikirim ulang ke {$email} untuk NIM {$nim}.", $nim, 'voter');

        if ($errorMsg) {
            return back()->with('warning', 'Kode OTP baru berhasil dibuat. Namun pengiriman email mengalami kendala: ' . $errorMsg);
        }

        return back()->with('success', 'Kode OTP baru telah dikirimkan ulang ke email ' . $email . '. Silakan periksa Kotak Masuk atau folder Spam.');
    }

    /**
     * Helper untuk mengirimkan email OTP via Resend API / SMTP.
     */
    protected function deliverOtpMail(string $nim, string $email, string $plainOtp): ?string
    {
        $mailHost = env('MAIL_HOST', config('mail.mailers.smtp.host', ''));
        $mailPassword = env('MAIL_PASSWORD', config('mail.mailers.smtp.password', ''));
        $mailUsername = env('MAIL_USERNAME', config('mail.mailers.smtp.username', ''));

        if (empty($mailPassword) || str_contains($mailPassword, 'TEMPEL_KODE') || str_contains($mailPassword, 'GANTI_DENGAN')) {
            return 'Kunci API Resend / SMTP belum diisi di .env';
        }

        // Kirim via HTTPS REST API resmi Resend
        if (str_contains($mailHost, 'resend.com') || str_starts_with($mailPassword, 're_')) {
            $fromAddress = env('MAIL_FROM_ADDRESS', 'onboarding@resend.dev');
            $fromName = env('MAIL_FROM_NAME', 'KPR PEMIRA POLBENG');

            $emailHtml = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px; color: #334155;">
    <div style="max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px; border: 1px solid #e2e8f0; text-align: center;">
        <h2 style="color: #0f172a; margin-bottom: 8px;">KPR PEMIRA POLBENG</h2>
        <p style="color: #64748b; font-size: 14px; margin-top: 0;">Otentikasi Akun Pemilih Mahasiswa</p>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        <p style="text-align: left; font-size: 15px;">Halo Mahasiswa dengan NIM <strong>{$nim}</strong>,</p>
        <p style="text-align: left; font-size: 14px; color: #475569;">Berikut adalah Kode OTP rahasia Anda untuk menyelesaikan pendaftaran akun pemilih:</p>
        <div style="margin: 25px 0; padding: 18px; background: #f1f5f9; border-radius: 8px; border: 2px dashed #cbd5e1;">
            <span style="font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #0284c7; font-family: monospace;">{$plainOtp}</span>
        </div>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
            Kode OTP ini berlaku selama <strong>10 menit</strong> dan hanya dapat digunakan 1 kali.<br>
            Jangan berikan kode ini kepada siapa pun demi kerahasiaan hak suara Anda.
        </p>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        <p style="font-size: 11px; color: #94a3b8;">Komisi Pemilihan Raya (KPR) &bull; Politeknik Negeri Bengkalis</p>
    </div>
</body>
</html>
HTML;

            try {
                $response = Http::timeout(10)
                    ->withoutVerifying()
                    ->withToken($mailPassword)
                    ->post('https://api.resend.com/emails', [
                        'from'    => "{$fromName} <{$fromAddress}>",
                        'to'      => [$email],
                        'subject' => '[PEMIRA POLBENG] Kode OTP Pendaftaran Pemilih',
                        'html'    => $emailHtml,
                    ]);

                if (! $response->successful()) {
                    $resendJson = $response->json();
                    $errorMsg = $resendJson['message'] ?? $response->body();
                    \Illuminate\Support\Facades\Log::error("[RESEND API ERROR] Ke: {$email} | Response: {$errorMsg}");
                    AuditLogger::log('OTP_MAIL_GAGAL', "Resend API error: {$errorMsg}");

                    return $errorMsg;
                }
            } catch (\Throwable $e) {
                AuditLogger::log('OTP_MAIL_GAGAL', "Gagal menghubungi API Resend: {$e->getMessage()}");
                return $e->getMessage();
            }
        } else {
            // Fallback SMTP standar (misal Gmail SMTP)
            try {
                Mail::to($email)->send(new OtpMail($plainOtp, $nim));
            } catch (\Throwable $e) {
                AuditLogger::log('OTP_MAIL_GAGAL', "Gagal kirim OTP ke {$email}: {$e->getMessage()}");
                return $e->getMessage();
            }
        }

        return null;
    }

    /** Tampilkan form verifikasi OTP. */
    public function showOtpForm()
    {
        if (! session('pendaftaran_nim')) {
            return redirect()->route('daftar.form');
        }

        $nim   = session('pendaftaran_nim');
        $email = session('pendaftaran_email');
        // Mask email: b***@gmail.com
        $maskedEmail = preg_replace('/(.{1}).+(@.+)/', '$1***$2', $email);

        return view('auth.pendaftaran.otp', [
            'nim'         => $nim,
            'maskedEmail' => $maskedEmail,
        ]);
    }

    /** Verifikasi OTP dan buat akun pending. */
    public function verifyOtpAndRegister(OtpVerifyRequest $request): RedirectResponse
    {
        $nim = session('pendaftaran_nim');

        if (! $nim || $nim !== $request->nim) {
            return redirect()->route('daftar.form')->with('error', 'Sesi pendaftaran tidak valid. Mulai ulang.');
        }

        // Cari OTP yang valid (belum digunakan, belum kedaluwarsa)
        $otpCode = OtpCode::where('nim', $nim)
            ->whereNull('used_at')
            ->where('expired_at', '>', now())
            ->latest()
            ->first();

        if (! $otpCode) {
            return back()->with('error', 'Kode OTP tidak ditemukan atau sudah kedaluwarsa. Kembali dan minta OTP baru.');
        }

        if ($otpCode->attemptsExceeded()) {
            AuditLogger::log('OTP_ATTEMPTS_EXCEEDED', "NIM $nim melebihi 5 percobaan OTP.", $nim, 'voter');
            return back()->with('error', 'Terlalu banyak percobaan salah. Kembali dan minta OTP baru.');
        }

        if (! $otpCode->verify($request->otp)) {
            $remaining = 5 - $otpCode->fresh()->percobaan;
            return back()->with('error', "Kode OTP salah. Sisa percobaan: {$remaining}.");
        }

        // OTP benar — buat atau perbarui akun voter
        $data = session('pendaftaran_data');
        $master = PemilihMaster::findByNim($nim);

        $phase = SystemPhase::current();
        $initialStatus = in_array($phase->fase, ['aktif', 'voting']) ? 'aktif' : 'pending';

        Voter::ensureSchema();

        Voter::updateOrCreate(
            ['nim' => $nim],
            [
                'name'               => $master?->nama ?? ('Mahasiswa ' . $nim),
                'study_program'      => $master?->prodi ?? 'Teknik Informatika',
                'faculty'            => 'Politeknik Negeri Bengkalis',
                'angkatan'           => $master?->angkatan ?? (strlen($nim) >= 4 ? '20' . substr($nim, 2, 2) : null),
                'email'              => $data['email'],
                'no_hp'              => $data['no_hp'] ?? null,
                'password'           => Hash::make($data['password']),
                'pendaftaran_status' => 'aktif',
                'registered_at'      => now(),
                'activated_at'       => now(),
                'is_active'          => true,
                // Kolom lama — pertahankan nilai default agar sistem voting lama tetap berjalan
                'dpt_status'         => 'DPT',
                'academic_status'    => 'aktif',
            ]
        );

        // Tandai kode pendaftaran sebagai terpakai (jika ada)
        if ($kodeId = session('pendaftaran_kode_id')) {
            KodePendaftaran::find($kodeId)?->update(['used_at' => now()]);
        }

        // Bersihkan session
        session()->forget(['pendaftaran_nim', 'pendaftaran_email', 'pendaftaran_data', 'pendaftaran_kode_id']);

        AuditLogger::log('PENDAFTARAN_SUKSES', "Akun pending berhasil dibuat untuk NIM $nim.", $nim, 'voter');

        return redirect()->route('daftar.sukses');
    }

    /** Halaman sukses pendaftaran. */
    public function sukses()
    {
        return view('auth.pendaftaran.sukses');
    }
}
