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

// Redirect root ke halaman login atau dashboard
Route::get('/', function () {
    return redirect()->route('voter.login');
});

// 1. RUTE AUTENTIKASI PEMILIH (MAHASISWA)
Route::controller(VoterAuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::get('/voter/login', 'showLoginForm')->name('voter.login');
    Route::post('/login', 'login')->name('voter.login.submit');
    Route::match(['get', 'post'], '/logout', 'logout')->name('voter.logout');
});

use App\Http\Middleware\EnsureSingleVoterSession;

// 2. RUTE BILIK SUARA (PROTECTED BY VOTER GUARD)
Route::middleware([EnsureVoterAuthenticated::class, EnsureSingleVoterSession::class])->prefix('voter')->name('voter.')->group(function () {
    Route::get('/dashboard', [VotingController::class, 'dashboard'])->name('dashboard');

    // Pemungutan suara dilindungi oleh EnsureVotingIsOpen & Rate Limiting
    Route::middleware([EnsureVotingIsOpen::class])->group(function () {
        Route::get('/ballot/{categoryId}', [VotingController::class, 'showBallot'])->name('ballot');
        Route::post('/ballot/submit', [VotingController::class, 'submitVote'])
            ->middleware('throttle:30,1')
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
use App\Http\Controllers\Admin\VoterController;

// 4. RUTE ADMINISTRATOR / PANITIA KPU KAMPUS
Route::controller(AdminAuthController::class)->prefix('admin')->group(function () {
    Route::get('/login', 'showLoginForm')->name('admin.login');
    Route::post('/login', 'login')->name('admin.login.submit');
    Route::match(['get', 'post'], '/logout', 'logout')->name('admin.logout');
});

Route::middleware(['auth:web'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/quick-count', [AdminDashboardController::class, 'quickCount'])->name('quick_count');
    Route::post('/session/status', [AdminDashboardController::class, 'updateSessionStatus'])->name('session.status');
    Route::post('/quick-count/toggle', [AdminDashboardController::class, 'toggleQuickCount'])->name('quick_count.toggle');
    Route::put('/categories/{id}/schedule', [AdminDashboardController::class, 'updateCategorySchedule'])->name('categories.schedule');
    Route::get('/audit-logs', [AdminDashboardController::class, 'auditLogs'])->name('audit_logs');
    
    // Laporan Hasil Tetap & Berita Acara Rekapitulasi Pemira (Siap Cetak PDF)
    Route::get('/reports', [ElectionReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/category/{id}', [ElectionReportController::class, 'showCategoryReport'])->name('reports.category');
    Route::get('/reports/all', [ElectionReportController::class, 'showAllReports'])->name('reports.all');
    Route::post('/reports/close/{id}', [ElectionReportController::class, 'closeCategoryElection'])->name('reports.close_category');
    Route::post('/reports/close-all', [ElectionReportController::class, 'closeAllElections'])->name('reports.close_all');
    Route::post('/reports/reopen/{id}', [ElectionReportController::class, 'reopenCategoryElection'])->name('reports.reopen_category');
    Route::post('/reports/set-end-time/{id}', [ElectionReportController::class, 'updateEndTime'])->name('reports.set_end_time');
    Route::post('/reports/set-all-end-time', [ElectionReportController::class, 'setAllEndTime'])->name('reports.set_all_end_time');
    
    // Radar Partisipasi & Mobilisasi Pemilih Lapangan
    Route::get('/participation-radar', [ParticipationRadarController::class, 'index'])->name('radar.index');
    Route::get('/participation-radar/export-unvoted', [ParticipationRadarController::class, 'exportUnvoted'])->name('radar.export_unvoted');
    Route::get('/participation-radar/export-summary', [ParticipationRadarController::class, 'exportSummary'])->name('radar.export_summary');
    
    // Manajemen Kategori Organisasi Mahasiswa (10 Ormawa: Presma, DPM, BEM-F, HMJ)
    Route::post('categories/seed-preset', [CategoryController::class, 'seedTenOrganizations'])->name('categories.seed_preset');
    Route::post('categories/{id}/toggle', [CategoryController::class, 'toggleStatus'])->name('categories.toggle');
    Route::resource('categories', CategoryController::class);

    // Manajemen Paslon (Ketua & Wakil)
    Route::resource('candidates', CandidateController::class);

    // Saringan DPS Kampus & Verifikasi Status Akademik
    Route::get('voters/dps-verification', [VoterController::class, 'dpsVerification'])->name('voters.dps');
    Route::post('voters/dps-simulation', [VoterController::class, 'seedDpsSimulation'])->name('voters.dps_simulation');
    Route::post('voters/dps-import', [VoterController::class, 'processDpsImport'])->name('voters.dps_import');
    
    // Pembuatan PIN Token Mahasiswa (Token Generator Otoritas Panitia)
    Route::post('voters/{id}/generate-token', [VoterController::class, 'generateToken'])->name('voters.generate_token');
    Route::post('voters/bulk-generate-tokens', [VoterController::class, 'bulkGenerateTokens'])->name('voters.bulk_tokens');
    Route::get('voters/export-tokens', [VoterController::class, 'exportTokens'])->name('voters.export_tokens');
    Route::get('voters/export-ormawa-dpt', [VoterController::class, 'exportOrmawaDpt'])->name('voters.export_ormawa');

    // Import & Export DPT Mahasiswa
    Route::get('voters/import', [VoterController::class, 'import'])->name('voters.import');
    Route::post('voters/import', [VoterController::class, 'processImport'])->name('voters.import.process');
    Route::get('voters/template', [VoterController::class, 'downloadTemplate'])->name('voters.template');
    Route::get('voters/export', [VoterController::class, 'exportDpt'])->name('voters.export');
    // Generator & Unduh Data Dummy Pemilih Mahasiswa
    Route::post('voters/generate-dummy', [VoterController::class, 'generateDummyData'])->name('voters.generate_dummy');
    Route::get('voters/download-dummy', [VoterController::class, 'downloadDummyExcel'])->name('voters.download_dummy');
    Route::post('voters/clear-dummy', [VoterController::class, 'clearDummyData'])->name('voters.clear_dummy');

    // Manajemen Pemilih Mahasiswa (DPT - CRUD Dikunci demi Netralitas Ormawa)
    Route::resource('voters', VoterController::class);
    Route::post('voters/{id}/toggle', [VoterController::class, 'toggleStatus'])->name('voters.toggle');
    Route::post('voters/{id}/reset-password', [VoterController::class, 'resetPassword'])->name('voters.reset_password');
});

// ===== AUTENTIKASI PENDAFTARAN PEMILIH MANDIRI =====

use App\Http\Controllers\Auth\PemilihRegistrationController;
use App\Http\Controllers\Auth\NimStatusController;
use App\Http\Controllers\Auth\PemilihLoginController;
use App\Http\Controllers\Admin\HelpdeskController;
use App\Http\Controllers\Admin\FaseAdminController;
use App\Http\Middleware\EnsureFasePendaftaran;
use App\Http\Middleware\EnsureHelpdeskRole;

// PENDAFTARAN
$otpSendThrottle = app()->environment('local') ? 'throttle:120,1' : 'throttle:10,5';
$otpVerifyThrottle = app()->environment('local') ? 'throttle:120,1' : 'throttle:15,5';
$checkStatusThrottle = app()->environment('local') ? 'throttle:120,1' : 'throttle:20,5';
$sengketaThrottle = app()->environment('local') ? 'throttle:60,1' : 'throttle:5,10';

Route::prefix('daftar')->name('daftar.')->group(function () use ($otpSendThrottle, $otpVerifyThrottle) {
    Route::get('/', [PemilihRegistrationController::class, 'showForm'])->name('form');
    Route::post('/otp', [PemilihRegistrationController::class, 'sendOtp'])->middleware($otpSendThrottle)->name('otp.send');
    Route::get('/otp', function () {
        return redirect()->route('voter.login', ['tab' => 'register']);
    });
    Route::get('/otp/verifikasi', [PemilihRegistrationController::class, 'showOtpForm'])->name('otp.form');
    Route::post('/otp/verifikasi', [PemilihRegistrationController::class, 'verifyOtpAndRegister'])->middleware($otpVerifyThrottle)->name('otp.verify');
    Route::post('/otp/resend', [PemilihRegistrationController::class, 'resendOtp'])->middleware($otpSendThrottle)->name('otp.resend');
    Route::get('/sukses', [PemilihRegistrationController::class, 'sukses'])->name('sukses');
});

// CEK STATUS NIM
Route::prefix('nim-status')->name('nim.')->group(function () use ($checkStatusThrottle, $sengketaThrottle) {
    Route::get('/', [NimStatusController::class, 'showForm'])->name('status.form');
    Route::post('/', [NimStatusController::class, 'checkStatus'])->middleware($checkStatusThrottle)->name('status.check');
    Route::get('/sengketa', [NimStatusController::class, 'showSengketaForm'])->name('sengketa.form');
    Route::post('/sengketa', [NimStatusController::class, 'submitSengketa'])->middleware($sengketaThrottle)->name('sengketa.submit');
});

// HELPDESK PANEL
Route::middleware(['auth:web', EnsureHelpdeskRole::class])->prefix('helpdesk')->name('helpdesk.')->group(function () {
    Route::get('/', [HelpdeskController::class, 'index'])->name('index');
    Route::get('/sengketa/{sengketa}', [HelpdeskController::class, 'show'])->name('sengketa.show');
    Route::post('/sengketa/{sengketa}/proses', [HelpdeskController::class, 'proses'])->name('sengketa.proses');
    Route::post('/sengketa/{sengketa}/setujui-cabut', [HelpdeskController::class, 'setujuiCabut'])->name('sengketa.setujui');
    Route::post('/sengketa/{sengketa}/cabut-akun', [HelpdeskController::class, 'cabutAkun'])->name('sengketa.cabut');
    Route::post('/sengketa/{sengketa}/tolak', [HelpdeskController::class, 'tolakSengketa'])->name('sengketa.tolak');
    Route::post('/sengketa/{sengketa}/kode-daftar', [HelpdeskController::class, 'terbitkanKodePendaftaran'])->name('sengketa.kode');
});

// ADMIN FASE PANEL (admin only)
Route::middleware(['auth:web'])->prefix('admin/fase')->name('admin.fase.')->group(function () {
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
