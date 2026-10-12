<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Auth\VoterAuthController;
use App\Http\Controllers\PublicAuditController;
use App\Http\Controllers\QuickCountController;
use App\Http\Controllers\VotingController;
use App\Http\Middleware\EnsureVoterAuthenticated;
use App\Http\Middleware\EnsureVotingIsOpen;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - E-Voting Kampus Aman & Anti-Kecurangan
|--------------------------------------------------------------------------
*/

// Konfigurasi Path Login Dinamis dari .env (Mitigasi Bot & Scanner / Security Hardening)
$voterLoginPath = trim((string) config('auth.login_path', env('LOGIN_PATH', 'auth-pemira')), '/');
if (empty($voterLoginPath)) {
    $voterLoginPath = 'auth-pemira';
}

$adminLoginPath = trim((string) config('auth.admin_login_path', env('ADMIN_LOGIN_PATH', 'admin/login')), '/');
if (empty($adminLoginPath)) {
    $adminLoginPath = 'admin/login';
}

// Redirect root ke rute login yang sedang aktif
Route::get('/', function () {
    return redirect()->route('voter.login');
});

// 1. RUTE AUTENTIKASI PEMILIH (MAHASISWA) - URL RAHASIA SESUAI .env (LOGIN_PATH)
Route::controller(VoterAuthController::class)->group(function () use ($voterLoginPath) {
    Route::get('/' . $voterLoginPath, 'showLoginForm')->name('login');
    Route::get('/' . $voterLoginPath, 'showLoginForm')->name('voter.login');
    Route::post('/' . $voterLoginPath, 'login')->middleware('throttle:6,1')->name('voter.login.submit');
    Route::match(['get', 'post'], '/logout', 'logout')->name('voter.logout');
});

// Matikan rute /login dan /voter/login default jika LOGIN_PATH telah disembunyikan (HTTP 404 Not Found)
if ($voterLoginPath !== 'login') {
    Route::any('/login', function () {
        abort(404, 'Halaman login default telah dinonaktifkan demi alasan keamanan.');
    });
}
if ($voterLoginPath !== 'voter/login') {
    Route::any('/voter/login', function () {
        abort(404, 'Halaman login default telah dinonaktifkan demi alasan keamanan.');
    });
}

use App\Http\Middleware\EnsureSingleVoterSession;

// 2. RUTE BILIK SUARA (PROTECTED BY VOTER GUARD)
Route::middleware([EnsureVoterAuthenticated::class, EnsureSingleVoterSession::class])->prefix('voter')->name('voter.')->group(function () {
    Route::get('/dashboard', [VotingController::class, 'dashboard'])->name('dashboard');

    // Pemungutan suara dilindungi oleh EnsureVotingIsOpen & Rate Limiting
    Route::middleware([EnsureVotingIsOpen::class])->group(function () {
        Route::get('/ballot/{categoryId}', [VotingController::class, 'showBallot'])->name('ballot');
        Route::post('/ballot/submit', [VotingController::class, 'submitVote'])
            ->middleware('throttle:15,1')
            ->name('ballot.submit');
    });

    Route::get('/receipt', [VotingController::class, 'showReceipt'])->name('receipt');
});

// 3. RUTE PUBLIK: QUICK COUNT & VERIFIKASI INDEPENDEN
Route::get('/quick-count', [QuickCountController::class, 'index'])->name('quick_count');
Route::get('/api/quick-count/live', [QuickCountController::class, 'getLiveData'])
    ->middleware('throttle:120,1')
    ->name('api.quick_count.live');

Route::get('/verify-ballot', [PublicAuditController::class, 'showVerificationPage'])->name('verify_ballot');
Route::post('/verify-ballot', [PublicAuditController::class, 'verifyBallot'])
    ->middleware('throttle:60,1')
    ->name('verify_ballot.submit');

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ElectionReportController;
use App\Http\Controllers\Admin\ParticipationRadarController;
use App\Http\Controllers\Admin\TwoFactorAuthController;
use App\Http\Controllers\Admin\VoterController;

