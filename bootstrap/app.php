<?php

use App\Http\Middleware\AdminIpAllowlistMiddleware;
use App\Http\Middleware\AdminSessionTimeoutMiddleware;
use App\Http\Middleware\EnsureTwoFactorAuthenticated;
use App\Http\Middleware\EnsureUserRole;
use App\Http\Middleware\EnsureVoterAuthenticated;
use App\Http\Middleware\EnsureVotingIsOpen;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Pastikan seluruh zona waktu aplikasi menggunakan Waktu Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Percayai Reverse Proxy & Tunnel (Cloudflare Tunnel, Ngrok, Nginx)
        $middleware->trustProxies(at: '*');

        // Daftarkan Security Headers secara global
        $middleware->append(SecurityHeadersMiddleware::class);

        // Bebaskan rute logout dari CSRF agar tidak pernah memicu error 419 saat sesi kedaluwarsa
        $middleware->validateCsrfTokens(except: [
            'admin/logout',
            'voter/logout',
            'pemilih/logout',
            'logout',
        ]);

        // Alias Middleware untuk Routing
        $middleware->alias([
            'voter.auth' => EnsureVoterAuthenticated::class,
            'voting.open' => EnsureVotingIsOpen::class,
            'admin.2fa' => EnsureTwoFactorAuthenticated::class,
            'admin.timeout' => AdminSessionTimeoutMiddleware::class,
            'admin.ip_allowlist' => AdminIpAllowlistMiddleware::class,
            'role' => EnsureUserRole::class,
        ]);

        // Arahkan tamu yang belum login ke portal yang tepat
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin*') || $request->is('helpdesk*')) {
                return route('admin.login');
            }
            if (\Illuminate\Support\Facades\Route::has('login')) {
                return route('login');
            }
            return route('voter.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Sensor otomatis parameter sensitif agar tidak tersimpan di session flash
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
            'pin',
            'otp',
            'kode_otp',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_code',
            'token',
            'bukti_ktm',
            'tanggal_lahir',
        ]);

        // Sanitasi context pada pelaporan error / exception
        $exceptions->report(function (\Throwable $e) {
            if (app()->bound('sentry') && !empty(env('SENTRY_LARAVEL_DSN'))) {
                try {
                    $safeContext = \App\Services\SecurityLogScrubber::scrubRequest(request());
                    \Sentry\withScope(function (\Sentry\State\Scope $scope) use ($safeContext, $e): void {
                        $scope->setContext('safe_request', $safeContext);
                        \Sentry\captureException($e);
                    });
                } catch (\Throwable $ignored) {}
            }
        });

        // Tangani TokenMismatchException & HttpException 419 (Page Expired) secara elegan dan ramah pengguna
        $handleTokenMismatch = function ($request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi formulir telah berakhir (Page Expired). Silakan refresh halaman dan coba kembali.',
                ], 419);
            }

            if ($request->is('admin*')) {
                \Illuminate\Support\Facades\Auth::guard('web')->logout();
                return redirect()->route('admin.login')->with('warning', 'Sesi panitia Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali.');
            }

            if ($request->is('daftar*')) {
                return redirect()->route('voter.login', ['tab' => 'register'])->with('warning', 'Sesi formulir pendaftaran telah kedaluwarsa demi keamanan. Silakan ulangi pengisian.');
            }

            if (\Illuminate\Support\Facades\Auth::guard('voter')->check()) {
                return redirect()->route('voter.dashboard')->with('warning', 'Sesi Anda telah diperbarui. Silakan lanjutkan pemilihan.');
            }

            \Illuminate\Support\Facades\Auth::guard('voter')->logout();
            return redirect()->route('voter.login')->with('warning', 'Sesi Anda telah berakhir karena tidak aktif. Silakan masuk kembali ke bilik suara.');
        };

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) use ($handleTokenMismatch) {
            return $handleTokenMismatch($request);
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) use ($handleTokenMismatch) {
            if ($e->getStatusCode() === 419) {
                return $handleTokenMismatch($request);
            }
        });

        // Tangani jika Database MySQL (XAMPP / Laragon) belum dinyalakan
        $exceptions->render(function (\Illuminate\Database\QueryException $e, $request) {
            $msg = $e->getMessage();
            if (str_contains($msg, '2002') || str_contains($msg, 'refused') || str_contains($msg, 'actively refused') || str_contains($msg, 'timed out')) {
                return response()->view('errors.database', ['error' => $msg], 500);
            }
        });

        $exceptions->render(function (\PDOException $e, $request) {
            $msg = $e->getMessage();
            if (str_contains($msg, '2002') || str_contains($msg, 'refused') || str_contains($msg, 'actively refused') || str_contains($msg, 'timed out')) {
                return response()->view('errors.database', ['error' => $msg], 500);
            }
        });
    })->create();
