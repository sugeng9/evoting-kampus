<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\OtpCode;
use App\Models\PemilihMaster;
use App\Models\SystemPhase;
use App\Models\Voter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class AntiEnumerationAndOtpTest extends TestCase
{
    use DatabaseTransactions;

    protected Voter $registeredVoter;
    protected string $voterPassword = 'ValidPassword123!';

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Matikan Turnstile bot-check selama unit test
        config(['services.turnstile.enabled' => false]);

        // 2. Pastikan skema tabel voter up to date
        Voter::ensureSchema();

        // 3. Pastikan fase sistem mengizinkan login & pendaftaran
        SystemPhase::updateOrCreate(
            ['id' => 1],
            [
                'fase' => 'pendaftaran',
                'pendaftaran_buka' => true,
                'login_buka' => true,
                'sengketa_buka' => true,
            ]
        );

        // 4. Siapkan Akun Mahasiswa Terdaftar
        $this->registeredVoter = Voter::updateOrCreate(
            ['nim' => '120140999'],
            [
                'name' => 'Mahasiswa Terdaftar Test',
                'study_program' => 'Teknik Informatika',
                'faculty' => 'Politeknik Negeri Bengkalis',
                'email' => 'mahasiswa.test@polbeng.ac.id',
                'email_hash' => Voter::hashBlindIndex('mahasiswa.test@polbeng.ac.id'),
                'password' => Hash::make($this->voterPassword),
                'pendaftaran_status' => 'aktif',
                'registered_at' => now(),
                'is_active' => true,
                'academic_status' => 'aktif',
                'dpt_status' => 'DPT',
            ]
        );

        // 5. Bersihkan throttle keys
        RateLimiter::clear('voter-login:120140999|127.0.0.1');
        RateLimiter::clear('voter-login:999888777|127.0.0.1');
        RateLimiter::clear('voter-login-ip:127.0.0.1');
    }

    /**
     * UJI 1: Anti-Enumeration - Pesan Error & Field Identik antara NIM Terdaftar vs Tidak Terdaftar
     */
    public function test_login_error_message_and_field_are_identical_for_existing_and_non_existing_nim(): void
    {
        // 1. Percobaan login dengan NIM terdaftar tapi Password SALAH
        $responseRegistered = $this->from(route('voter.login'))->post(route('voter.login.submit'), [
            'nim' => $this->registeredVoter->nim,
            'pin' => 'WrongPassword999!',
        ]);

        $responseRegistered->assertRedirect(route('voter.login'));
        $responseRegistered->assertSessionHasErrors(['nim']);
        $errorMsgRegistered = session('errors')->first('nim');

        // Bersihkan throttle khusus untuk mengisolasi perbandingan pesan
        RateLimiter::clear('voter-login:' . Str::lower($this->registeredVoter->nim) . '|127.0.0.1');
        RateLimiter::clear('voter-login-ip:127.0.0.1');

        // 2. Percobaan login dengan NIM yang SAMA SEKALI TIDAK TERDAFTAR
        $nonExistentNim = '999888777';
        $responseNonExistent = $this->from(route('voter.login'))->post(route('voter.login.submit'), [
            'nim' => $nonExistentNim,
            'pin' => 'WrongPassword999!',
        ]);

        $responseNonExistent->assertRedirect(route('voter.login'));
        $responseNonExistent->assertSessionHasErrors(['nim']);
        $errorMsgNonExistent = session('errors')->first('nim');

        // Verifikasi 1: Pesan error 100% IDENTIK (Anti-User Enumeration)
        $this->assertSame(
            $errorMsgRegistered,
            $errorMsgNonExistent,
            'Pesan error untuk NIM terdaftar dan tidak terdaftar WAJIB identik agar penyerang tidak bisa mencacah NIM.'
        );

        // Verifikasi 2: Pesan tidak membocorkan keberadaan data di DPT
        $this->assertStringNotContainsString('tidak terdaftar di DPT', $errorMsgNonExistent);
        $this->assertStringContainsString('NIM atau kata sandi/PIN tidak cocok atau belum terdaftar.', $errorMsgNonExistent);

        // Verifikasi 3: Log audit internal tetap mencatat perbedaan peristiwa untuk audit forensik PanWas/KPR
        $this->assertTrue(
            AuditLog::where('event_type', 'LOGIN_FAILED_INVALID_PIN')->exists(),
            'Audit log internal harus mencatat kegagalan password.'
        );
        $this->assertTrue(
            AuditLog::where('event_type', 'LOGIN_FAILED_NIM_NOT_FOUND')->exists(),
            'Audit log internal harus mencatat kegagalan NIM tidak ditemukan.'
        );
    }

    /**
     * UJI 2: Proteksi Brute Force - Penguncian Akun Setelah 3 Kali Percobaan Gagal
     */
    public function test_login_rate_limiting_locks_out_after_three_failed_attempts(): void
    {
        $testNim = '120140999';

        // 3 kali percobaan gagal
        for ($i = 1; $i <= 3; $i++) {
            $this->from(route('voter.login'))->post(route('voter.login.submit'), [
                'nim' => $testNim,
                'pin' => 'Salah' . $i,
            ]);
        }

        // Percobaan ke-4: Harus terblokir oleh RateLimiter
        $response = $this->from(route('voter.login'))->post(route('voter.login.submit'), [
            'nim' => $testNim,
            'pin' => 'SalahLagi',
        ]);

        $response->assertRedirect(route('voter.login'));
        $response->assertSessionHasErrors(['nim']);
        $errorMsg = session('errors')->first('nim');

        $this->assertStringContainsString('dikunci sementara', $errorMsg);
    }

    /**
     * UJI 3: Anti-Bombing OTP - Batas Kuota Harian (Daily Quota Ceiling) per NIM (Maks 5x / 24 Jam)
     */
    public function test_registration_otp_daily_quota_ceiling_per_nim(): void
    {
        $testNim = '120140888';
        $dob = '2004-05-20';

        // Siapkan data master BAAK
        PemilihMaster::updateOrCreate(
            ['nim' => $testNim],
            [
                'nama' => 'Mahasiswa Test Quota NIM',
                'prodi' => 'Teknik Informatika',
                'angkatan' => '2022',
                'tanggal_lahir_hash' => hash('sha256', $dob),
            ]
        );

        $dailyNimKey = 'otp-daily-nim:' . Str::lower($testNim);
        RateLimiter::clear($dailyNimKey);

        // Simulasikan 5x permintaan OTP dalam 24 jam
        for ($i = 1; $i <= 5; $i++) {
            RateLimiter::hit($dailyNimKey, 86400);
        }

        // Request ke-6 via web controller: Harus diblokir oleh Daily Quota Ceiling
        $response = $this->from(route('voter.login', ['tab' => 'register']))->post(route('daftar.otp.send'), [
            'nim' => $testNim,
            'tanggal_lahir' => $dob,
            'email' => "quota_test_{$testNim}@polbeng.ac.id",
            'password' => 'PasswordAman123!',
            'password_confirmation' => 'PasswordAman123!',
        ]);

        $response->assertRedirect(route('voter.login', ['tab' => 'register']));
        $this->assertTrue(session()->has('error'));
        $this->assertStringContainsString('Batas kuota harian permintaan OTP untuk NIM', session('error'));
    }

    /**
     * UJI 4: Anti-Bombing OTP - Batas Kuota Harian (Daily Quota Ceiling) per Email (Maks 5x / 24 Jam)
     */
    public function test_registration_otp_daily_quota_ceiling_per_email(): void
    {
        $testNim = '120140777';
        $dob = '2004-08-15';
        $targetEmail = 'spam_target@example.com';

        PemilihMaster::updateOrCreate(
            ['nim' => $testNim],
            [
                'nama' => 'Mahasiswa Test Quota Email',
                'prodi' => 'Teknik Informatika',
                'angkatan' => '2022',
                'tanggal_lahir_hash' => hash('sha256', $dob),
            ]
        );

        $dailyEmailKey = 'otp-daily-email:' . hash('sha256', Str::lower(trim($targetEmail)));
        RateLimiter::clear($dailyEmailKey);

        // Simulasikan 5x pengiriman ke email tersebut
        for ($i = 1; $i <= 5; $i++) {
            RateLimiter::hit($dailyEmailKey, 86400);
        }

        // Request ke-6: Harus diblokir demi melindungi email dari spam bombing
        $response = $this->from(route('voter.login', ['tab' => 'register']))->post(route('daftar.otp.send'), [
            'nim' => $testNim,
            'tanggal_lahir' => $dob,
            'email' => $targetEmail,
            'password' => 'PasswordAman123!',
            'password_confirmation' => 'PasswordAman123!',
        ]);

        $response->assertRedirect(route('voter.login', ['tab' => 'register']));
        $this->assertTrue(session()->has('error'));
        $this->assertStringContainsString('Batas kuota harian permintaan OTP untuk alamat email ini', session('error'));
    }

    /**
     * UJI 5: Anti-Bombing OTP - Batas Kuota Harian (Daily Quota Ceiling) per IP (Maks 15x / 24 Jam)
     */
    public function test_registration_otp_daily_quota_ceiling_per_ip(): void
    {
        $testNim = '120140666';
        $dob = '2004-10-10';

        PemilihMaster::updateOrCreate(
            ['nim' => $testNim],
            [
                'nama' => 'Mahasiswa Test Quota IP',
                'prodi' => 'Teknik Informatika',
                'angkatan' => '2022',
                'tanggal_lahir_hash' => hash('sha256', $dob),
            ]
        );

        $clientIp = '127.0.0.1';
        $dailyIpKey = 'otp-daily-ip:' . $clientIp;
        RateLimiter::clear($dailyIpKey);

        // Simulasikan 15x request dari IP yang sama
        for ($i = 1; $i <= 15; $i++) {
            RateLimiter::hit($dailyIpKey, 86400);
        }

        // Request ke-16: Harus diblokir oleh Daily IP Ceiling
        $response = $this->from(route('voter.login', ['tab' => 'register']))->post(route('daftar.otp.send'), [
            'nim' => $testNim,
            'tanggal_lahir' => $dob,
            'email' => 'unique_mail_test@polbeng.ac.id',
            'password' => 'PasswordAman123!',
            'password_confirmation' => 'PasswordAman123!',
        ]);

        $response->assertRedirect(route('voter.login', ['tab' => 'register']));
        $this->assertTrue(session()->has('error'));
        $this->assertStringContainsString('Batas kuota harian permintaan OTP dari jaringan/IP', session('error'));
    }

    /**
     * UJI 6: OTP Invalidation - Hangus Otomatis Setelah 3 Kali Percobaan Salah
     */
    public function test_otp_invalidation_after_three_wrong_attempts(): void
    {
        $nim = '120140555';
        $email = 'otp_invalidation_test@polbeng.ac.id';

        $otp = OtpCode::createForNim($nim, $email);
        $correctOtp = $otp->plain_otp;

        $this->assertFalse($otp->isExpired());
        $this->assertFalse($otp->attemptsExceeded());

        // 3 kali tebakan salah
        $this->assertFalse($otp->verify('000000')); // Percobaan 1
        $this->assertFalse($otp->verify('111111')); // Percobaan 2
        $this->assertFalse($otp->verify('222222')); // Percobaan 3

        $freshOtp = $otp->fresh();
        $this->assertTrue($freshOtp->attemptsExceeded(), 'Percobaan harus tercatat >= 3.');
        $this->assertTrue($freshOtp->isExpired(), 'OTP harus otomatis hangus (expired) setelah 3x salah.');

        // Coba masukkan OTP yang sebenarnya benar setelah hangus -> wajib tetap ditolak!
        $this->assertFalse($freshOtp->verify($correctOtp), 'OTP yang sudah hangus tidak boleh dapat diverifikasi kembali.');
    }

    /**
     * UJI 7: Cek Status NIM - Pesan Generik untuk Mencegah Enumerasi Mahasiswa
     */
    public function test_nim_status_check_uses_generic_error_for_invalid_nim_or_dob(): void
    {
        $validNim = '120140444';
        $validDob = '2004-12-12';

        PemilihMaster::updateOrCreate(
            ['nim' => $validNim],
            [
                'nama' => 'Mahasiswa Test Cek Status',
                'prodi' => 'Teknik Informatika',
                'angkatan' => '2022',
                'tanggal_lahir_hash' => hash('sha256', $validDob),
            ]
        );

        // Kasus A: NIM tidak ada
        $respA = $this->from(route('nim.status.form'))->post(route('nim.status.check'), [
            'nim' => '999999999',
            'tanggal_lahir' => $validDob,
        ]);
        $respA->assertRedirect(route('nim.status.form'));
        $respA->assertSessionHas('error', 'Data tidak ditemukan atau tidak cocok.');

        // Kasus B: NIM ada, tapi tanggal lahir salah
        $respB = $this->from(route('nim.status.form'))->post(route('nim.status.check'), [
            'nim' => $validNim,
            'tanggal_lahir' => '1999-01-01',
        ]);
        $respB->assertRedirect(route('nim.status.form'));
        $respB->assertSessionHas('error', 'Data tidak ditemukan atau tidak cocok.');

        // Kasus C: Keduanya benar -> Berhasil menampilkan hasil status
        $respC = $this->post(route('nim.status.check'), [
            'nim' => $validNim,
            'tanggal_lahir' => $validDob,
        ]);
        $respC->assertOk();
        $respC->assertViewIs('auth.nim_status.result');
    }
}
