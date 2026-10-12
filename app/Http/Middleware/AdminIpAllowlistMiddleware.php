<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIpAllowlistMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowlistRaw = config('auth.admin_ip_allowlist', env('ADMIN_IP_ALLOWLIST', ''));

        // Jika tidak diatur atau bernilai '*', allowlist tidak aktif (mode terbuka / fleksibel)
        if (empty(trim($allowlistRaw)) || trim($allowlistRaw) === '*') {
            return $next($request);
        }

        $clientIp = $request->ip();
        $allowedList = array_filter(array_map('trim', explode(',', $allowlistRaw)));

        $isAllowed = false;
        foreach ($allowedList as $allowed) {
            if ($allowed === $clientIp || $allowed === '*' || $this->ipMatches($clientIp, $allowed)) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            AuditLog::record(
                aksi: 'ADMIN_IP_BLOCKED',
                target: "route:{$request->path()}",
                payload: [
                    'client_ip' => $clientIp,
                    'attempted_url' => $request->fullUrl(),
                    'allowlist_configured' => $allowedList,
                ],
                aktor: "blocked_ip:{$clientIp}"
            );

            abort(403, "Akses Terbatas: Alamat IP Anda ({$clientIp}) tidak terdaftar dalam allowlist resmi workstation Panitia KPR.");
        }

        return $next($request);
    }

    /**
     * Cocokkan IP dengan subnet CIDR atau format wildcard
     */
    private function ipMatches(string $ip, string $range): bool
    {
        // Wildcard support: e.g. 192.168.1.*
        if (str_contains($range, '*')) {
            $pattern = '/^' . str_replace(['.', '*'], ['\.', '[0-9]+'], $range) . '$/';
            return (bool) preg_match($pattern, $ip);
        }

        // CIDR support: e.g. 192.168.1.0/24
        if (str_contains($range, '/')) {
            [$subnet, $bits] = explode('/', $range);
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            if ($ipLong === false || $subnetLong === false) {
                return false;
            }
            $mask = -1 << (32 - (int) $bits);
            $subnetLong &= $mask;
            return ($ipLong & $mask) === $subnetLong;
        }

        return false;
    }
}
