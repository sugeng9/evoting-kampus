<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Candidate;
use App\Models\ElectionCategory;
use App\Models\ElectionSetting;
use App\Models\Voter;
use App\Models\VoterParticipation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{
    /**
     * Dashboard Utama Panitia / KPU Kampus
     */
    public function index()
    {
        $setting = ElectionSetting::current();
        $totalDPT = Voter::count();
        $activeDPT = Voter::where('is_active', true)->count();
        $totalParticipations = VoterParticipation::count();
        
        $categories = ElectionCategory::withCount(['candidates', 'participations', 'ballots'])->get();
        $recentLogs = AuditLog::orderBy('created_at', 'desc')->limit(15)->get();
        $quickCountResults = \App\Http\Controllers\QuickCountController::getAggregatedResults();

        return view('admin.dashboard', compact(
            'setting',
            'totalDPT',
            'activeDPT',
            'totalParticipations',
            'categories',
            'recentLogs',
            'quickCountResults'
        ));
    }

    /**
     * Halaman Khusus Quick Count Grafik & Visual Real-Time untuk Administrator
     */
    public function quickCount()
    {
        $setting = ElectionSetting::current();
        $results = \App\Http\Controllers\QuickCountController::getAggregatedResults();

        return view('admin.quick_count', compact('setting', 'results'));
    }

    /**
     * Kontrol Status Sesi Voting (Buka / Tutup / Jeda Pemilihan)
     */
    public function updateSessionStatus(Request $request)
    {
        $request->validate([
            'status' => ['required', 'in:DRAFT,OPEN,PAUSED,CLOSED'],
        ]);

        $setting = ElectionSetting::current();
        $oldStatus = $setting->status;
        $newStatus = $request->input('status');

        $setting->update([
            'status' => $newStatus,
        ]);

        AuditLog::log('ELECTION_STATUS_CHANGED', 'admin', Auth::id(), [
            'from' => $oldStatus,
            'to' => $newStatus,
            'admin_name' => Auth::user()->name ?? 'Administrator',
        ]);

        // Bersihkan cache quick count agar segera sinkron
        Cache::forget('quick_count_aggregated_results');

        return back()->with('success', "Status sesi pemilihan berhasil diubah menjadi: {$newStatus}");
    }

    /**
     * Kontrol Publikasi Quick Count / Real-Time Dashboard
     */
    public function toggleQuickCount(Request $request)
    {
        $setting = ElectionSetting::current();
        $newStatus = !$setting->is_quick_count_public;

        $setting->update([
            'is_quick_count_public' => $newStatus,
        ]);

        AuditLog::log('QUICK_COUNT_VISIBILITY_TOGGLED', 'admin', Auth::id(), [
            'is_public' => $newStatus,
        ]);

        Cache::forget('quick_count_aggregated_results');

        $statusText = $newStatus ? 'DIBUKA untuk publik' : 'DITUTUP (Hanya panitia)';
        return back()->with('success', "Visibilitas Quick Count sekarang: {$statusText}");
    }

    /**
     * Memperbarui Jadwal Waktu dan Status Pemilihan Per Kategori (Presma / HMJ)
     */
    public function updateCategorySchedule(Request $request, int $id)
    {
        $validated = $request->validate([
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'status_override' => ['required', 'in:AUTO,FORCE_OPEN,FORCE_CLOSED'],
        ], [
            'end_at.after_or_equal' => 'Waktu selesai harus sama dengan atau setelah waktu mulai.',
            'status_override.in' => 'Pilihan mode status tidak valid.',
        ]);

        $category = ElectionCategory::findOrFail($id);

        $oldStart = $category->start_at ? $category->start_at->toDateTimeString() : null;
        $oldEnd = $category->end_at ? $category->end_at->toDateTimeString() : null;
        $oldOverride = $category->status_override;

        $category->update([
            'start_at' => !empty($validated['start_at']) ? \Carbon\Carbon::parse($validated['start_at'], 'Asia/Jakarta') : null,
            'end_at' => !empty($validated['end_at']) ? \Carbon\Carbon::parse($validated['end_at'], 'Asia/Jakarta') : null,
            'status_override' => $validated['status_override'],
        ]);

        AuditLog::log('CATEGORY_SCHEDULE_UPDATED', 'admin', Auth::id(), [
            'category_id' => $category->id,
            'category_name' => $category->name,
            'old' => ['start' => $oldStart, 'end' => $oldEnd, 'override' => $oldOverride],
            'new' => [
                'start' => $category->start_at?->toDateTimeString(),
                'end' => $category->end_at?->toDateTimeString(),
                'override' => $category->status_override,
            ],
            'admin_name' => Auth::user()->name ?? 'Administrator',
        ]);

        Cache::forget('quick_count_aggregated_results');

        return back()->with('success', "Jadwal pemilihan untuk kategori '{$category->name}' berhasil diperbarui.");
    }

    /**
     * Menampilkan Audit Log Sistem yang Tidak Dapat Dimanipulasi
     */
    public function auditLogs()
    {
        $logs = AuditLog::orderBy('created_at', 'desc')->paginate(50);
        return view('admin.audit_logs', compact('logs'));
    }
}
