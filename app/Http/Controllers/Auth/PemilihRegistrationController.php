<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\OtpVerifyRequest;
use App\Http\Requests\PendaftaranRequest;
use App\Http\Requests\ResetDemoRequest;
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
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

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

        // 0. Anti-Bot Time-Trap (Mencegah submit instan via bot/script)
        if ($request->filled('form_time')) {
            try {
                $formTime = (int) decrypt($request->form_time);
                if ((time() - $formTime) < 2) {
                    AuditLogger::log('BOT_FAST_SUBMISSION', "Submit form terlalu cepat (<2s) dari IP {$request->ip()}.", $request->nim, 'system');
                    return back()->withInput()->with('error', 'Pengisian formulir terdeteksi terlalu cepat. Harap tunggu beberapa detik lalu coba lagi.');
                }
            } catch (\Throwable $e) {}
        }

        // 0. Anti-Spam Rate Limit per IP (Maksimal 6 permohonan pendaftaran per 10 menit)
        $ipKey = 'reg-otp-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 6)) {
            $seconds = RateLimiter::availableIn($ipKey);
            AuditLogger::log('REG_IP_THROTTLED', "IP {$request->ip()} melebihi batas request pendaftaran.", $request->nim, 'system');
            return back()->withInput()->with('error', "Terlalu banyak permintaan pendaftaran dari jaringan Anda. Harap tunggu {$seconds} detik lagi.");
        }

        // 0. Anti-Brute Force NIM & Tanggal Lahir (Kunci jika 5 kali berturut-turut salah)
        $nimFailKey = 'reg-fail-nim:' . Str::lower($request->nim);
        if (RateLimiter::tooManyAttempts($nimFailKey, 5)) {
            $seconds = RateLimiter::availableIn($nimFailKey);
            AuditLogger::log('NIM_REG_LOCKED', "NIM {$request->nim} dikunci sementara karena 5x salah data.", $request->nim, 'system');
            return back()->withInput()->with('error', "NIM {$request->nim} terkunci sementara selama {$seconds} detik karena terlalu banyak percobaan data tidak cocok. Silakan coba lagi nanti.");
        }

        // 0. Batasi pengiriman OTP per NIM (Maksimal 3 kali dalam 10 menit untuk 1 NIM)
        $nimOtpKey = 'reg-otp-nim:' . Str::lower($request->nim);
        if (RateLimiter::tooManyAttempts($nimOtpKey, 3)) {
            $seconds = RateLimiter::availableIn($nimOtpKey);
            return back()->withInput()->with('error', "Kode OTP untuk NIM ini baru saja dikirimkan. Harap tunggu {$seconds} detik sebelum meminta kode baru.");
        }

        // 0b. Batas Kuota Harian (Daily Quota Ceiling) - Anti-Abuse & Anti-Bombing OTP (24 Jam)
        $dailyNimKey = 'otp-daily-nim:' . Str::lower($request->nim);
        $dailyEmailKey = 'otp-daily-email:' . hash('sha256', Str::lower(trim($request->email)));
        $dailyIpKey = 'otp-daily-ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($dailyNimKey, 5)) {
            $seconds = RateLimiter::availableIn($dailyNimKey);
            $hours = max(1, (int) ceil($seconds / 3600));
            AuditLogger::log('OTP_DAILY_LIMIT_EXCEEDED', "Batas kuota harian OTP tercapai untuk NIM {$request->nim}.", $request->nim, 'system');
            return back()->withInput()->with('error', "Batas kuota harian permintaan OTP untuk NIM {$request->nim} telah tercapai (maksimal 5x dalam 24 jam). Silakan coba lagi dalam {$hours} jam atau hubungi Helpdesk KPR.");
        }

        if (RateLimiter::tooManyAttempts($dailyEmailKey, 5)) {
            $seconds = RateLimiter::availableIn($dailyEmailKey);
            $hours = max(1, (int) ceil($seconds / 3600));
            AuditLogger::log('OTP_DAILY_LIMIT_EXCEEDED', "Batas kuota harian OTP tercapai untuk email {$request->email}.", $request->nim, 'system');
            return back()->withInput()->with('error', "Batas kuota harian permintaan OTP untuk alamat email ini telah tercapai (maksimal 5x dalam 24 jam). Silakan gunakan email lain atau hubungi Helpdesk KPR.");
        }

        if (RateLimiter::tooManyAttempts($dailyIpKey, 15)) {
            $seconds = RateLimiter::availableIn($dailyIpKey);
            $hours = max(1, (int) ceil($seconds / 3600));
            AuditLogger::log('OTP_DAILY_LIMIT_EXCEEDED', "Batas kuota harian OTP tercapai untuk IP {$request->ip()}.", $request->nim, 'system');
            return back()->withInput()->with('error', "Batas kuota harian permintaan OTP dari jaringan/IP Anda telah tercapai (maksimal 15x dalam 24 jam). Silakan coba lagi dalam {$hours} jam atau hubungi Helpdesk KPR.");
        }

        // 1. Verifikasi NIM + tanggal lahir terhadap data master resmi kampus (BAAK)
        $master = PemilihMaster::findByNim($request->nim);

        if (! $master) {
            // Anti-Timing Attack: Lakukan komputasi hash & parsing string identik agar durasi respon seragam
            hash('sha256', trim($request->tanggal_lahir));
            try {
                \Carbon\Carbon::parse(trim($request->tanggal_lahir))->format('Y-m-d');
                hash('sha256', 'dummy_timing_salt_guard');
            } catch (\Throwable $e) {}
        }

        if (! $master || ! $master->verifyTanggalLahir(trim($request->tanggal_lahir))) {
            RateLimiter::hit($nimFailKey, 900); // Penalti 15 menit
            RateLimiter::hit($ipKey, 600);
            $sisa = 5 - RateLimiter::attempts($nimFailKey);
            $warningTeks = $sisa > 0 ? " (Sisa kesempatan: {$sisa}x sebelum NIM dikunci sementara)" : "";

            AuditLogger::log('PENDAFTARAN_GAGAL_VERIFIKASI',
                "NIM {$request->nim} — data tidak cocok.", $request->nim, 'voter');
            return back()->withInput()->with('error', 'Data NIM dan Tanggal Lahir tidak cocok dengan data resmi mahasiswa kampus. Pastikan tanggal lahir sesuai format (YYYY-MM-DD).' . $warningTeks);
        }

        // Reset kegagalan tanggal lahir karena data cocok
        RateLimiter::clear($nimFailKey);

        // 2. Cek apakah NIM sudah pernah registrasi mandiri dan akunnya sudah aktif
        $existingVoter = Voter::where('nim', $request->nim)->first();
        if ($existingVoter && $existingVoter->pendaftaran_status === 'aktif' && $existingVoter->registered_at !== null && $existingVoter->email !== null) {
            return back()->withInput()->with('error', "NIM {$request->nim} sudah pernah mendaftar dan akun telah aktif. Silakan langsung masuk pada tab 'Masuk (Login)'. Jika Anda lupa kata sandi, mintalah reset sandi ke panitia di bilik suara.");
        }

        // 2b. Cek keunikan email menggunakan Blind Index (HMAC-SHA256) agar 1 email tidak disalahgunakan untuk banyak NIM
        $emailHash = Voter::hashBlindIndex($request->email);
        $emailOwner = Voter::where('email_hash', $emailHash)
            ->where('nim', '!=', $request->nim)
            ->where('pendaftaran_status', 'aktif')
            ->first();

        if ($emailOwner) {
            return back()->withInput()->with('error', 'Alamat email ini sudah digunakan oleh akun mahasiswa lain. Harap gunakan alamat email pribadi Anda.');
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

        // 4. Buat OTP dan kirim email (Update rate limiter rentang pendek dan harian)
        RateLimiter::hit($nimOtpKey, 600);
        RateLimiter::hit($ipKey, 600);
        RateLimiter::hit($dailyNimKey, 86400);
        RateLimiter::hit($dailyEmailKey, 86400);
        RateLimiter::hit($dailyIpKey, 86400);

        $otpModel = OtpCode::createForNim($request->nim, $request->email);

        \Illuminate\Support\Facades\Log::info("==========================================");
        \Illuminate\Support\Facades\Log::info("[OTP KPR POLBENG] NIM: {$request->nim} | OTP: {$otpModel->plain_otp} | Ke: {$request->email}");
        \Illuminate\Support\Facades\Log::info("==========================================");

        // 4b. Kirim email OTP secara asinkron via Job Queue (Bebas dari HTTP Gateway Timeout saat lonjakan traffic)
        try {
            \App\Jobs\SendOtpEmailJob::dispatch($request->nim, $request->email, $otpModel->plain_otp);
        } catch (\Throwable $e) {
            // Fallback sinkron jika antrean queue belum siap
            $this->deliverOtpMail($request->nim, $request->email, $otpModel->plain_otp);
        }

        // 5. Simpan data ke session untuk langkah verifikasi
        session([
            'pendaftaran_nim'   => $request->nim,
            'pendaftaran_email' => $request->email,
            'pendaftaran_data'  => $request->only(['email', 'no_hp', 'password']),
        ]);

        AuditLogger::log('OTP_DIKIRIM', "OTP dikirim ke {$request->email} untuk NIM {$request->nim}.", $request->nim, 'voter');

        return redirect()->route('daftar.otp.form')
            ->with('success', 'Kode OTP telah diproses dan dikirimkan ke email ' . $request->email . '. Silakan periksa Kotak Masuk atau folder Spam.');
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

        // Anti-Spam Resend Cooldown (60 detik) & Limit (Maksimal 3 OTP per 10 menit)
        $resendCooldownKey = 'reg-resend-cooldown:' . Str::lower($nim);
        if (RateLimiter::tooManyAttempts($resendCooldownKey, 1)) {
            $seconds = RateLimiter::availableIn($resendCooldownKey);
            return back()->with('error', "Harap tunggu {$seconds} detik sebelum meminta kirim ulang kode OTP.");
        }

        $nimOtpKey = 'reg-otp-nim:' . Str::lower($nim);
        if (RateLimiter::tooManyAttempts($nimOtpKey, 3)) {
            $seconds = RateLimiter::availableIn($nimOtpKey);
            return back()->with('error', "Batas permintaan OTP untuk NIM ini telah tercapai. Harap tunggu {$seconds} detik sebelum meminta kode baru.");
        }

        // Batas Kuota Harian (Daily Quota Ceiling) - 24 Jam
        $dailyNimKey = 'otp-daily-nim:' . Str::lower($nim);
        $dailyEmailKey = 'otp-daily-email:' . hash('sha256', Str::lower(trim($email)));
        $dailyIpKey = 'otp-daily-ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($dailyNimKey, 5)) {
            $seconds = RateLimiter::availableIn($dailyNimKey);
            $hours = max(1, (int) ceil($seconds / 3600));
            AuditLogger::log('OTP_DAILY_LIMIT_EXCEEDED', "Batas kuota harian OTP tercapai saat resend untuk NIM {$nim}.", $nim, 'system');
            return back()->with('error', "Batas kuota harian permintaan OTP untuk NIM {$nim} telah tercapai (maksimal 5x dalam 24 jam). Silakan coba lagi dalam {$hours} jam atau hubungi Helpdesk KPR.");
        }

        if (RateLimiter::tooManyAttempts($dailyEmailKey, 5)) {
            $seconds = RateLimiter::availableIn($dailyEmailKey);
            $hours = max(1, (int) ceil($seconds / 3600));
            AuditLogger::log('OTP_DAILY_LIMIT_EXCEEDED', "Batas kuota harian OTP tercapai saat resend untuk email {$email}.", $nim, 'system');
            return back()->with('error', "Batas kuota harian permintaan OTP untuk email ini telah tercapai (maksimal 5x dalam 24 jam). Silakan coba lagi dalam {$hours} jam.");
        }

        if (RateLimiter::tooManyAttempts($dailyIpKey, 15)) {
            $seconds = RateLimiter::availableIn($dailyIpKey);
            $hours = max(1, (int) ceil($seconds / 3600));
            AuditLogger::log('OTP_DAILY_LIMIT_EXCEEDED', "Batas kuota harian OTP tercapai saat resend untuk IP {$request->ip()}.", $nim, 'system');
            return back()->with('error', "Batas kuota harian permintaan OTP dari jaringan/IP ini telah tercapai (maksimal 15x dalam 24 jam).");
        }

        RateLimiter::hit($resendCooldownKey, 60);
        RateLimiter::hit($nimOtpKey, 600);
        RateLimiter::hit($dailyNimKey, 86400);
        RateLimiter::hit($dailyEmailKey, 86400);
        RateLimiter::hit($dailyIpKey, 86400);

        $otpModel = OtpCode::createForNim($nim, $email);

        \Illuminate\Support\Facades\Log::info("==========================================");
        \Illuminate\Support\Facades\Log::info("[RESEND OTP KPR POLBENG] NIM: {$nim} | OTP: {$otpModel->plain_otp} | Ke: {$email}");
        \Illuminate\Support\Facades\Log::info("==========================================");

        // Kirim email OTP ulang secara asinkron via Job Queue
        try {
            \App\Jobs\SendOtpEmailJob::dispatch($nim, $email, $otpModel->plain_otp);
        } catch (\Throwable $e) {
            $this->deliverOtpMail($nim, $email, $otpModel->plain_otp);
        }

        AuditLogger::log('OTP_DIKIRIM_ULANG', "OTP diproses kirim ulang ke {$email} untuk NIM {$nim}.", $nim, 'voter');

        return back()->with('success', 'Kode OTP baru telah diproses dan dikirimkan ulang ke email ' . $email . '. Silakan periksa Kotak Masuk atau folder Spam.');
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
            $fromName = env('MAIL_FROM_NAME', 'KPR PEMIRA POLITEKNIK NEGERI BENGKALIS');

            $emailHtml = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px; color: #334155;">
    <div style="max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 12px; padding: 30px; border: 1px solid #e2e8f0; text-align: center;">
        <h2 style="color: #0f172a; margin-bottom: 8px;">KPR PEMIRA POLITEKNIK NEGERI BENGKALIS</h2>
        <p style="color: #64748b; font-size: 14px; margin-top: 0;">Otentikasi Akun Pemilih Mahasiswa</p>
        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;">
        <p style="text-align: left; font-size: 15px;">Halo Mahasiswa dengan NIM <strong>{$nim}</strong>,</p>
        <p style="text-align: left; font-size: 14px; color: #475569;">Berikut adalah Kode OTP rahasia Anda untuk menyelesaikan pendaftaran akun pemilih:</p>
        <div style="margin: 25px 0; padding: 18px; background: #f1f5f9; border-radius: 8px; border: 2px dashed #cbd5e1;">
            <span style="font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #0284c7; font-family: monospace;">{$plainOtp}</span>
        </div>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
            Kode OTP ini berlaku selama <strong>5 menit</strong> dan hanya dapat digunakan 1 kali.<br>
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
                        'subject' => '[PEMIRA POLITEKNIK NEGERI BENGKALIS] Kode OTP Pendaftaran Pemilih',
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
                Mail::to($email)->sendNow(new OtpMail($plainOtp, $nim));
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
            AuditLogger::log('OTP_ATTEMPTS_EXCEEDED', "NIM $nim melebihi 3 percobaan OTP.", $nim, 'voter');
            return back()->with('error', 'Kode OTP hangus karena terlalu banyak percobaan salah (maksimal 3 kali). Silakan kembali dan minta kode OTP baru.');
        }

        if (! $otpCode->verify($request->otp)) {
            $fresh = $otpCode->fresh();
            $remaining = max(0, 3 - $fresh->percobaan);
            if ($remaining === 0) {
                AuditLogger::log('OTP_HANGUS', "Kode OTP untuk NIM $nim hangus setelah 3x salah.", $nim, 'voter');
                return back()->with('error', 'Kode OTP salah. Batas 3 kali percobaan telah tercapai dan kode OTP Anda telah hangus. Silakan kembali dan minta kode OTP baru.');
            }
            return back()->with('error', "Kode OTP salah. Sisa kesempatan: {$remaining}x.");
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
                'email_hash'         => Voter::hashBlindIndex($data['email']),
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

    /**
     * Reset data mahasiswa demo agar siap diuji pendaftaran kembali dari awal.
     */
    public function resetDemo(ResetDemoRequest $request): RedirectResponse
    {
        $nim = trim((string) $request->input('nim'));
        if ($nim && PemilihMaster::resetDummyRegistration($nim)) {
            return redirect()->route('voter.login', ['tab' => 'register'])
                ->with('success', "Data akun demo NIM {$nim} berhasil direset! Anda sekarang dapat menguji coba alur pendaftaran dari awal.");
        }

        return redirect()->route('voter.login', ['tab' => 'register'])
            ->with('error', 'Gagal mereset data demo atau NIM tidak terdaftar dalam daftar demo.');
    }
}
