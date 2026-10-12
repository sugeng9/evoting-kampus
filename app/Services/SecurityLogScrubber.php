<?php

namespace App\Services;

use Illuminate\Http\Request;

class SecurityLogScrubber
{
    /**
     * Daftar kunci sensitif yang WAJIB disensor dari log, exception, dan stack trace
     * demi perlindungan privasi mahasiswa dan kredensial keamanan.
     */
    protected static array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'pin',
        'pin_confirmation',
        'otp',
        'otp_code',
        'kode_otp',
        'token',
        'access_token',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_code',
        'secret',
        'api_key',
        'bukti_ktm',
        'tanggal_lahir',
        'authorization',
        'cookie',
    ];

    /**
     * Sensor array secara rekursif (misal data request, payload log, context exception)
     */
    public static function scrubArray(array $data): array
    {
        $scrubbed = [];

        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (self::isSensitiveKey($normalizedKey)) {
                $scrubbed[$key] = '[SCRUBBED]';
            } elseif (is_array($value)) {
                $scrubbed[$key] = self::scrubArray($value);
            } else {
                $scrubbed[$key] = $value;
            }
        }

        return $scrubbed;
    }

    /**
     * Sensor string teks jika memuat parameter sensitif (misal URL query string atau log line)
     */
    public static function scrubString(string $text): string
    {
        foreach (self::$sensitiveKeys as $key) {
            // Sensor format: key=value atau "key": "value"
            $text = preg_replace(
                '/(' . preg_quote($key, '/') . '\s*[=:]\s*["\']?)([^"\'&\s]+)(["\'&\s]?)/i',
                '$1[SCRUBBED]$3',
                $text
            );
        }

        return $text;
    }

    /**
     * Ambil data request yang telah disanitasi secara aman untuk context exception
     */
    public static function scrubRequest(?Request $request): array
    {
        if (!$request) {
            return [];
        }

        $allData = $request->all();
        $safeData = self::scrubArray($allData);

        return [
            'method'     => $request->method(),
            'url'        => $request->fullUrl(),
            'ip'         => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 200),
            'inputs'     => $safeData,
        ];
    }

    /**
     * Periksa apakah suatu kunci termasuk dalam daftar sensitif
     */
    protected static function isSensitiveKey(string $key): bool
    {
        foreach (self::$sensitiveKeys as $sensitive) {
            if ($key === $sensitive || str_contains($key, $sensitive)) {
                return true;
            }
        }
        return false;
    }
}
