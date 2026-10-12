<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BallotBox;
use App\Services\SecurityLogScrubber;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MonitoringAndIncidentPreparednessTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Uji pembersihan kredensial sensitif secara rekursif oleh SecurityLogScrubber
     */
    public function test_security_log_scrubber_strips_sensitive_credentials(): void
    {
        $dirtyPayload = [
            'nim'                   => '12010001',
            'name'                  => 'Ahmad Syahadan',
            'email'                 => 'syahadan@polbeng.ac.id',
            'password'              => 'SuperSecretPassword123!',
            'password_confirmation' => 'SuperSecretPassword123!',
            'pin'                   => '123456',
            'otp'                   => '987654',
            'kode_otp'              => '654321',
            'token'                 => 'secret_ballot_token_xyz',
            'two_factor_secret'     => 'JBSWY3DPEHPK3PXP',
            'two_factor_recovery_codes' => [
                'code1' => 'REC-1111-2222',
                'code2' => 'REC-3333-4444',
            ],
            'bukti_ktm'             => 'ktm_scan.pdf',
            'tanggal_lahir'         => '2003-08-17',
            'meta'                  => [
                'current_password'  => 'OldPassword123!',
                'ip'                => '192.168.1.1',
            ],
        ];

        $cleaned = SecurityLogScrubber::scrubArray($dirtyPayload);

        // Kunci sensitif harus disensor menjadi [SCRUBBED]
        $this->assertSame('[SCRUBBED]', $cleaned['password']);
        $this->assertSame('[SCRUBBED]', $cleaned['password_confirmation']);
        $this->assertSame('[SCRUBBED]', $cleaned['pin']);
        $this->assertSame('[SCRUBBED]', $cleaned['otp']);
        $this->assertSame('[SCRUBBED]', $cleaned['kode_otp']);
        $this->assertSame('[SCRUBBED]', $cleaned['token']);
        $this->assertSame('[SCRUBBED]', $cleaned['two_factor_secret']);
        $this->assertSame('[SCRUBBED]', $cleaned['two_factor_recovery_codes']);
        $this->assertSame('[SCRUBBED]', $cleaned['bukti_ktm']);
        $this->assertSame('[SCRUBBED]', $cleaned['tanggal_lahir']);
        $this->assertSame('[SCRUBBED]', $cleaned['meta']['current_password']);

        // Kunci non-sensitif harus tetap utuh untuk kebutuhan audit & debugging
        $this->assertSame('12010001', $cleaned['nim']);
        $this->assertSame('Ahmad Syahadan', $cleaned['name']);
        $this->assertSame('syahadan@polbeng.ac.id', $cleaned['email']);
        $this->assertSame('192.168.1.1', $cleaned['meta']['ip']);
    }

    /**
     * Uji pembersihan parameter sensitif dari string teks / URL query
     */
    public function test_security_log_scrubber_strips_string_parameters(): void
    {
        $queryString = 'url=/auth/login?nim=12010001&password=SecretPassWord!&pin=123456&otp=888999';
        $scrubbed = SecurityLogScrubber::scrubString($queryString);

        $this->assertStringNotContainsString('SecretPassWord!', $scrubbed);
        $this->assertStringNotContainsString('123456', $scrubbed);
        $this->assertStringNotContainsString('888999', $scrubbed);
        $this->assertStringContainsString('12010001', $scrubbed);
        $this->assertStringContainsString('password=[SCRUBBED]', $scrubbed);
        $this->assertStringContainsString('pin=[SCRUBBED]', $scrubbed);
        $this->assertStringContainsString('otp=[SCRUBBED]', $scrubbed);
    }

    /**
     * Uji pembersihan data request context untuk pelaporan error ke Sentry / Bug Tracker
     */
    public function test_security_log_scrubber_request_context_sanitization(): void
    {
        $request = Request::create('/pemilih/login', 'POST', [
            'nim'      => '12010001',
            'pin'      => '999888',
            'password' => 'RahasiaMahasiswa123!',
        ]);

        $safeContext = SecurityLogScrubber::scrubRequest($request);

        $this->assertSame('POST', $safeContext['method']);
        $this->assertSame('12010001', $safeContext['inputs']['nim']);
        $this->assertSame('[SCRUBBED]', $safeContext['inputs']['pin']);
        $this->assertSame('[SCRUBBED]', $safeContext['inputs']['password']);
    }

    /**
     * Uji prinsip kerahasiaan suara (Secret Ballot Separation):
     * Tabel ballot_boxes dilarang keras menyimpan korelasi langsung ke identitas pemilih (voter_id).
     */
    public function test_secret_ballot_isolation_schema_guarantees_voter_anonymity(): void
    {
        $columns = Schema::getColumnListing('ballot_boxes');

        // Tabel kotak suara TIDAK BOLEH memuat kolom identitas pemilih
        $this->assertNotContains('voter_id', $columns, 'PELANGGARAN KERAHASIAAN SUARA: ballot_boxes memuat voter_id!');
        $this->assertNotContains('nim', $columns, 'PELANGGARAN KERAHASIAAN SUARA: ballot_boxes memuat nim!');
        $this->assertNotContains('email', $columns, 'PELANGGARAN KERAHASIAAN SUARA: ballot_boxes memuat email!');
        $this->assertNotContains('name', $columns, 'PELANGGARAN KERAHASIAAN SUARA: ballot_boxes memuat nama pemilih!');

        // Kolom yang diizinkan hanya data agregat pemungutan suara
        $this->assertContains('election_category_id', $columns);
        $this->assertContains('candidate_id', $columns);
        $this->assertContains('ballot_hash', $columns);
    }

    /**
     * Uji konfigurasi monitoring Sentry: PII otomatis dimatikan dan before_send terpasang
     */
    public function test_sentry_configuration_adheres_to_privacy_guidelines(): void
    {
        $sentryConfig = config('sentry');

        $this->assertIsArray($sentryConfig);
        $this->assertFalse($sentryConfig['send_default_pii'], 'send_default_pii harus false demi kepatuhan UU PDP');
        $this->assertIsCallable($sentryConfig['before_send'], 'before_send scrubber callback harus terdefinisi');
    }

    /**
     * Uji ketersediaan dan integritas dokumen SOP Tanggap Darurat & Kebijakan Privasi UU PDP
     */
    public function test_incident_response_sop_and_privacy_policy_documents_exist(): void
    {
        $base = base_path();

        // 1. INCIDENT_RESPONSE.md
        $incidentDoc = $base . '/INCIDENT_RESPONSE.md';
        $this->assertFileExists($incidentDoc);
        $incidentContent = file_get_contents($incidentDoc);

        $this->assertStringContainsString('Tanggap Darurat', $incidentContent);
        $this->assertStringContainsString('KPR', $incidentContent);
        $this->assertStringContainsString('PanWas', $incidentContent);
        $this->assertStringContainsString('DDoS', $incidentContent);
        $this->assertStringContainsString('Brute Force', $incidentContent);
        $this->assertStringContainsString('audit:verifikasi', $incidentContent);
        $this->assertStringContainsString('Disaster Recovery', $incidentContent);

        // 2. PRIVASI_DATA.md
        $privacyDoc = $base . '/PRIVASI_DATA.md';
        $this->assertFileExists($privacyDoc);
        $privacyContent = file_get_contents($privacyDoc);

        $this->assertStringContainsString('UU No. 27 Tahun 2022', $privacyContent);
        $this->assertStringContainsString('Perlindungan Data Pribadi', $privacyContent);
        $this->assertStringContainsString('Secret Ballot', $privacyContent);
        $this->assertStringContainsString('ballot_boxes', $privacyContent);
        $this->assertStringContainsString('voter_participations', $privacyContent);
        $this->assertStringContainsString('sengketa:purge-ktm', $privacyContent);
        $this->assertStringContainsString('Politeknik Negeri Bengkalis', $privacyContent);
    }
}
