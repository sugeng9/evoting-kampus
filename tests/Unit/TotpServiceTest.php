<?php

namespace Tests\Unit;

use App\Services\TotpService;
use PHPUnit\Framework\TestCase;

class TotpServiceTest extends TestCase
{
    /**
     * Uji RFC 4648 Base32 Encode & Decode
     */
    public function test_base32_encode_and_decode(): void
    {
        $testStrings = [
            '',
            'f',
            'fo',
            'foo',
            'foob',
            'fooba',
            'foobar',
            'Politeknik Negeri Bengkalis KPR 2026',
        ];

        foreach ($testStrings as $str) {
            $encoded = TotpService::base32Encode($str);
            $decoded = TotpService::base32Decode($encoded);
            $this->assertEquals($str, $decoded, "Gagal pada encode/decode untuk string: '{$str}'");
        }
    }

    /**
     * Uji TOTP RFC 6238 Generator & Verifier
     */
    public function test_totp_generation_and_verification(): void
    {
        $secret = TotpService::generateSecret(20);
        $this->assertNotEmpty($secret);
        $this->assertEquals(32, strlen($secret), 'Secret 20-byte harus menghasilkan 32 karakter Base32.');

        // Kode saat ini harus valid
        $currentCode = TotpService::generateCode($secret);
        $this->assertEquals(6, strlen($currentCode));
        $this->assertTrue(ctype_digit($currentCode));

        $this->assertTrue(TotpService::verify($secret, $currentCode));

        // Kode sembarang harus tidak valid
        $this->assertFalse(TotpService::verify($secret, '999999'));
    }

    /**
     * Uji Single-Use Recovery Code Burn
     */
    public function test_recovery_code_generation_and_consume(): void
    {
        $codes = TotpService::generateRecoveryCodes(8);
        $this->assertCount(8, $codes);

        $codeToUse = $codes[0];
        $remaining = TotpService::consumeRecoveryCode($codes, $codeToUse);

        $this->assertNotNull($remaining);
        $this->assertCount(7, $remaining);
        $this->assertNotContains($codeToUse, $remaining);

        // Konsumsi kedua kali harus null (hangus)
        $secondAttempt = TotpService::consumeRecoveryCode($remaining, $codeToUse);
        $this->assertNull($secondAttempt);
    }
}
