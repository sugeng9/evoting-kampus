<?php

namespace App\Services;

use Illuminate\Support\Str;

class TotpService
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate Secret Key Base32 Acak (160-bit / 20 byte standar Google Authenticator)
     */
    public static function generateSecret(int $byteCount = 20): string
    {
        $randomBytes = random_bytes($byteCount);
        return self::base32Encode($randomBytes);
    }

    /**
     * Encode binary string ke format Base32 (RFC 4648)
     */
    public static function base32Encode(string $binary): string
    {
        if ($binary === '') {
            return '';
        }

        $binaryString = '';
        for ($i = 0; $i < strlen($binary); $i++) {
            $binaryString .= sprintf('%08b', ord($binary[$i]));
        }

        $chunks = str_split($binaryString, 5);
        $b32 = '';
        foreach ($chunks as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $b32 .= self::BASE32_ALPHABET[bindec($chunk)];
        }

        return $b32;
    }

    /**
     * Decode Base32 string ke raw binary
     */
    public static function base32Decode(string $b32): string
    {
        $b32 = strtoupper(preg_replace('/[^A-Z2-7]/', '', $b32));
        if ($b32 === '') {
            return '';
        }

        $binaryString = '';
        for ($i = 0; $i < strlen($b32); $i++) {
            $val = strpos(self::BASE32_ALPHABET, $b32[$i]);
            if ($val === false) {
                continue;
            }
            $binaryString .= sprintf('%05b', $val);
        }

        $bytes = '';
        $chunks = str_split($binaryString, 8);
        foreach ($chunks as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }

        return $bytes;
    }

    /**
     * Hitung Kode TOTP 6-Digit Berdasarkan Timestamp (RFC 6238)
     */
    public static function generateCode(string $secret, ?int $timestamp = null, int $digits = 6, int $period = 30): string
    {
        $timestamp = $timestamp ?? time();
        $timeSlice = (int) floor($timestamp / $period);

        // Pack time slice ke dalam 8-byte big-endian binary
        $binaryTime = pack('N*', 0) . pack('N*', $timeSlice);
        $secretBinary = self::base32Decode($secret);

        // Hitung HMAC-SHA1
        $hash = hash_hmac('sha1', $binaryTime, $secretBinary, true);

        // Dynamic Truncation
        $offset = ord(substr($hash, -1)) & 0x0F;
        $truncatedHash = substr($hash, $offset, 4);
        $value = unpack('N', $truncatedHash)[1] & 0x7FFFFFFF;

        $modulo = pow(10, $digits);
        return str_pad((string) ($value % $modulo), $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Alias praktis untuk generateCode
     */
    public static function generateTotp(string $secret, ?int $timestamp = null): string
    {
        return self::generateCode($secret, $timestamp);
    }

    /**
     * Verifikasi Kode TOTP dengan Toleransi Waktu Drift (Default: ±30 detik)
     */
    public static function verify(string $secret, string $code, int $window = 1, int $period = 30): bool
    {
        $cleanCode = trim(str_replace(' ', '', $code));
        if (strlen($cleanCode) !== 6 || !ctype_digit($cleanCode)) {
            return false;
        }

        $currentTime = time();
        for ($i = -$window; $i <= $window; $i++) {
            $ts = $currentTime + ($i * $period);
            if (hash_equals(self::generateCode($secret, $ts, 6, $period), $cleanCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Buat URI Standar otpauth:// untuk Pemindaian Google Authenticator / Authy
     */
    public static function getOtpAuthUri(string $issuer, string $accountName, string $secret): string
    {
        $label = rawurlencode($issuer) . ':' . rawurlencode($accountName);
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'digits' => 6,
            'period' => 30,
            'algorithm' => 'SHA1',
        ]);

        return "otpauth://totp/{$label}?{$params}";
    }

    /**
     * Generate 8 Kode Pemulihan Sekali Pakai (Recovery Codes)
     * Format: 8 karakter alfanumerik huruf kapital dengan pemisah tanda hubung (misal: A1B2-C3D4)
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $part1 = strtoupper(Str::random(4));
            $part2 = strtoupper(Str::random(4));
            $codes[] = "{$part1}-{$part2}";
        }
        return $codes;
    }

    /**
     * Verifikasi dan Konsumsi Kode Pemulihan (Single-Use Consume)
     * Mengembalikan daftar sisa kode yang belum dipakai, atau null jika kode tidak cocok.
     */
    public static function consumeRecoveryCode(array $availableCodes, string $inputCode): ?array
    {
        $normalizedInput = strtoupper(trim(str_replace('-', '', $inputCode)));

        foreach ($availableCodes as $index => $code) {
            $normalizedStored = strtoupper(trim(str_replace('-', '', $code)));
            if (hash_equals($normalizedStored, $normalizedInput)) {
                // Hanguskan kode yang baru dipakai
                unset($availableCodes[$index]);
                return array_values($availableCodes);
            }
        }

        return null;
    }
}
