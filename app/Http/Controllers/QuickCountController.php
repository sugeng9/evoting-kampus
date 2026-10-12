<?php

namespace App\Http\Controllers;

use App\Models\BallotBox;
use App\Models\Candidate;
use App\Models\ElectionCategory;
use App\Models\ElectionSetting;
use App\Models\Voter;
use App\Models\VoterParticipation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class QuickCountController extends Controller
{
    /**
     * Tampilkan Halaman Rekapitulasi / Quick Count
     */
    public function index()
    {
        $setting = ElectionSetting::current();
        $isAdmin = Auth::guard('web')->check();

        // Jika quick count belum dibuka dan bukan admin, tampilkan halaman penolakan/info hitung mundur
        if (!$setting->is_quick_count_public && !$isAdmin) {
            return view('public.quick_count_locked', compact('setting'));
        }

        $results = $this->getAggregatedResults();

        return view('public.quick_count', compact('setting', 'results', 'isAdmin'));
    }

    /**
     * Endpoint API JSON untuk Live Real-Time Dashboard (Chart.js / Polling)
     */
    public function getLiveData(): JsonResponse
    {
        $setting = ElectionSetting::current();
        $isAdmin = Auth::guard('web')->check();

        if (!$setting->is_quick_count_public && !$isAdmin) {
            return response()->json([
                'status' => 'locked',
                'message' => 'Hasil quick count masih dirahasiakan oleh panitia KPR (Komisi Pemilihan Raya) & PanWas sampai sesi voting resmi berakhir.',
            ], 403);
        }

        $results = $this->getAggregatedResults();

        return response()->json([
            'status' => 'success',
            'last_updated' => now()->timezone('Asia/Jakarta')->format('H:i:s') . ' WIB',
            'data' => $results,
        ]);
    }

    /**
     * Kalkulasi Agregasi Suara dengan Cache Layering (Anti-Database DoS)
     */
    public static function getAggregatedResults(): array
    {
        // Cache hasil kalkulasi selama 5 detik agar hasil cepat terupdate namun tetap aman dari DoS
        return Cache::remember('quick_count_aggregated_results', 5, function () {
            $categories = ElectionCategory::with('candidates')
                ->where('is_active', true)
                ->orderBy('display_order', 'asc')
                ->get();
            $totalDPT = Voter::where('is_active', true)->count();

            $categoriesData = [];

            foreach ($categories as $category) {
                // Hitung total suara yang masuk di kategori ini
                $totalVotesCategory = BallotBox::where('election_category_id', $category->id)->count();

                // Hitung perolehan per kandidat via Eloquent aggregation (100% Parameterized & Type-Safe)
                $candidateVoteCounts = Candidate::where('election_category_id', $category->id)
                    ->withCount('ballots')
                    ->pluck('ballots_count', 'id')
                    ->toArray();

                $candidateResults = [];
                $highestVotes = -1;
                $leaderCandidateId = null;

                foreach ($category->candidates as $candidate) {
                    $votes = $candidateVoteCounts[$candidate->id] ?? 0;
                    $percentage = $totalVotesCategory > 0 ? round(($votes / $totalVotesCategory) * 100, 1) : 0;

                    if ($votes > $highestVotes && $votes > 0) {
                        $highestVotes = $votes;
                        $leaderCandidateId = $candidate->id;
                    }

                    $candidateResults[] = [
                        'id' => $candidate->id,
                        'number' => $candidate->candidate_number,
                        'leader_name' => $candidate->leader_name,
                        'vice_leader_name' => $candidate->vice_leader_name,
                        'photo_path' => $candidate->photo_path,
                        'total_votes' => $votes,
                        'percentage' => $percentage,
                    ];
                }

                // DPT proporsional untuk HMJ vs Universitas
                $categoryDpt = $category->study_program_filter
                    ? Voter::where('study_program', $category->study_program_filter)->where('is_active', true)->count()
                    : $totalDPT;

                $turnoutPercentage = $categoryDpt > 0 ? round(($totalVotesCategory / $categoryDpt) * 100, 1) : 0;
                $notVotedCategory = max(0, $categoryDpt - $totalVotesCategory);
                $notVotedPercentage = $categoryDpt > 0 ? round(($notVotedCategory / $categoryDpt) * 100, 1) : 0;
                $pillar = $category->getPillarBadge();

                $categoriesData[] = [
                    'category_id' => $category->id,
                    'category_code' => $category->code,
                    'category_name' => $category->name,
                    'pillar_type' => $category->getPillarType(),
                    'pillar_badge' => $pillar,
                    'study_program' => $category->study_program_filter,
                    'schedule_status' => $category->getScheduleStatus(),
                    'status_label' => $category->getStatusLabel(),
                    'is_ended' => $category->isEnded(),
                    'is_ongoing' => $category->isOngoing(),
                    'target_timestamp' => $category->getTargetTimestamp(),
                    'start_at' => $category->start_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB',
                    'end_at' => $category->end_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB',
                    'category_dpt' => $categoryDpt,
                    'total_votes' => $totalVotesCategory,
                    'turnout_percentage' => $turnoutPercentage,
                    'not_voted_count' => $notVotedCategory,
                    'not_voted_percentage' => $notVotedPercentage,
                    'leader_candidate_id' => $leaderCandidateId,
                    'is_aklamasi' => $category->isAklamasi(),
                    'is_nihil' => $category->isNihil(),
                    'is_voting_required' => $category->isVotingRequired(),
                    'aklamasi_candidate' => $category->isAklamasi() ? ($candidateResults[0] ?? null) : null,
                    'candidates' => $candidateResults,
                ];
            }

            $uniqueVotersVoted = VoterParticipation::distinct()->count('voter_id');
            $totalVotersNotVoted = max(0, $totalDPT - $uniqueVotersVoted);
            $votedPercentage = $totalDPT > 0 ? round(($uniqueVotersVoted / $totalDPT) * 100, 1) : 0;
            $notVotedPercentage = $totalDPT > 0 ? round(($totalVotersNotVoted / $totalDPT) * 100, 1) : 0;

            return [
                'total_dpt' => $totalDPT,
                'total_participations' => VoterParticipation::count(),
                'voters_voted' => $uniqueVotersVoted,
                'voters_not_voted' => $totalVotersNotVoted,
                'voters_voted_pct' => $votedPercentage,
                'voters_not_voted_pct' => $notVotedPercentage,
                'categories' => $categoriesData,
            ];
        });
    }
}
