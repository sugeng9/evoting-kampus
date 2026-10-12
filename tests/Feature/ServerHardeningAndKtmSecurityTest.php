<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\PemilihMaster;
use App\Models\Sengketa;
use App\Models\SystemPhase;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServerHardeningAndKtmSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected User $helpdeskUser;
    protected string $testNim;
    protected string $testDob;

    protected function setUp(): void
    {
        parent::setUp();

        // Pastikan kolom bukti_ktm_path ada di tabel sengketa
        if (!Schema::hasColumn('sengketa', 'bukti_ktm_path')) {
            Schema::table('sengketa', function (Blueprint $table) {
                $table->string('bukti_ktm_path', 255)->nullable()->after('ktm_diverifikasi');
            });
        }

        User::ensureSchema();

        config(['auth.admin_ip_allowlist' => '*']);
        putenv('ADMIN_IP_ALLOWLIST=*');
        $_ENV['ADMIN_IP_ALLOWLIST'] = '*';

        Storage::fake('local');

        // Pastikan fase mengizinkan sengketa (misal fase 'pendaftaran' atau 'sengketa')
        $phase = SystemPhase::first();
        if ($phase) {
            $phase->update(['fase' => 'pendaftaran']);
        } else {
            SystemPhase::create(['fase' => 'pendaftaran', 'deskripsi' => 'Pendaftaran']);
        }

        // Siapkan user helpdesk untuk tes otorisasi download (2FA terkonfirmasi)
        $this->helpdeskUser = User::create([
            'name'                    => 'Petugas Helpdesk Pengujian',
            'username'                => 'helpdesk_' . uniqid(),
            'email'                   => 'helpdesk_' . uniqid() . '@polbeng.ac.id',
            'password'                => Hash::make('PasswordHelpdesk123!'),
            'role'                    => 'helpdesk',
            'is_active'               => true,
            'two_factor_secret'       => TotpService::generateSecret(20),
            'two_factor_confirmed_at' => now(),
        ]);

        // Siapkan DPT master untuk validasi form sengketa
        $this->testNim = '9999' . rand(1000, 9999);
        $this->testDob = '2003-08-17';

        PemilihMaster::create([
            'nim'                 => $this->testNim,
            'nama'                => 'Mahasiswa Uji Sengketa',
            'jurusan'             => 'Teknik Informatika',
            'prodi'               => 'D4 Rekayasa Perangkat Lokik',
            'tanggal_lahir_hash'  => hash('sha256', $this->testDob),
        ]);
    }

    /**
     * Uji validasi berkas KTM: ekstensi berbahaya / tidak diizinkan (.php, .exe, .sh) & ukuran > 3MB ditolak.
     */
    public function test_ktm_upload_rejects_disallowed_extension_and_size(): void
    {
        // 1. Ekstensi berbahaya (.php / .exe)
        $dangerousFile = UploadedFile::fake()->create('exploit.php', 100, 'application/x-php');

        $response = $this->post(route('nim.sengketa.submit'), [
            'nim'             => $this->testNim,
            'tanggal_lahir'   => $this->testDob,
            'pelapor_kontak'  => '081234567890',
            'alasan'          => 'NIM saya terdaftar oleh orang lain dan saya butuh pemulihan akun.',
            'bukti_ktm'       => $dangerousFile,
        ]);

        $response->assertSessionHasErrors('bukti_ktm');
        $this->assertDatabaseMissing('sengketa', ['nim' => $this->testNim]);

        // 2. Ukuran melebihi 3072 KB (misal 5MB)
        $oversizedFile = UploadedFile::fake()->create('ktm_huge.jpg', 5000, 'image/jpeg');

        $response2 = $this->post(route('nim.sengketa.submit'), [
            'nim'             => $this->testNim,
            'tanggal_lahir'   => $this->testDob,
            'pelapor_kontak'  => '081234567890',
            'alasan'          => 'NIM saya terdaftar oleh orang lain dan saya butuh pemulihan akun.',
            'bukti_ktm'       => $oversizedFile,
        ]);

        $response2->assertSessionHasErrors('bukti_ktm');
    }

    /**
     * Uji upload berkas KTM yang valid disimpan di direktori privat dengan UUID v4 tanpa membeberkan NIM di nama berkas.
     */
    public function test_ktm_upload_stores_in_private_storage_with_uuid(): void
    {
        $validFile = UploadedFile::fake()->image('scan_ktm_asli.jpg', 800, 600)->size(500);

        $response = $this->post(route('nim.sengketa.submit'), [
            'nim'             => $this->testNim,
            'tanggal_lahir'   => $this->testDob,
            'pelapor_kontak'  => '081234567890',
            'alasan'          => 'NIM saya terdaftar oleh orang lain dan saya butuh pemulihan akun.',
            'bukti_ktm'       => $validFile,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $sengketa = Sengketa::where('nim', $this->testNim)->latest()->first();
        $this->assertNotNull($sengketa);
        $this->assertNotNull($sengketa->bukti_ktm_path);

        // Path harus berformat private_ktm/{uuid}.jpg
        $this->assertStringStartsWith('private_ktm/', $sengketa->bukti_ktm_path);
        $this->assertStringEndsWith('.jpg', $sengketa->bukti_ktm_path);

        // Nama file di disk TIDAK boleh memuat NIM mahasiswa asli (UU PDP data minimization)
        $filename = basename($sengketa->bukti_ktm_path);
        $this->assertStringNotContainsString($this->testNim, $filename);

        // File benar-benar ada di local disk (bukan public)
        Storage::disk('local')->assertExists($sengketa->bukti_ktm_path);
        Storage::disk('public')->assertMissing($sengketa->bukti_ktm_path);
    }

    /**
     * Tamu (unauthenticated) diarahkan ke login admin saat mencoba mengunduh berkas KTM privat.
     */
    public function test_unauthenticated_guest_cannot_download_private_ktm(): void
    {
        $dummyPath = 'private_ktm/' . Str::uuid() . '.jpg';
        Storage::disk('local')->put($dummyPath, 'fake-binary-image-content');

        $sengketa = Sengketa::create([
            'nim'              => $this->testNim,
            'alasan'           => 'Pencurian identitas pemira kampus.',
            'pelapor_kontak'   => '081234567890',
            'bukti_ktm_path'   => $dummyPath,
            'ktm_diverifikasi' => true,
            'status'           => 'baru',
        ]);

        // Tamu tanpa login diarahkan ke login admin
        $response = $this->get(route('helpdesk.sengketa.ktm', $sengketa->id));
        $response->assertRedirect(route('admin.login'));
    }

    /**
     * Admin yang belum lolos 2FA dilarang mengunduh berkas KTM dan diarahkan ke tantangan/setup 2FA.
     */
    public function test_admin_without_2fa_cannot_download_private_ktm(): void
    {
        $dummyPath = 'private_ktm/' . Str::uuid() . '.png';
        Storage::disk('local')->put($dummyPath, 'fake-png-content');

        $sengketa = Sengketa::create([
            'nim'              => $this->testNim,
            'alasan'           => 'Pencurian identitas pemira kampus.',
            'pelapor_kontak'   => '081234567890',
            'bukti_ktm_path'   => $dummyPath,
            'ktm_diverifikasi' => true,
            'status'           => 'baru',
        ]);

        // 1. User sudah setup 2FA, tetapi sesi belum menyelesaikan tantangan kode TOTP
        $response = $this->actingAs($this->helpdeskUser, 'web')
            ->get(route('helpdesk.sengketa.ktm', $sengketa->id));

        $response->assertRedirect(route('admin.2fa.challenge'));

        // 2. User baru yang belum setup 2FA sama sekali
        $unconfiguredUser = User::create([
            'name'                    => 'Petugas Baru Belum 2FA',
            'username'                => 'helpdesk_no2fa_' . uniqid(),
            'email'                   => 'helpdesk_no2fa_' . uniqid() . '@polbeng.ac.id',
            'password'                => Hash::make('PasswordHelpdesk123!'),
            'role'                    => 'helpdesk',
            'is_active'               => true,
            'two_factor_secret'       => null,
            'two_factor_confirmed_at' => null,
        ]);

        $responseB = $this->actingAs($unconfiguredUser, 'web')
            ->get(route('helpdesk.sengketa.ktm', $sengketa->id));

        $responseB->assertRedirect(route('admin.2fa.setup'));
    }

    /**
     * Admin berwenang yang telah lolos verifikasi 2FA dapat mengakses berkas KTM dengan header keamanan yang ketat.
     */
    public function test_authorized_admin_with_2fa_can_download_private_ktm(): void
    {
        $dummyContent = 'sample-ktm-pdf-binary-stream';
        $dummyPath = 'private_ktm/' . Str::uuid() . '.pdf';
        Storage::disk('local')->put($dummyPath, $dummyContent);

        $sengketa = Sengketa::create([
            'nim'              => $this->testNim,
            'alasan'           => 'Pencurian identitas pemira kampus.',
            'pelapor_kontak'   => '081234567890',
            'bukti_ktm_path'   => $dummyPath,
            'ktm_diverifikasi' => true,
            'status'           => 'baru',
        ]);

        $response = $this->actingAs($this->helpdeskUser, 'web')
            ->withSession([
                'admin_2fa_passed'         => true,
                'admin_last_activity_time' => time(),
            ])
            ->get(route('helpdesk.sengketa.ktm', $sengketa->id));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));

        // Pastikan akses log audit mencatat aktivitas melihat KTM
        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'KTM_ACCESSED',
        ]);
    }

    /**
     * Uji Artisan command sengketa:purge-ktm menghapus berkas KTM yang sengketanya telah selesai/ditolak.
     */
    public function test_purge_resolved_sengketa_evidence_artisan_command(): void
    {
        $dummyPathOld = 'private_ktm/' . Str::uuid() . '.jpg';
        $dummyPathRecent = 'private_ktm/' . Str::uuid() . '.jpg';

        Storage::disk('local')->put($dummyPathOld, 'content-old');
        Storage::disk('local')->put($dummyPathRecent, 'content-recent');

        // Sengketa A: Selesai 35 hari lalu (melebihi retensi 30 hari)
        $oldSengketa = Sengketa::create([
            'nim'              => '12010091',
            'alasan'           => 'Sengketa lampau.',
            'pelapor_kontak'   => '0811111111',
            'bukti_ktm_path'   => $dummyPathOld,
            'ktm_diverifikasi' => true,
            'status'           => 'selesai',
            'diselesaikan_at'  => now()->subDays(35),
            'updated_at'       => now()->subDays(35),
        ]);

        // Sengketa B: Selesai baru 5 hari lalu (masih dalam masa sanggah)
        $recentSengketa = Sengketa::create([
            'nim'              => '12010092',
            'alasan'           => 'Sengketa baru.',
            'pelapor_kontak'   => '0822222222',
            'bukti_ktm_path'   => $dummyPathRecent,
            'ktm_diverifikasi' => true,
            'status'           => 'selesai',
            'diselesaikan_at'  => now()->subDays(5),
            'updated_at'       => now()->subDays(5),
        ]);

        // Jalankan artisan purge dengan force
        $exitCode = Artisan::call('sengketa:purge-ktm', ['--days' => 30, '--force' => true]);
        $this->assertSame(0, $exitCode);

        // Berkas lama harus sudah dihapus dari disk dan path diset null
        Storage::disk('local')->assertMissing($dummyPathOld);
        $oldSengketa->refresh();
        $this->assertNull($oldSengketa->bukti_ktm_path);

        // Berkas baru tetap harus ada utuh
        Storage::disk('local')->assertExists($dummyPathRecent);
        $recentSengketa->refresh();
        $this->assertNotNull($recentSengketa->bukti_ktm_path);

        // Log audit pembersihan tercatat
        $this->assertDatabaseHas('audit_logs', [
            'aksi' => 'KTM_PURGED',
        ]);
    }

    /**
     * Uji keberadaan dan integritas file hardening (.htaccess, public/.htaccess, deploy/nginx/evoting-kampus.conf, SERVER_HARDENING.md).
     */
    public function test_hardening_configuration_and_documentation_artifacts(): void
    {
        $base = base_path();

        // 1. Root .htaccess
        $rootHtaccess = $base . '/.htaccess';
        $this->assertFileExists($rootHtaccess);
        $rootContent = file_get_contents($rootHtaccess);
        $this->assertStringContainsString('Options -Indexes', $rootContent);
        $this->assertStringContainsString('.env', $rootContent);

        // 2. Public .htaccess
        $publicHtaccess = public_path('.htaccess');
        $this->assertFileExists($publicHtaccess);
        $pubContent = file_get_contents($publicHtaccess);
        $this->assertStringContainsString('Options -Indexes', $pubContent);
        $this->assertStringContainsString('X-Content-Type-Options', $pubContent);
        $this->assertStringContainsString('X-Frame-Options', $pubContent);

        // 3. Nginx production config
        $nginxConf = $base . '/deploy/nginx/evoting-kampus.conf';
        $this->assertFileExists($nginxConf);
        $nginxContent = file_get_contents($nginxConf);
        $this->assertStringContainsString('pemira.polbeng.ac.id', $nginxContent);
        $this->assertStringContainsString('limit_req_zone', $nginxContent);
        $this->assertStringContainsString('fastcgi_buffer_size', $nginxContent);
        $this->assertStringContainsString('Strict-Transport-Security', $nginxContent);

        // 4. Server Hardening SOP
        $hardeningDoc = $base . '/SERVER_HARDENING.md';
        $this->assertFileExists($hardeningDoc);
        $docContent = file_get_contents($hardeningDoc);
        $this->assertStringContainsString('Ubuntu 22.04', $docContent);
        $this->assertStringContainsString('sysctl', $docContent);
        $this->assertStringContainsString('Fail2ban', $docContent);
        $this->assertStringContainsString('composer audit', $docContent);
    }
}
