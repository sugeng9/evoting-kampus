<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitVoteRequest;
use App\Models\BallotBox;
use App\Models\Candidate;
use App\Models\ElectionCategory;
use App\Models\ElectionSetting;
use App\Models\VoterParticipation;
use App\Services\DoubleVotingException;
use App\Services\IneligibleVoterException;
use App\Services\VotingClosedException;
use App\Services\VotingService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VotingController extends Controller
{
    protected VotingService $votingService;

    public function __construct(VotingService $votingService)
    {
        $this->votingService = $votingService;
    }

    /**
     * Dashboard Pemilih: Menampilkan Kategori Pemilihan (Presma & Kahim)
     */
    public function dashboard()
    {
        $voter = Auth::guard('voter')->user();
        $setting = ElectionSetting::current();
        
        $categoriesData = $this->votingService->getEligibleCategoriesForVoter($voter);

        // Hitung ringkasan status hak suara pemilih saat ini (Hanya kategori yang memerlukan voting - OPSI B)
        $votingCategories = collect($categoriesData)->where('requires_voting', true);
        $totalEligible = $votingCategories->count();
        $totalVoted = $votingCategories->where('has_voted', true)->count();
        $allCompleted = ($totalEligible > 0 && $totalEligible === $totalVoted);
        $aklamasiCount = collect($categoriesData)->where('is_aklamasi', true)->count();
        $nihilCount = collect($categoriesData)->where('is_nihil', true)->count();

        // Transparansi Data Partisipasi: Sudah Memilih vs Belum Memilih
        $totalDptGlobal = \App\Models\Voter::where('is_active', true)->count();
        $votedVotersCountGlobal = \App\Models\VoterParticipation::distinct()->count('voter_id');
        $notVotedVotersCountGlobal = max(0, $totalDptGlobal - $votedVotersCountGlobal);
        $votedPctGlobal = $totalDptGlobal > 0 ? round(($votedVotersCountGlobal / $totalDptGlobal) * 100, 1) : 0;
        $notVotedPctGlobal = $totalDptGlobal > 0 ? round(($notVotedVotersCountGlobal / $totalDptGlobal) * 100, 1) : 0;

        // Data Transparansi Jurusan Pemilih
        $totalDptDept = \App\Models\Voter::where('study_program', $voter->study_program)
            ->where('is_active', true)
            ->count();
        $votedVotersCountDept = \App\Models\VoterParticipation::whereHas('voter', function ($q) use ($voter) {
            $q->where('study_program', $voter->study_program)->where('is_active', true);
        })->distinct()->count('voter_id');
        $notVotedVotersCountDept = max(0, $totalDptDept - $votedVotersCountDept);
        $votedPctDept = $totalDptDept > 0 ? round(($votedVotersCountDept / $totalDptDept) * 100, 1) : 0;
        $notVotedPctDept = $totalDptDept > 0 ? round(($notVotedVotersCountDept / $totalDptDept) * 100, 1) : 0;

        // Perkaya setiap kategori dengan statistik 'Sudah Memilih' vs 'Belum Memilih'
        foreach ($categoriesData as &$item) {
            $cat = $item['category'];
            $votesInCat = \App\Models\BallotBox::where('election_category_id', $cat->id)->count();
            $targetDptCat = $cat->study_program_filter 
                ? \App\Models\Voter::where('study_program', $cat->study_program_filter)->where('is_active', true)->count()
                : $totalDptGlobal;
            $notVotedInCat = max(0, $targetDptCat - $votesInCat);
            $votedPctCat = $targetDptCat > 0 ? round(($votesInCat / max(1, $targetDptCat)) * 100, 1) : 0;
            $notVotedPctCat = $targetDptCat > 0 ? round(($notVotedInCat / max(1, $targetDptCat)) * 100, 1) : 0;

            $item['stats'] = [
                'target_dpt' => $targetDptCat,
                'voted_count' => $votesInCat,
                'not_voted_count' => $notVotedInCat,
                'voted_pct' => $votedPctCat,
                'not_voted_pct' => $notVotedPctCat,
            ];
        }
        unset($item);

        $participationTransparency = [
            'global' => [
                'total_dpt' => $totalDptGlobal,
                'voted' => $votedVotersCountGlobal,
                'not_voted' => $notVotedVotersCountGlobal,
                'voted_pct' => $votedPctGlobal,
                'not_voted_pct' => $notVotedPctGlobal,
            ],
            'department' => [
                'name' => $voter->study_program,
                'total_dpt' => $totalDptDept,
                'voted' => $votedVotersCountDept,
                'not_voted' => $notVotedVotersCountDept,
                'voted_pct' => $votedPctDept,
                'not_voted_pct' => $notVotedPctDept,
            ],
        ];

        return view('voter.dashboard', compact(
            'voter', 
            'setting', 
            'categoriesData', 
            'totalEligible', 
            'totalVoted', 
            'allCompleted',
            'aklamasiCount',
            'nihilCount',
            'participationTransparency'
        ));
    }

    /**
     * Bilik Suara: Menampilkan Calon Kandidat untuk Kategori Tertentu
     */
    public function showBallot(int $categoryId)
    {
        $voter = Auth::guard('voter')->user();
        $category = ElectionCategory::with('candidates')->findOrFail($categoryId);

        // Pengalihan cerdas jika pemilih mengakses tautan ID kategori lama
        if ($category->code === 'PRESMA_2026') {
            $bem = ElectionCategory::where('code', 'BEM_KM_2026')->where('is_active', true)->first();
            if ($bem) {
                return redirect()->route('voter.ballot', $bem->id);
            }
        }
        if ($category->code === 'DPM_KM_2026') {
            $mpm = ElectionCategory::where('code', 'MPM_KM_2026')->where('is_active', true)->first();
            if ($mpm) {
                return redirect()->route('voter.ballot', $mpm->id);
            }
        }

        // Cek kelayakan OPSI B: Bilik suara hanya melayani kategori yang ada kompetisi (>= 2 paslon)
        if (!$category->isVotingRequired()) {
            if ($category->isAklamasi()) {
                $cand = $category->getAklamasiCandidate();
                $candName = $cand ? $cand->leader_name : 'Calon Tunggal';
                return redirect()->route('voter.dashboard')->with('info', "Kategori '{$category->name}' telah ditetapkan secara AKLAMASI kepada Sdr. {$candName} (Calon Tunggal) sesuai Surat Ketetapan KPU. Anda tidak perlu mencoblos pada kategori ini.");
            } else {
                return redirect()->route('voter.dashboard')->with('info', "Kategori '{$category->name}' saat ini berstatus NIHIL (belum ada pasangan calon terdaftar).");
            }
        }

        // Cek kelayakan
        if (!$category->isEligible($voter)) {
            return redirect()->route('voter.dashboard')->with('error', 'Anda tidak berhak memilih pada kategori ini.');
        }

        // Cek apakah sudah memilih
        if ($voter->hasVotedForCategory($categoryId)) {
            return redirect()->route('voter.dashboard')->with('error', 'Anda sudah menggunakan hak suara untuk kategori ini.');
        }

        $setting = ElectionSetting::current();
        if (!$setting->isOpen()) {
            return redirect()->route('voter.dashboard')->with('error', 'Sesi pemilihan saat ini ditutup.');
        }

        // Cek jadwal waktu independen kategori (Presma vs HMJ)
        if (!$category->isScheduleOpen()) {
            $status = $category->getScheduleStatus();
            $msg = match($status) {
                'UPCOMING' => 'Bilik suara untuk ' . $category->name . ' belum dibuka. Jadwal buka: ' . ($category->start_at ? $category->start_at->translatedFormat('d M Y, H:i T') : 'segera') . '.',
                'ENDED' => 'Sesi pemilihan untuk ' . $category->name . ' telah berakhir pada ' . ($category->end_at ? $category->end_at->translatedFormat('d M Y, H:i T') : '-') . '.',
                default => 'Sesi pemilihan untuk kategori ini saat ini sedang ditutup oleh panitia.'
            };
            return redirect()->route('voter.dashboard')->with('error', $msg);
        }

        // Generate One-Time Idempotency Voting Token
        $votingToken = \Illuminate\Support\Str::random(40);
        session(['voting_token_' . $categoryId => $votingToken]);

        return view('voter.ballot', compact('voter', 'category', 'setting', 'votingToken'));
    }

    /**
     * Submit Suara Final dengan Database Transaction & Anti-Race Condition
     */
    public function submitVote(SubmitVoteRequest $request)
    {
        // 1. Validasi Input Ketat via FormRequest
        $validated = $request->validated();

        $voter = Auth::guard('voter')->user();

        // 2. Re-verifikasi PIN untuk mencegah pembajakan sesi di bilik fisik/komputer lab
        if (!Hash::check($validated['confirmation_pin'], $voter->password)) {
            return back()->withErrors([
                'confirmation_pin' => 'PIN konfirmasi yang Anda masukkan salah. Suara belum tersimpan.',
            ]);
        }

        // Validasi OPSI B: Tolak submit jika kategori berstatus Aklamasi/Nihil
        $category = ElectionCategory::findOrFail((int) $validated['category_id']);
        if (!$category->isVotingRequired()) {
            return redirect()->route('voter.dashboard')->with('error', 'Kategori ini berstatus Aklamasi / Nihil dan tidak menerima pemungutan suara.');
        }

        // 3. Simpan Suara Melalui Core Voting Service
        try {
            $receipt = $this->votingService->castVote(
                $voter,
                (int) $validated['category_id'],
                (int) $validated['candidate_id'],
                $validated['voting_token'] ?? null
            );

            // Simpan data kuitansi ke flash session untuk dicetak/disimpan oleh mahasiswa
            return redirect()->route('voter.receipt')->with('receipt', $receipt);

        } catch (DoubleVotingException $e) {
            return redirect()->route('voter.dashboard')->with('error', $e->getMessage());
        } catch (VotingClosedException $e) {
            return redirect()->route('voter.dashboard')->with('error', $e->getMessage());
        } catch (IneligibleVoterException $e) {
            return redirect()->route('voter.dashboard')->with('error', $e->getMessage());
        } catch (Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem saat memproses suara Anda. Silakan coba kembali.');
        }
    }

    /**
     * Tampilan Kuitansi Kriptografis Bukti Partisipasi & Hasil Suara Sementara
     */
    public function showReceipt()
    {
        $receipt = session('receipt');

        if (!$receipt) {
            return redirect()->route('voter.dashboard');
        }

        $voter = Auth::guard('voter')->user();
        $categoryId = $receipt['category_id'] ?? null;
        $categoryResults = null;
        $setting = ElectionSetting::current();

        // Tampilkan hasil Quick Count paslon hanya jika Panitia KPU membuka akses publik
        if ($categoryId && $setting->is_quick_count_public) {
            $category = ElectionCategory::with('candidates')->find($categoryId);
            if ($category) {
                $totalVotesCategory = BallotBox::where('election_category_id', $categoryId)->count();
                $candidateVoteCounts = BallotBox::select('candidate_id', DB::raw('count(*) as total'))
                    ->where('election_category_id', $categoryId)
                    ->groupBy('candidate_id')
                    ->pluck('total', 'candidate_id')
                    ->toArray();

                $candidatesData = [];
                foreach ($category->candidates as $candidate) {
                    $votes = $candidateVoteCounts[$candidate->id] ?? 0;
                    $percentage = $totalVotesCategory > 0 ? round(($votes / $totalVotesCategory) * 100, 1) : 0;
                    $candidatesData[] = [
                        'id' => $candidate->id,
                        'number' => $candidate->candidate_number,
                        'leader_name' => $candidate->leader_name,
                        'vice_leader_name' => $candidate->vice_leader_name,
                        'photo_path' => $candidate->photo_path,
                        'total_votes' => $votes,
                        'percentage' => $percentage,
                    ];
                }

                // Urutkan perolehan suara terbanyak ke sedikit
                usort($candidatesData, fn($a, $b) => $b['total_votes'] <=> $a['total_votes']);

                $categoryResults = [
                    'category' => $category,
                    'total_votes' => $totalVotesCategory,
                    'candidates' => $candidatesData,
                ];
            }
        }

        // Cek kategori hak suara berikutnya yang belum dicoblos oleh mahasiswa ini
        $eligibleCategories = $this->votingService->getEligibleCategoriesForVoter($voter);
        $nextCategory = null;
        $remainingCount = 0;
        foreach ($eligibleCategories as $item) {
            if (!$item['has_voted']) {
                $remainingCount++;
                $isOpen = $item['is_open'] ?? $item['category']->isScheduleOpen();
                if (!$nextCategory && $isOpen) {
                    $nextCategory = $item['category'];
                }
            }
        }

        // Fallback jika semua sisa kategori belum ada yang open
        if (!$nextCategory && $remainingCount > 0) {
            foreach ($eligibleCategories as $item) {
                if (!$item['has_voted']) {
                    $nextCategory = $item['category'];
                    break;
                }
            }
        }

        return view('voter.receipt', compact('receipt', 'categoryResults', 'nextCategory', 'remainingCount'));
    }
}
