<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BallotBox;
use App\Models\ElectionCategory;
use App\Models\ElectionSetting;
use App\Models\Voter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ElectionReportController extends Controller
{
    /**
     * Halaman Indeks Laporan & Berita Acara Hasil Tetap Seluruh Ormawa
     */
    public function index(Request $request)
    {
        ElectionCategory::cleanupLegacyDuplicates();

        $categories = ElectionCategory::with('allCandidates')
            ->where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();

        $setting = ElectionSetting::current();
        $totalDPTGlobal = Voter::where('is_active', true)->count();

        $reportsData = [];
        foreach ($categories as $cat) {
            $reportsData[] = $this->compileCategoryReportData($cat, $totalDPTGlobal);
        }

        // Hitung statistik kesiapan hasil tetap
        $totalOrmawa = count($reportsData);
        $endedCount = collect($reportsData)->where('is_ended', true)->count();
        $ongoingCount = collect($reportsData)->where('is_ended', false)->count();

        $filter = $request->query('filter', 'all'); // 'all', 'ended', 'ongoing'
        if ($filter === 'ended') {
            $filteredReports = collect($reportsData)->where('is_ended', true)->values()->all();
        } elseif ($filter === 'ongoing') {
            $filteredReports = collect($reportsData)->where('is_ended', false)->values()->all();
        } else {
            $filteredReports = $reportsData;
        }

        return view('admin.reports.index', compact(
            'setting',
            'reportsData',
            'filteredReports',
            'totalOrmawa',
            'endedCount',
            'ongoingCount',
            'filter'
        ));
    }

    /**
     * Tampilan Cetak Berita Acara & Laporan Resmi Per Kategori / Ormawa
     */
    public function showCategoryReport(int $id)
    {
        ElectionCategory::cleanupLegacyDuplicates();

        $category = ElectionCategory::with('allCandidates')->findOrFail($id);
        $setting = ElectionSetting::current();
        $totalDPTGlobal = Voter::where('is_active', true)->count();

        $report = $this->compileCategoryReportData($category, $totalDPTGlobal);

        return view('admin.reports.print_single', compact('setting', 'report'));
    }

    /**
     * Tampilan Cetak Gabungan Seluruh 10 Ormawa (Buku Berita Acara Resmi Lengkap)
     */
    public function showAllReports()
    {
        ElectionCategory::cleanupLegacyDuplicates();

        $categories = ElectionCategory::with('allCandidates')
            ->where('is_active', true)
            ->orderBy('display_order', 'asc')
            ->get();

        $setting = ElectionSetting::current();
        $totalDPTGlobal = Voter::where('is_active', true)->count();

        $reports = [];
        foreach ($categories as $cat) {
            $reports[] = $this->compileCategoryReportData($cat, $totalDPTGlobal);
        }

        $allEnded = collect($reports)->every(fn($r) => $r['is_ended']);

        return view('admin.reports.print_all', compact('setting', 'reports', 'allEnded'));
    }

    /**
     * Aksi Cepat: Akhiri Waktu Pemilihan & Kunci Hasil Tetap Per Kategori
     */
    public function closeCategoryElection(int $id)
    {
        $category = ElectionCategory::findOrFail($id);
        $now = now();

        $category->update([
            'status_override' => 'FORCE_CLOSED',
            'end_at' => $now,
        ]);

        AuditLog::log('CATEGORY_CLOSED_OFFICIAL', 'admin', Auth::id(), [
            'category_id' => $category->id,
            'category_name' => $category->name,
            'closed_at' => $now->toDateTimeString(),
            'admin_name' => Auth::user()->name ?? 'Administrator',
        ]);

        Cache::forget('quick_count_aggregated_results');

        return back()->with('success', "Waktu pemilihan '{$category->name}' resmi berakhir! Hasil perolehan suara terkunci menjadi HASIL TETAP RESMI dan Pemenang Terpilih telah disahkan.");
    }

    /**
     * Aksi Cepat: Selesaikan & Kunci Hasil Tetap Seluruh 10 Ormawa Sekaligus
     */
    public function closeAllElections()
    {
        $now = now();

        ElectionCategory::where('is_active', true)->update([
            'status_override' => 'FORCE_CLOSED',
            'end_at' => $now,
        ]);

        $setting = ElectionSetting::current();
        $setting->update([
            'status' => 'CLOSED',
            'voting_ends_at' => $now,
            'is_quick_count_public' => true,
        ]);

        AuditLog::log('ALL_CATEGORIES_CLOSED_OFFICIAL', 'admin', Auth::id(), [
            'closed_at' => $now->toDateTimeString(),
            'admin_name' => Auth::user()->name ?? 'Administrator',
        ]);

        Cache::forget('quick_count_aggregated_results');

        return back()->with('success', "Seluruh 10 Pemilihan Organisasi Mahasiswa resmi diselesaikan serentak! Seluruh hasil suara terkunci menjadi HASIL TETAP RESMI dan Buku Laporan Rekapitulasi siap disahkan & dicetak.");
    }

    /**
     * Aksi: Buka Kembali Sesi Pemilihan (Perpanjang Waktu)
     */
    public function reopenCategoryElection(Request $request, int $id)
    {
        $category = ElectionCategory::findOrFail($id);
        $hours = (int) $request->input('hours', 2);
        $newEnd = now()->addHours($hours);

        $category->update([
            'status_override' => 'FORCE_OPEN',
            'end_at' => $newEnd,
        ]);

        AuditLog::log('CATEGORY_REOPENED', 'admin', Auth::id(), [
            'category_id' => $category->id,
            'category_name' => $category->name,
            'new_end' => $newEnd->toDateTimeString(),
            'admin_name' => Auth::user()->name ?? 'Administrator',
        ]);

        Cache::forget('quick_count_aggregated_results');

        return back()->with('success', "Pemilihan '{$category->name}' dibuka kembali hingga " . $newEnd->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . " WIB.");
    }

    /**
     * Atur Batas Waktu Pemilihan Habis untuk Ormawa Tertentu
     */
    public function updateEndTime(Request $request, int $id)
    {
        $validated = $request->validate([
            'end_at' => ['required', 'date'],
        ]);

        $category = ElectionCategory::findOrFail($id);
        $parsedEnd = Carbon::parse($validated['end_at'], 'Asia/Jakarta');

        $category->update([
            'end_at' => $parsedEnd,
            'status_override' => $parsedEnd->isPast() ? 'FORCE_CLOSED' : 'AUTO',
        ]);

        Cache::forget('quick_count_aggregated_results');

        return back()->with('success', "Batas waktu pemilihan habis untuk '{$category->name}' diatur ke: " . $parsedEnd->translatedFormat('d F Y, H:i') . " WIB.");
    }

    /**
     * Atur Batas Waktu Pemilihan Serentak untuk Seluruh 10 Ormawa
     */
    public function setAllEndTime(Request $request)
    {
        $validated = $request->validate([
            'end_at' => ['required', 'date'],
        ]);

        $parsedEnd = Carbon::parse($validated['end_at'], 'Asia/Jakarta');

        ElectionCategory::where('is_active', true)->update([
            'end_at' => $parsedEnd,
            'status_override' => $parsedEnd->isPast() ? 'FORCE_CLOSED' : 'AUTO',
        ]);

        $setting = ElectionSetting::current();
        $setting->update([
            'voting_ends_at' => $parsedEnd,
            'status' => $parsedEnd->isPast() ? 'CLOSED' : 'OPEN',
        ]);

        Cache::forget('quick_count_aggregated_results');

        return back()->with('success', "Batas waktu pemilihan serentak 10 Ormawa berhasil diatur ke: " . $parsedEnd->translatedFormat('d F Y, H:i') . " WIB.");
    }

    /**
     * Kompilasi data analitik & penetapan pemenang resmi per kategori ormawa
     */
    protected function compileCategoryReportData(ElectionCategory $category, int $totalDPTGlobal): array
    {
        $isEnded = $category->isEnded();
        $isOngoing = $category->isOngoing();
        $schedStatus = $category->getScheduleStatus();

        // 1. DPT yang berhak
        $categoryDPT = $category->study_program_filter
            ? Voter::where('study_program', $category->study_program_filter)->where('is_active', true)->count()
            : $totalDPTGlobal;

        // 2. Total surat suara sah di kotak suara digital
        $totalBallots = BallotBox::where('election_category_id', $category->id)->count();

        // 3. Suara per kandidat
        $votesMap = BallotBox::select('candidate_id', DB::raw('count(*) as total'))
            ->where('election_category_id', $category->id)
            ->groupBy('candidate_id')
            ->pluck('total', 'candidate_id')
            ->toArray();

        $candidatesData = [];
        $highestVotes = -1;
        $winnerData = null;
        $isTie = false;

        $sortedCandidates = $category->allCandidates->sortBy('candidate_number');

        foreach ($sortedCandidates as $cand) {
            $votes = $votesMap[$cand->id] ?? 0;
            $percentage = $totalBallots > 0 ? round(($votes / $totalBallots) * 100, 2) : 0;

            $candItem = [
                'id' => $cand->id,
                'number' => $cand->candidate_number,
                'leader_name' => $cand->leader_name,
                'leader_nim' => $cand->leader_nim,
                'vice_leader_name' => $cand->vice_leader_name,
                'vice_leader_nim' => $cand->vice_leader_nim,
                'photo_path' => $cand->photo_path,
                'vision' => $cand->vision,
                'mission' => $cand->mission,
                'votes' => $votes,
                'percentage' => $percentage,
            ];

            if ($votes > $highestVotes && $votes > 0) {
                $highestVotes = $votes;
                $winnerData = $candItem;
                $isTie = false;
            } elseif ($votes === $highestVotes && $votes > 0) {
                $isTie = true;
            }

            $candidatesData[] = $candItem;
        }

        // Tentukan status pemenang (definitif vs sementara)
        if ($isTie) {
            $winnerData = null; // Hasil imbang
        } elseif ($winnerData) {
            // Pemenang hanya definitif jika waktu pemilihan telah habis / ditutup
            $winnerData['is_definitive'] = $isEnded;
        }

        $turnoutPercentage = $categoryDPT > 0 ? round(($totalBallots / $categoryDPT) * 100, 2) : 0;
        $notVotedCount = max(0, $categoryDPT - $totalBallots);
        $notVotedPercentage = $categoryDPT > 0 ? round(($notVotedCount / $categoryDPT) * 100, 2) : 0;

        // Nomor Berita Acara Resmi
        $baNumber = 'BA-' . str_pad($category->display_order, 2, '0', STR_PAD_LEFT) . '/KPU-POLBENG/PEMIRA/' . date('Y');

        // Checksum hash integritas kriptografis dokumen
        $hashSeed = $category->code . '|' . $categoryDPT . '|' . $totalBallots . '|' . ($isEnded ? 'FINAL' : 'PROVISIONAL') . '|' . json_encode($candidatesData) . '|' . config('app.key');
        $documentChecksum = hash('sha256', $hashSeed);

        // Format tanggal & batas waktu habis
        $endTimeFormatted = $category->end_at
            ? $category->end_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') . ' WIB'
            : ($category->status_override === 'FORCE_CLOSED' ? 'Ditutup Panitia' : 'Sesuai Jadwal Pleno');

        return [
            'category' => $category,
            'ba_number' => $baNumber,
            'pillar_badge' => $category->getPillarBadge(),
            'pillar_type' => $category->getPillarType(),
            'category_dpt' => $categoryDPT,
            'total_ballots' => $totalBallots,
            'turnout_percentage' => $turnoutPercentage,
            'not_voted_count' => $notVotedCount,
            'not_voted_percentage' => $notVotedPercentage,
            'candidates' => $candidatesData,
            'winner' => $winnerData,
            'is_tie' => $isTie,
            'is_ended' => $isEnded,
            'is_ongoing' => $isOngoing,
            'schedule_status' => $schedStatus,
            'schedule_status_label' => $category->getStatusLabel(),
            'end_time_formatted' => $endTimeFormatted,
            'target_timestamp' => $category->getTargetTimestamp(),
            'document_checksum' => strtoupper($documentChecksum),
            'generated_at' => now()->timezone('Asia/Jakarta')->translatedFormat('l, d F Y - H:i:s') . ' WIB',
            'official_date' => now()->timezone('Asia/Jakarta')->translatedFormat('d F Y'),
        ];
    }
}
