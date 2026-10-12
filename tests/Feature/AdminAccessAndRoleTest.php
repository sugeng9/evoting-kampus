<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ElectionSetting;
use App\Models\User;
use App\Models\Voter;
use App\Policies\VoterPolicy;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccessAndRoleTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;
    protected User $panitia;
    protected User $helpdesk;
    protected User $auditor;
    protected string $originalIpAllowlist;

    protected function setUp(): void
    {
        parent::setUp();

        User::ensureSchema();

        $this->originalIpAllowlist = (string) config('auth.admin_ip_allowlist', getenv('ADMIN_IP_ALLOWLIST'));
        config(['auth.admin_ip_allowlist' => '*']);
        putenv('ADMIN_IP_ALLOWLIST=*');
        $_ENV['ADMIN_IP_ALLOWLIST'] = '*';

        // Buat user pengujian untuk setiap peran
        $this->superAdmin = User::create([
            'name' => 'Super Administrator Test',
            'username' => 'test_superadmin_' . uniqid(),
            'email' => 'superadmin_' . uniqid() . '@polbeng.ac.id',
            'password' => Hash::make('SecretAdmin123!'),
            'role' => 'super_admin',
            'is_active' => true,
            'two_factor_secret' => TotpService::generateSecret(20),
            'two_factor_recovery_codes' => TotpService::generateRecoveryCodes(8),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->panitia = User::create([
            'name' => 'Panitia Pemira Test',
            'username' => 'test_panitia_' . uniqid(),
            'email' => 'panitia_' . uniqid() . '@polbeng.ac.id',
            'password' => Hash::make('SecretPanitia123!'),
            'role' => 'panitia',
            'is_active' => true,
            'two_factor_secret' => TotpService::generateSecret(20),
            'two_factor_recovery_codes' => TotpService::generateRecoveryCodes(8),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->helpdesk = User::create([
            'name' => 'Petugas Helpdesk Test',
            'username' => 'test_helpdesk_' . uniqid(),
            'email' => 'helpdesk_' . uniqid() . '@polbeng.ac.id',
            'password' => Hash::make('SecretHelpdesk123!'),
            'role' => 'helpdesk',
            'is_active' => true,
            'two_factor_secret' => TotpService::generateSecret(20),
            'two_factor_recovery_codes' => TotpService::generateRecoveryCodes(8),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->auditor = User::create([
            'name' => 'Auditor Pengawas Test',
            'username' => 'test_auditor_' . uniqid(),
            'email' => 'auditor_' . uniqid() . '@polbeng.ac.id',
            'password' => Hash::make('SecretAuditor123!'),
            'role' => 'auditor',
            'is_active' => true,
            'two_factor_secret' => TotpService::generateSecret(20),
            'two_factor_recovery_codes' => TotpService::generateRecoveryCodes(8),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        config(['auth.admin_ip_allowlist' => $this->originalIpAllowlist]);
        putenv("ADMIN_IP_ALLOWLIST={$this->originalIpAllowlist}");
        $_ENV['ADMIN_IP_ALLOWLIST'] = $this->originalIpAllowlist;
        parent::tearDown();
    }

    /**
     * UJI 1: Bypass 2FA Ditolak - Admin yang belum mengaktifkan 2FA dialihkan ke form setup
     */
    public function test_admin_without_2fa_is_redirected_to_setup(): void
    {
        $unconfiguredAdmin = User::create([
            'name' => 'Admin Baru Belum 2FA',
            'username' => 'no_2fa_' . uniqid(),
            'email' => 'no2fa_' . uniqid() . '@polbeng.ac.id',
            'password' => Hash::make('Secret123!'),
            'role' => 'super_admin',
            'is_active' => true,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        $response = $this->actingAs($unconfiguredAdmin, 'web')
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.2fa.setup'));
    }

    /**
     * UJI 2: Bypass 2FA Ditolak - Admin dengan 2FA aktif tetapi belum lolos tantangan sesi
     */
    public function test_admin_with_unverified_session_is_redirected_to_challenge(): void
    {
        // Login tanpa mengisi session 'admin_2fa_passed' => true
        $response = $this->actingAs($this->superAdmin, 'web')
            ->withSession(['admin_2fa_passed' => false])
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.2fa.challenge'));
    }

    /**
     * UJI 3: Admin dengan 2FA Terverifikasi dapat mengakses Dashboard
     */
    public function test_admin_with_verified_2fa_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->superAdmin, 'web')
            ->withSession([
                'admin_2fa_passed' => true,
                'admin_last_activity' => time(),
            ])
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    /**
     * UJI 4: Verifikasi TOTP 6-Digit (Berhasil & Gagal)
     */
    public function test_2fa_totp_verification_success_and_failure(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP'; // Base32 test secret
        $this->superAdmin->two_factor_secret = $secret;
        $this->superAdmin->two_factor_confirmed_at = now();
        $this->superAdmin->save();

        $validTotp = TotpService::generateTotp($secret);

        // A. Verifikasi kode valid
        $response = $this->actingAs($this->superAdmin, 'web')
            ->withSession([
                'admin_2fa_passed' => false,
                'admin_2fa_user_id' => $this->superAdmin->id,
            ])
            ->post(route('admin.2fa.verify'), [
                'code' => $validTotp,
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session('admin_2fa_passed'));

        // B. Verifikasi kode salah
        $failResponse = $this->actingAs($this->superAdmin, 'web')
            ->withSession([
                'admin_2fa_passed' => false,
                'admin_2fa_user_id' => $this->superAdmin->id,
            ])
            ->from(route('admin.2fa.challenge'))
            ->post(route('admin.2fa.verify'), [
                'code' => '000000',
            ]);

        $failResponse->assertRedirect(route('admin.2fa.challenge'));
        $failResponse->assertSessionHasErrors('code');
    }

    /**
     * UJI 5: Recovery Code Sekali Pakai (Single-Use Burn)
     */
    public function test_2fa_recovery_code_burn_single_use(): void
    {
        $recoveryCodes = ['code111111', 'code222222', 'code333333'];
        $this->superAdmin->two_factor_recovery_codes = $recoveryCodes;
        $this->superAdmin->save();

        // 1. Gunakan recovery code pertama
        $response = $this->actingAs($this->superAdmin, 'web')
            ->withSession([
                'admin_2fa_passed' => false,
                'admin_2fa_user_id' => $this->superAdmin->id,
            ])
            ->post(route('admin.2fa.verify'), [
                'recovery_code' => 'code111111',
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session('admin_2fa_passed'));

        // Periksa bahwa code111111 telah hangus di database
        $freshCodes = (array) $this->superAdmin->fresh()->two_factor_recovery_codes;
        $this->assertCount(2, $freshCodes, 'Sisa kode pemulihan harus berkurang menjadi 2.');
        $this->assertNotContains('code111111', $freshCodes, 'Kode yang telah dipakai wajib dibakar (burn).');

        // 2. Coba pakai kembali kode yang sama -> harus ditolak
        $reuseResponse = $this->actingAs($this->superAdmin, 'web')
            ->withSession([
                'admin_2fa_passed' => false,
                'admin_2fa_user_id' => $this->superAdmin->id,
            ])
            ->from(route('admin.2fa.challenge'))
            ->post(route('admin.2fa.verify'), [
                'recovery_code' => 'code111111',
            ]);

        $reuseResponse->assertRedirect(route('admin.2fa.challenge'));
        $reuseResponse->assertSessionHasErrors('recovery_code');
    }

    /**
     * UJI 6: Inactivity Session Timeout - Otomatis Logout setelah 15 Menit Tanpa Aktivitas
     */
    public function test_session_timeout_auto_logouts_after_15_minutes(): void
    {
        // Simulasikan aktivitas terakhir 901 detik (15 menit 1 detik) yang lalu
        $response = $this->actingAs($this->superAdmin, 'web')
            ->withSession([
                'admin_2fa_passed' => true,
                'admin_last_activity' => time() - 901,
            ])
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $this->assertFalse(Auth::guard('web')->check(), 'Admin harus otomatis di-logout oleh timeout guard.');
    }

    /**
     * UJI 7: IP Allowlist untuk Panel Admin
     */
    public function test_admin_ip_allowlist_blocking(): void
    {
        config(['auth.admin_ip_allowlist' => '192.168.1.100,10.10.0.0/16']);
        putenv('ADMIN_IP_ALLOWLIST=192.168.1.100,10.10.0.0/16');
        $_ENV['ADMIN_IP_ALLOWLIST'] = '192.168.1.100,10.10.0.0/16';

        // A. Akses dari IP yang TIDAK terdaftar (e.g. 203.0.113.50) -> Harus 403 Forbidden
        $blockedResponse = $this->actingAs($this->superAdmin, 'web')
            ->withSession([
                'admin_2fa_passed' => true,
                'admin_last_activity' => time(),
            ])
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.50'])
            ->get(route('admin.dashboard'));

        $blockedResponse->assertStatus(403);

        // B. Akses dari IP yang terdaftar (192.168.1.100) -> Sukses 200 OK
        $allowedResponse = $this->actingAs($this->superAdmin, 'web')
            ->withSession([
                'admin_2fa_passed' => true,
                'admin_last_activity' => time(),
            ])
            ->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
            ->get(route('admin.dashboard'));

        $allowedResponse->assertStatus(200);
    }

    /**
     * UJI 8: Role Authorization (RBAC) & Auditor Read-Only Protection
     */
    public function test_role_authorization_and_auditor_read_only(): void
    {
        // 1. Auditor dapat mengakses GET laporan & audit-logs
        $auditorView = $this->actingAs($this->auditor, 'web')
            ->withSession([
                'admin_2fa_passed' => true,
                'admin_last_activity' => time(),
            ])
            ->get(route('admin.audit_logs'));

        $auditorView->assertStatus(200);

        // 2. Auditor DITOLAK saat mencoba operasi penulisan (POST session status) -> 403 Forbidden
        $auditorPost = $this->actingAs($this->auditor, 'web')
            ->withSession([
                'admin_2fa_passed' => true,
                'admin_last_activity' => time(),
            ])
            ->post(route('admin.session.status'), [
                'status' => 'OPEN',
            ]);

        $auditorPost->assertStatus(403);

        // 3. Helpdesk DITOLAK saat mencoba mengubah konfigurasi pemira / session status -> 403 Forbidden
        $helpdeskPost = $this->actingAs($this->helpdesk, 'web')
            ->withSession([
                'admin_2fa_passed' => true,
                'admin_last_activity' => time(),
            ])
            ->post(route('admin.session.status'), [
                'status' => 'OPEN',
            ]);

        $helpdeskPost->assertStatus(403);

        // 4. Panitia berhak mengubah session status (Operasional Pemira)
        $panitiaPost = $this->actingAs($this->panitia, 'web')
            ->withSession([
                'admin_2fa_passed' => true,
                'admin_last_activity' => time(),
            ])
            ->post(route('admin.session.status'), [
                'status' => 'OPEN',
            ]);

        $panitiaPost->assertRedirect();
    }

    /**
     * UJI 9: IDOR Protection pada Voter Policy
     */
    public function test_idor_protection_voter_cannot_access_or_vote_for_others(): void
    {
        $voter1 = Voter::create([
            'nim' => '999888771',
            'name' => 'Mahasiswa Pemilih 1',
            'study_program' => 'Teknik Informatika',
            'faculty' => 'Teknik Informatika',
            'academic_status' => 'aktif',
            'is_active' => true,
            'password' => Hash::make('123456'),
        ]);

        $voter2 = Voter::create([
            'nim' => '999888772',
            'name' => 'Mahasiswa Pemilih 2',
            'study_program' => 'Teknik Mesin',
            'faculty' => 'Teknik Mesin',
            'academic_status' => 'aktif',
            'is_active' => true,
            'password' => Hash::make('123456'),
        ]);

        $policy = new VoterPolicy();

        // 1. Voter 1 TIDAK BOLEH melihat data Voter 2 (Anti-IDOR)
        $this->assertFalse($policy->view($voter1, $voter2), 'Voter 1 tidak boleh melihat data Voter 2.');

        // 2. Voter 1 BOLEH melihat datanya sendiri
        $this->assertTrue($policy->view($voter1, $voter1), 'Voter 1 boleh melihat datanya sendiri.');

        // 3. Voter 1 TIDAK BOLEH mengubah data Voter 2 (Anti-IDOR)
        $this->assertFalse($policy->update($voter1, $voter2), 'Voter 1 tidak boleh memperbarui data Voter 2.');

        // 4. Voter 1 TIDAK BOLEH mencoblos atas nama Voter 2 (Anti-IDOR)
        $this->assertFalse($policy->vote($voter1, $voter2), 'Voter 1 tidak boleh menggunakan hak suara Voter 2.');

        // 5. Voter 1 BOLEH mencoblos atas namanya sendiri yang aktif
        $this->assertTrue($policy->vote($voter1, $voter1), 'Voter 1 boleh menggunakan hak suaranya sendiri.');

        // 6. Admin / Panitia / Auditor memiliki wewenang administratif melihat data pemilih
        $this->assertTrue($policy->view($this->superAdmin, $voter1));
        $this->assertTrue($policy->view($this->panitia, $voter1));
        $this->assertTrue($policy->view($this->auditor, $voter1));
    }
}