// 4. RUTE ADMINISTRATOR / PANITIA KPR (KOMISI PEMILIHAN RAYA) & PANWAS
Route::controller(AdminAuthController::class)->group(function () use ($adminLoginPath) {
    Route::get('/' . $adminLoginPath, 'showLoginForm')->name('admin.login');
    Route::post('/' . $adminLoginPath, 'login')->middleware('throttle:5,1')->name('admin.login.submit');
    Route::match(['get', 'post'], '/admin/logout', 'logout')->name('admin.logout');
});

if ($adminLoginPath !== 'admin/login') {
    Route::any('/admin/login', function () {
        abort(404, 'Halaman login admin default telah dinonaktifkan demi alasan keamanan.');
    });
}

// 4.1 RUTE 2FA GOOGLE AUTHENTICATOR (TOTP)
Route::middleware(['auth:web'])->prefix('admin/2fa')->name('admin.2fa.')->group(function () {
    Route::get('/setup', [TwoFactorAuthController::class, 'showSetup'])->name('setup');
    Route::post('/confirm', [TwoFactorAuthController::class, 'confirmSetup'])->name('confirm');
    Route::get('/challenge', [TwoFactorAuthController::class, 'showChallenge'])->name('challenge');
    Route::post('/verify', [TwoFactorAuthController::class, 'verifyChallenge'])->name('verify');
});

