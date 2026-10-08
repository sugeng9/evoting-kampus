<?php

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
        ]);

        // Arahkan tamu yang belum login ke portal yang tepat
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('admin*')) {
                return route('admin.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Tangani TokenMismatchException (Error 419 / Page Expired) secara elegan dan ramah pengguna
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->is('admin*')) {
                \Illuminate\Support\Facades\Auth::guard('web')->logout();
                return redirect()->route('admin.login')->with('warning', 'Sesi panitia Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali.');
            }

            \Illuminate\Support\Facades\Auth::guard('voter')->logout();
            return redirect()->route('voter.login', ['tab' => 'register'])->with('warning', 'Sesi form Anda telah berakhir karena batas waktu tidak aktif. Silakan ulangi pendaftaran atau cek status.');
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