// 4.2 PANEL ADMIN UTAMA (PROTECTED BY 2FA, TIMEOUT, IP ALLOWLIST, RBAC)
Route::middleware(['auth:web', 'admin.timeout', 'admin.ip_allowlist', 'admin.2fa'])->prefix('admin')->name('admin.')->group(function () {
    // Rute Tinjauan & Pelaporan (Dapat Diakses Super Admin, Panitia, dan Auditor)
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/quick-count', [AdminDashboardController::class, 'quickCount'])->name('quick_count');
    Route::get('/audit-logs', [AdminDashboardController::class, 'auditLogs'])->name('audit_logs');
    
    // Laporan Hasil Tetap & Berita Acara Rekapitulasi Pemira (Siap Cetak PDF)
    Route::get('/reports', [ElectionReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/category/{id}', [ElectionReportController::class, 'showCategoryReport'])->name('reports.category');
    Route::get('/reports/all', [ElectionReportController::class, 'showAllReports'])->name('reports.all');
    
    // Radar Partisipasi & Mobilisasi Pemilih Lapangan
    Route::get('/participation-radar', [ParticipationRadarController::class, 'index'])->name('radar.index');
    Route::get('/participation-radar/export-unvoted', [ParticipationRadarController::class, 'exportUnvoted'])->name('radar.export_unvoted');
    Route::get('/participation-radar/export-summary', [ParticipationRadarController::class, 'exportSummary'])->name('radar.export_summary');

    // Saringan DPS Kampus & Verifikasi Status Akademik (View)
    Route::get('voters/dps-verification', [VoterController::class, 'dpsVerification'])->name('voters.dps');
    Route::get('voters/export-tokens', [VoterController::class, 'exportTokens'])->name('voters.export_tokens');
    Route::get('voters/export-ormawa-dpt', [VoterController::class, 'exportOrmawaDpt'])->name('voters.export_ormawa');
    Route::get('voters/import', [VoterController::class, 'import'])->name('voters.import');
    Route::get('voters/template', [VoterController::class, 'downloadTemplate'])->name('voters.template');
    Route::get('voters/export', [VoterController::class, 'exportDpt'])->name('voters.export');
    Route::get('voters/download-dummy', [VoterController::class, 'downloadDummyExcel'])->name('voters.download_dummy');
    Route::get('voters', [VoterController::class, 'index'])->name('voters.index');
    Route::get('voters/{voter}', [VoterController::class, 'show'])->name('voters.show');

    // Reset Password Pemilih: Diizinkan untuk Super Admin, Panitia, dan Petugas Helpdesk
    Route::post('voters/{id}/reset-password', [VoterController::class, 'resetPassword'])
        ->middleware('role:super_admin,panitia,helpdesk')
        ->name('voters.reset_password');

    // RUTE OPERASIONAL PEMIRA: HANYA SUPER ADMIN & PANITIA (AUDITOR & HELPDESK DITOLAK)
    Route::middleware(['role:super_admin,panitia'])->group(function () {
        Route::post('/session/status', [AdminDashboardController::class, 'updateSessionStatus'])->name('session.status');
        Route::post('/quick-count/toggle', [AdminDashboardController::class, 'toggleQuickCount'])->name('quick_count.toggle');
        Route::put('/categories/{id}/schedule', [AdminDashboardController::class, 'updateCategorySchedule'])->name('categories.schedule');
        
        Route::post('/reports/close/{id}', [ElectionReportController::class, 'closeCategoryElection'])->name('reports.close_category');
        Route::post('/reports/close-all', [ElectionReportController::class, 'closeAllElections'])->name('reports.close_all');
        Route::post('/reports/reopen/{id}', [ElectionReportController::class, 'reopenCategoryElection'])->name('reports.reopen_category');
        Route::post('/reports/set-end-time/{id}', [ElectionReportController::class, 'updateEndTime'])->name('reports.set_end_time');
        Route::post('/reports/set-all-end-time', [ElectionReportController::class, 'setAllEndTime'])->name('reports.set_all_end_time');

        // Manajemen Kategori Organisasi Mahasiswa
        Route::post('categories/seed-preset', [CategoryController::class, 'seedTenOrganizations'])->name('categories.seed_preset');
        Route::post('categories/{id}/toggle', [CategoryController::class, 'toggleStatus'])->name('categories.toggle');
        Route::resource('categories', CategoryController::class)->except(['index', 'show']);

        // Manajemen Paslon (Ketua & Wakil)
        Route::resource('candidates', CandidateController::class)->except(['index', 'show']);

        // Operasi DPT & Token Generator
        Route::post('voters/dps-simulation', [VoterController::class, 'seedDpsSimulation'])->name('voters.dps_simulation');
        Route::post('voters/dps-import', [VoterController::class, 'processDpsImport'])->name('voters.dps_import');
        Route::post('voters/{id}/generate-token', [VoterController::class, 'generateToken'])->name('voters.generate_token');
        Route::post('voters/bulk-generate-tokens', [VoterController::class, 'bulkGenerateTokens'])->name('voters.bulk_tokens');
        Route::post('voters/import', [VoterController::class, 'processImport'])->name('voters.import.process');
        Route::post('voters/generate-dummy', [VoterController::class, 'generateDummyData'])->name('voters.generate_dummy');
        Route::post('voters/clear-dummy', [VoterController::class, 'clearDummyData'])->name('voters.clear_dummy');
        Route::post('voters/{id}/toggle', [VoterController::class, 'toggleStatus'])->name('voters.toggle');
        Route::delete('voters/{voter}', [VoterController::class, 'destroy'])->name('voters.destroy');
    });

    // Resource Categories & Candidates Read endpoints (index, show)
    Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('candidates', [CandidateController::class, 'index'])->name('candidates.index');
    Route::get('candidates/{candidate}', [CandidateController::class, 'show'])->name('candidates.show');
});

// ===== AUTENTIKASI PENDAFTARAN PEMILIH MANDIRI =====

use App\Http\Controllers\Auth\PemilihRegistrationController;
use App\Http\Controllers\Auth\NimStatusController;
use App\Http\Controllers\Auth\PemilihLoginController;
use App\Http\Controllers\Admin\HelpdeskController;
use App\Http\Controllers\Admin\FaseAdminController;
use App\Http\Middleware\EnsureFasePendaftaran;
use App\Http\Middleware\EnsureHelpdeskRole;

// PENDAFTARAN & VERIFIKASI (Anti-Spam Bot & Anti-Flooding)
$otpSendThrottle = 'throttle:5,1';
$otpVerifyThrottle = 'throttle:6,1';
$checkStatusThrottle = 'throttle:15,1';
$sengketaThrottle = 'throttle:5,1';

Route::prefix('daftar')->name('daftar.')->group(function () use ($otpSendThrottle, $otpVerifyThrottle) {
    Route::get('/', [PemilihRegistrationController::class, 'showForm'])->name('form');
    Route::post('/otp', [PemilihRegistrationController::class, 'sendOtp'])->middleware($otpSendThrottle)->name('otp.send');
    Route::get('/otp', function () {
        return redirect()->route('voter.login', ['tab' => 'register']);
    });
    Route::get('/otp/verifikasi', [PemilihRegistrationController::class, 'showOtpForm'])->name('otp.form');
    Route::post('/otp/verifikasi', [PemilihRegistrationController::class, 'verifyOtpAndRegister'])->middleware($otpVerifyThrottle)->name('otp.verify');
    Route::post('/otp/resend', [PemilihRegistrationController::class, 'resendOtp'])->middleware($otpSendThrottle)->name('otp.resend');
    Route::post('/reset-demo', [PemilihRegistrationController::class, 'resetDemo'])->name('reset_demo');
    Route::get('/sukses', [PemilihRegistrationController::class, 'sukses'])->name('sukses');
});

// CEK STATUS NIM
Route::prefix('nim-status')->name('nim.')->group(function () use ($checkStatusThrottle, $sengketaThrottle) {
    Route::get('/', [NimStatusController::class, 'showForm'])->name('status.form');
    Route::post('/', [NimStatusController::class, 'checkStatus'])->middleware($checkStatusThrottle)->name('status.check');
    Route::get('/sengketa', [NimStatusController::class, 'showSengketaForm'])->name('sengketa.form');
    Route::post('/sengketa', [NimStatusController::class, 'submitSengketa'])->middleware($sengketaThrottle)->name('sengketa.submit');
});

// PUSAT BANTUAN & HELPDESK PEMILIH (HANYA DI HALAMAN LUAR)
Route::get('/bantuan', function () {
    return redirect()->route('voter.login', ['helpdesk' => 'open']);
})->name('helpdesk.pemilih');

// HELPDESK PANEL
Route::middleware(['auth:web', 'admin.timeout', 'admin.ip_allowlist', 'admin.2fa', EnsureHelpdeskRole::class])->prefix('helpdesk')->name('helpdesk.')->group(function () {
    Route::get('/', [HelpdeskController::class, 'index'])->name('index');
    Route::get('/sengketa/{sengketa}', [HelpdeskController::class, 'show'])->name('sengketa.show');
    Route::get('/sengketa/{sengketa}/ktm', [HelpdeskController::class, 'downloadKtm'])->name('sengketa.ktm');
    Route::post('/sengketa/{sengketa}/proses', [HelpdeskController::class, 'proses'])->name('sengketa.proses');
    Route::post('/sengketa/{sengketa}/setujui-cabut', [HelpdeskController::class, 'setujuiCabut'])->name('sengketa.setujui');
    Route::post('/sengketa/{sengketa}/cabut-akun', [HelpdeskController::class, 'cabutAkun'])->name('sengketa.cabut');
    Route::post('/sengketa/{sengketa}/tolak', [HelpdeskController::class, 'tolakSengketa'])->name('sengketa.tolak');
    Route::post('/sengketa/{sengketa}/kode-daftar', [HelpdeskController::class, 'terbitkanKodePendaftaran'])->name('sengketa.kode');
});

// ADMIN FASE PANEL (super admin only)
Route::middleware(['auth:web', 'admin.timeout', 'admin.ip_allowlist', 'admin.2fa', 'role:super_admin'])->prefix('admin/fase')->name('admin.fase.')->group(function () {
    Route::get('/', [FaseAdminController::class, 'showFasePanel'])->name('panel');
    Route::post('/ubah', [FaseAdminController::class, 'ubahFase'])->name('ubah');
    Route::post('/aktifkan-pending', [FaseAdminController::class, 'aktifkanSemuaPending'])->name('aktifkan');
    Route::post('/import-master', [FaseAdminController::class, 'importMasterCsv'])->name('import');
});

// LOGIN PEMILIH DENGAN PASSWORD (NIM + PASSWORD)
Route::prefix('pemilih')->name('pemilih.')->group(function () {
    Route::get('/login', [PemilihLoginController::class, 'showLoginForm'])->name('login.form');
    Route::post('/login', [PemilihLoginController::class, 'login'])->middleware('throttle:10,1')->name('login');
    Route::match(['get', 'post'], '/logout', [PemilihLoginController::class, 'logout'])->name('logout');
});

// ROUTE DIAGNOSTIK TEST EMAIL SMTP (Bisa diakses untuk tes kirim)
Route::get('/test-email', function () {
    $to = request('to', env('MAIL_FROM_ADDRESS', 'syahadanduri@gmail.com'));
    try {
        \Illuminate\Support\Facades\Mail::raw("Halo! Ini adalah tes pengiriman email dari Sistem Pemira Politeknik Negeri Bengkalis pada " . now()->setTimezone('Asia/Jakarta')->toDateTimeString() . " WIB.\n\nJika Anda membaca pesan ini, berarti koneksi Gmail SMTP Anda sudah 100% AKTIF dan siap melayani pendaftaran seluruh 30+ penguji/mahasiswa!", function ($msg) use ($to) {
            $msg->to($to)->subject('[PEMIRA POLITEKNIK NEGERI BENGKALIS] Tes Koneksi Email Berhasil!');
        });
        return response("<div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 25px; background: #ecfdf5; color: #065f46; border: 2px solid #10b981; border-radius: 12px; text-align: center;'>
            <h2 style='color: #047857; margin-top: 0;'>🎉 SUKSES! Email Berhasil Terkirim</h2>
            <p style='font-size: 15px;'>Email pengujian berhasil dikirim ke: <strong>{$to}</strong></p>
            <p style='font-size: 13px; color: #065f46;'>Silakan periksa kotak masuk atau spam di email tersebut. Sekarang sistem siap mengirimkan OTP ke seluruh 30+ mahasiswa secara otomatis!</p>
        </div>");
    } catch (\Throwable $e) {
        return response("<div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 25px; background: #fff1f2; color: #9f1239; border: 2px solid #f43f5e; border-radius: 12px;'>
            <h2 style='color: #e11d48; margin-top: 0;'>❌ GAGAL Mengirim Email</h2>
            <p style='font-size: 14px;'><strong>Penyebab:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <hr style='border: none; border-top: 1px solid #fecdd3; margin: 15px 0;'>
            <p style='font-size: 13px;'>Pastikan alamat email di <code>MAIL_USERNAME</code> pada file <code>.env</code> cocok dengan akun Google tempat membuat sandi aplikasi 16 karakter.</p>
        </div>", 500);
    }
});

// ROUTE PEMICU SEEDER DELEGASI SOSIALISASI (Akses via browser untuk memuat akun siap coblos)
Route::get('/seed-sosialisasi', function () {
    try {
        (new \Database\Seeders\DelegasiSosialisasiSeeder())->run();

        $voters = \App\Models\Voter::where('nim', 'like', '1201%0001')
            ->orWhere('nim', 'like', '1201%0002')
            ->orderBy('study_program', 'asc')
            ->orderBy('nim', 'asc')
            ->get();

        $rowsHtml = '';
        foreach ($voters as $idx => $v) {
            $isAklamasiProdi = in_array($v->study_program, ['Bahasa', 'Kemaritiman']);
            $scenarioBadge = $isAklamasiProdi 
                ? "<span style='background:#fef3c7; color:#92400e; padding:4px 8px; border-radius:6px; font-weight:bold; font-size:11px;'>🏆 Aklamasi (2 Bilik: BEM, MPM + Info HMJ)</span>"
                : "<span style='background:#d1fae5; color:#065f46; padding:4px 8px; border-radius:6px; font-weight:bold; font-size:11px;'>🟢 Kompetisi (3 Bilik: BEM, MPM, HMJ)</span>";

            $rowsHtml .= "<tr style='border-bottom:1px solid #e2e8f0; font-size:13px;'>
                <td style='padding:12px; text-align:center; font-weight:bold;'>" . ($idx + 1) . "</td>
                <td style='padding:12px; font-weight:bold; color:#0f172a;'>{$v->study_program}</td>
                <td style='padding:12px;'><code style='background:#f1f5f9; padding:4px 8px; border-radius:6px; font-weight:bold; color:#1e293b;'>{$v->nim}</code></td>
                <td style='padding:12px; color:#334155;'>{$v->name}</td>
                <td style='padding:12px;'><code style='background:#ecfdf5; color:#047857; padding:4px 8px; border-radius:6px; font-weight:bold;'>123456</code></td>
                <td style='padding:12px;'>{$scenarioBadge}</td>
                <td style='padding:12px; text-align:center;'>
                    <a href='/login?quick_nim={$v->nim}' style='background:#059669; color:white; padding:6px 12px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:12px;'>Login &rarr;</a>
                </td>
            </tr>";
        }

        return response("<!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>Akun Delegasi Sosialisasi Pemira Politeknik Negeri Bengkalis</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; margin: 0; padding: 30px; color: #1e293b; }
                .card { max-width: 1000px; margin: 0 auto; background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; overflow: hidden; }
                .header { background: linear-gradient(135deg, #0f172a, #1e293b); color: white; padding: 24px 30px; }
                table { width: 100%; border-collapse: collapse; text-align: left; }
                th { background: #f8fafc; padding: 12px; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: bold; border-bottom: 2px solid #e2e8f0; }
            </style>
        </head>
        <body>
            <div class='card'>
                <div class='header'>
                    <div style='display:inline-block; background:rgba(16,185,129,0.2); color:#6ee7b7; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:bold; margin-bottom:8px;'>
                        🎉 SEEDER BERHASIL DIJALANKAN
                    </div>
                    <h1 style='margin:0; font-size:22px; font-weight:900;'>Akun Delegasi Sosialisasi Siap Coblos (8 Jurusan Politeknik Negeri Bengkalis)</h1>
                    <p style='margin:6px 0 0 0; font-size:13px; color:#94a3b8;'>Seluruh akun berstatus DPT Aktif dengan PIN: <strong>123456</strong>. Delegasi dapat langsung menggunakannya untuk demonstrasi bilik suara.</p>
                </div>
                <div style='padding:20px;'>
                    <table>
                        <thead>
                            <tr>
                                <th style='text-align:center;'>No</th>
                                <th>Jurusan / Prodi</th>
                                <th>NIM (Username)</th>
                                <th>Nama Mahasiswa</th>
                                <th>PIN / Password</th>
                                <th>Skenario Bilik Suara</th>
                                <th style='text-align:center;'>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$rowsHtml}
                        </tbody>
                    </table>
                </div>
                <div style='background:#f8fafc; padding:16px 30px; border-top:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#64748b;'>
                    <span>Gunakan NIM di atas dan PIN <strong>123456</strong> di halaman <a href='/login' style='color:#059669; font-weight:bold;'>Login Pemilih</a>.</span>
                    <a href='/admin/voters' style='color:#0f172a; font-weight:bold; text-decoration:none;'>&larr; Buka Admin Panel</a>
                </div>
            </div>
        </body>
        </html>");
    } catch (\Throwable $e) {
        return response("<div style='font-family:sans-serif; max-width:600px; margin:40px auto; padding:20px; background:#fff1f2; color:#9f1239; border-radius:12px; border:2px solid #f43f5e;'>
            <h3>Gagal memuat data seeder</h3>
            <p>" . htmlspecialchars($e->getMessage()) . "</p>
        </div>", 500);
    }
});
