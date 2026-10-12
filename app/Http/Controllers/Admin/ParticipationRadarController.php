<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ElectionCategory;
use App\Models\Voter;
use App\Models\VoterParticipation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ParticipationRadarController extends Controller
{
    /**
     * Dashboard Radar Partisipasi Lapangan & Mobilisasi Pemilih
     */
    public function index(Request $request)
    {
        $selectedProdi = $request->query('prodi', 'all');
        $filterStatus = $request->query('status', 'zero_votes'); // 'zero_votes', 'partial_votes', 'all_incomplete'
        $search = trim($request->query('search', ''));
        $sortBy = $request->query('sort', 'turnout_asc'); // 'turnout_asc' (prioritas mobilisasi), 'turnout_desc', 'name_asc', 'dpt_desc'

        // 1. Ambil daftar seluruh program studi yang terdaftar di DPT
        $allProdis = Voter::whereNotNull('study_program')
            ->where('study_program', '!=', '')
            ->distinct()
            ->orderBy('study_program', 'asc')
            ->pluck('study_program')
            ->toArray();

        // 2. Kalkulasi statistik partisipasi per Program Studi
        $radarData = [];
        $totalDptGlobal = 0;
        $totalVotedGlobal = 0;
        $totalFullyVotedGlobal = 0;
        $totalNotVotedGlobal = 0;

        foreach ($allProdis as $prodi) {
            $prodiDpt = Voter::where('study_program', $prodi)->where('is_active', true)->count();

            // Total pemilih unik yang sudah menggunakan minimal 1 hak suara
            $votedCount = VoterParticipation::whereHas('voter', function ($q) use ($prodi) {
                $q->where('study_program', $prodi)->where('is_active', true);
            })->distinct()->count('voter_id');

            // Hitung berapa kategori yang memerlukan pemungutan suara aktif untuk prodi ini (Opsi B: Aklamasi tidak dihitung bilik suara)
            $prodiVotingRequiredCount = ElectionCategory::where('is_active', true)
                ->get()
                ->filter(function($c) use ($prodi) {
                    $matches = (!$c->study_program_filter || strcasecmp(trim($c->study_program_filter), trim($prodi)) === 0);
                    return $matches && $c->isVotingRequired();
                })
                ->count();
            $prodiVotingRequiredCount = max(1, $prodiVotingRequiredCount);

            // Total pemilih yang sudah menuntaskan seluruh hak suara aktif
            $fullyVotedCount = VoterParticipation::whereHas('voter', function ($q) use ($prodi) {
                $q->where('study_program', $prodi)->where('is_active', true);
            })
                ->select('voter_id')
                ->groupBy('voter_id')
                ->havingRaw('count(*) >= ?', [(int) $prodiVotingRequiredCount])
                ->get()
                ->count();

            $partialCount = max(0, $votedCount - $fullyVotedCount);
            $notVotedCount = max(0, $prodiDpt - $votedCount);

            $turnoutPct = $prodiDpt > 0 ? round(($votedCount / $prodiDpt) * 100, 1) : 0;
            $fullPct = $prodiDpt > 0 ? round(($fullyVotedCount / $prodiDpt) * 100, 1) : 0;
            $notVotedPct = $prodiDpt > 0 ? round(($notVotedCount / $prodiDpt) * 100, 1) : 0;

            // Kategori Urgensi Lapangan
            // < 30%: KRITIS (Merah - Butuh Mobilisasi Cepat Panitia/Komti)
            // 30% - 60%: MODERAT (Kuning - Perlu Pengingat Tambahan)
            // >= 60%: AMAN (Hijau - Kuorum Partisipasi Bagus)
            if ($turnoutPct < 30) {
                $urgency = 'CRITICAL';
                $urgencyLabel = 'Butuh Mobilisasi Darurat';
                $badgeClass = 'bg-rose-100 text-rose-800 border-rose-200';
            } elseif ($turnoutPct < 60) {
                $urgency = 'MODERATE';
                $urgencyLabel = 'Perlu Dorongan';
                $badgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
            } else {
                $urgency = 'GOOD';
                $urgencyLabel = 'Kuorum Terpenuhi';
                $badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
            }

            $radarData[] = [
                'study_program' => $prodi,
                'total_dpt' => $prodiDpt,
                'voted_count' => $votedCount,
                'fully_voted_count' => $fullyVotedCount,
                'partial_count' => $partialCount,
                'not_voted_count' => $notVotedCount,
                'turnout_pct' => $turnoutPct,
                'full_pct' => $fullPct,
                'not_voted_pct' => $notVotedPct,
                'urgency' => $urgency,
                'urgency_label' => $urgencyLabel,
                'badge_class' => $badgeClass,
            ];

            $totalDptGlobal += $prodiDpt;
            $totalVotedGlobal += $votedCount;
            $totalFullyVotedGlobal += $fullyVotedCount;
            $totalNotVotedGlobal += $notVotedCount;
        }

        // 3. Sorting Radar Data (Secara default: prodi dengan partisipasi terendah ditaruh paling atas agar panitia langsung tahu mana yang butuh mobilisasi!)
        $radarCollection = collect($radarData);
        if ($sortBy === 'turnout_asc') {
            $radarCollection = $radarCollection->sortBy('turnout_pct');
        } elseif ($sortBy === 'turnout_desc') {
            $radarCollection = $radarCollection->sortByDesc('turnout_pct');
        } elseif ($sortBy === 'name_asc') {
            $radarCollection = $radarCollection->sortBy('study_program');
        } elseif ($sortBy === 'dpt_desc') {
            $radarCollection = $radarCollection->sortByDesc('total_dpt');
        }
        $sortedRadar = $radarCollection->values()->all();

        // 4. Hitung Ringkasan Nasional / Global Kampus
        $globalTurnoutPct = $totalDptGlobal > 0 ? round(($totalVotedGlobal / $totalDptGlobal) * 100, 1) : 0;
        $criticalProdisCount = collect($sortedRadar)->where('urgency', 'CRITICAL')->count();
        $moderateProdisCount = collect($sortedRadar)->where('urgency', 'MODERATE')->count();
        $goodProdisCount = collect($sortedRadar)->where('urgency', 'GOOD')->count();

        // 5. Query Daftar Mahasiswa Belum Memilih untuk Mobilisasi
        $unvotedQuery = Voter::where('is_active', true)->withCount('participations');

        if ($selectedProdi !== 'all') {
            $unvotedQuery->where('study_program', $selectedProdi);
        }

        if ($filterStatus === 'zero_votes') {
            // Belum memilih sama sekali (0 hak suara)
            $unvotedQuery->has('participations', '=', 0);
        } elseif ($filterStatus === 'partial_votes') {
            // Baru memilih sebagian (1 atau 2 hak suara)
            $unvotedQuery->has('participations', '>', 0)->has('participations', '<', 3);
        } elseif ($filterStatus === 'all_incomplete') {
            // Belum menuntaskan seluruh 3 hak suara (< 3 hak suara)
            $unvotedQuery->has('participations', '<', 3);
        }

        if ($search !== '') {
            $escapedSearch = addcslashes($search, '%_\\');
            $unvotedQuery->where(function ($q) use ($escapedSearch) {
                $q->where('nim', 'like', "%{$escapedSearch}%")
                  ->orWhere('name', 'like', "%{$escapedSearch}%");
            });
        }

        $unvotedList = $unvotedQuery->orderBy('study_program', 'asc')
            ->orderBy('nim', 'asc')
            ->paginate(25)
            ->withQueryString();

        // 6. Template Teks WhatsApp Blast Otomatis untuk disalin ke Komti / Grup Kelas
        $waBlastSample = $this->generateWaBlastText($selectedProdi, $filterStatus);

        return view('admin.radar.index', compact(
            'allProdis',
            'sortedRadar',
            'selectedProdi',
            'filterStatus',
            'search',
            'sortBy',
            'unvotedList',
            'totalDptGlobal',
            'totalVotedGlobal',
            'totalFullyVotedGlobal',
            'totalNotVotedGlobal',
            'globalTurnoutPct',
            'criticalProdisCount',
            'moderateProdisCount',
            'goodProdisCount',
            'waBlastSample'
        ));
    }

    /**
     * Ekspor Data Mahasiswa Belum Memilih ke File CSV / Excel (Siap Kirim ke Dosen/Komti)
     */
    public function exportUnvoted(Request $request)
    {
        $selectedProdi = $request->query('prodi', 'all');
        $filterStatus = $request->query('status', 'zero_votes');

        $query = Voter::where('is_active', true)->withCount('participations');

        if ($selectedProdi !== 'all') {
            $query->where('study_program', $selectedProdi);
        }

        if ($filterStatus === 'zero_votes') {
            $query->has('participations', '=', 0);
            $statusFilename = 'belum_memilih_sama_sekali';
        } elseif ($filterStatus === 'partial_votes') {
            $query->has('participations', '>', 0)->has('participations', '<', 3);
            $statusFilename = 'baru_sebagian';
        } else {
            $query->has('participations', '<', 3);
            $statusFilename = 'belum_tuntas_semua';
        }

        $cleanProdiName = $selectedProdi === 'all' ? 'seluruh_jurusan' : str_replace([' ', '/', '\\'], '_', strtolower($selectedProdi));
        $filename = "dpt_{$statusFilename}_{$cleanProdiName}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        AuditLog::log('RADAR_UNVOTED_EXPORTED', 'admin', Auth::id(), [
            'prodi' => $selectedProdi,
            'status' => $filterStatus,
            'admin_name' => Auth::user()->name ?? 'Administrator',
        ]);

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            
            // Tulis UTF-8 BOM agar Microsoft Excel di Windows otomatis membaca karakter dengan benar
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Baris Header CSV
            fputcsv($handle, [
                'No',
                'NIM',
                'Nama Mahasiswa',
                'Program Studi / Jurusan',
                'Fakultas',
                'Hak Suara Terpakai',
                'Status Partisipasi',
                'Waktu Login Terakhir (WIB)',
            ]);

            $no = 1;
            $query->orderBy('study_program', 'asc')->orderBy('nim', 'asc')->chunk(200, function ($voters) use ($handle, &$no) {
                foreach ($voters as $v) {
                    $usedVotes = $v->participations_count;
                    if ($usedVotes === 0) {
                        $statusText = 'Belum Memilih Sama Sekali (0 dari 3)';
                    } elseif ($usedVotes < 3) {
                        $statusText = "Baru Sebagian ({$usedVotes} dari 3 Hak Suara)";
                    } else {
                        $statusText = 'Sudah Tuntas (3 dari 3)';
                    }

                    fputcsv($handle, [
                        $no++,
                        "'" . $v->nim, // Awali tanda petik agar Excel tidak memotong angka 0 di depan
                        $v->name,
                        $v->study_program ?: '-',
                        $v->faculty ?: '-',
                        "{$usedVotes} / 3",
                        $statusText,
                        $v->last_login_at ? $v->last_login_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Belum Pernah Login',
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Ekspor Ringkasan Leaderboard Partisipasi Seluruh Jurusan ke CSV / Excel
     */
    public function exportSummary()
    {
        $allProdis = Voter::whereNotNull('study_program')
            ->where('study_program', '!=', '')
            ->distinct()
            ->orderBy('study_program', 'asc')
            ->pluck('study_program')
            ->toArray();

        $rows = [];
        foreach ($allProdis as $prodi) {
            $voterIds = Voter::where('study_program', $prodi)->where('is_active', true)->pluck('id');
            $prodiDpt = $voterIds->count();
            $votedCount = VoterParticipation::whereIn('voter_id', $voterIds)->distinct()->count('voter_id');
            $notVotedCount = max(0, $prodiDpt - $votedCount);
            $turnoutPct = $prodiDpt > 0 ? round(($votedCount / $prodiDpt) * 100, 1) : 0;

            if ($turnoutPct < 30) {
                $statusKuorum = 'KRITIS (Perlu Mobilisasi Darurat)';
            } elseif ($turnoutPct < 60) {
                $statusKuorum = 'SEDANG (Perlu Dorongan)';
            } else {
                $statusKuorum = 'AMAN (Kuorum Terpenuhi)';
            }

            $rows[] = [
                'prodi' => $prodi,
                'total_dpt' => $prodiDpt,
                'voted' => $votedCount,
                'not_voted' => $notVotedCount,
                'pct' => $turnoutPct,
                'status' => $statusKuorum,
            ];
        }

        // Urutkan dari partisipasi tertinggi ke terendah
        $sortedRows = collect($rows)->sortByDesc('pct')->values()->all();

        $filename = "rekap_partisipasi_prodi_pemira_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        AuditLog::record('DATA_EXPORTED', 'export:radar_summary', [
            'type' => 'turnout_leaderboard_csv',
            'filename' => $filename,
            'admin_name' => Auth::user()->name ?? 'Administrator',
        ]);

        $callback = function () use ($sortedRows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Peringkat',
                'Program Studi / Jurusan',
                'Total DPT (Pemilih Sah)',
                'Jumlah Sudah Memilih',
                'Jumlah Belum Memilih',
                'Partisipasi (%)',
                'Status Kuorum Lapangan',
            ]);

            foreach ($sortedRows as $idx => $r) {
                fputcsv($handle, [
                    $idx + 1,
                    $r['prodi'],
                    $r['total_dpt'],
                    $r['voted'],
                    $r['not_voted'],
                    $r['pct'] . '%',
                    $r['status'],
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generator Template Pesan WhatsApp Pengingat Mobilisasi
     */
    protected function generateWaBlastText(string $prodi, string $filterStatus): string
    {
        $targetProdiTitle = ($prodi === 'all') ? 'Seluruh Mahasiswa Politeknik Negeri Bengkalis' : "Jurusan {$prodi}";

        $unvotedQuery = Voter::where('is_active', true);
        if ($prodi !== 'all') {
            $unvotedQuery->where('study_program', $prodi);
        }

        if ($filterStatus === 'zero_votes') {
            $unvotedQuery->has('participations', '=', 0);
            $actionLabel = 'belum menggunakan hak suara sama sekali';
        } else {
            $unvotedQuery->has('participations', '<', 3);
            $actionLabel = 'belum menuntaskan pemilihan (3 hak suara)';
        }

        $sampleNames = $unvotedQuery->orderBy('nim', 'asc')->limit(10)->get(['nim', 'name']);
        $totalLeft = $unvotedQuery->count();

        $text = "📢 *PENGUMUMAN PENTING KPU-M POLBENG 2026*\n\n";
        $text .= "Halo rekan-rekan mahasiswa *{$targetProdiTitle}*,\n";
        $text .= "Pemungutan suara PEMIRA tinggal beberapa jam lagi sebelum bilik suara digital ditutup secara resmi.\n\n";
        $text .= "Tercatat masih ada *{$totalLeft} mahasiswa* yang {$actionLabel}.\n\n";
        
        if ($sampleNames->count() > 0) {
            $text .= "Beberapa di antaranya:\n";
            foreach ($sampleNames as $i => $s) {
                $text .= ($i + 1) . ". {$s->nim} - {$s->name}\n";
            }
            if ($totalLeft > 10) {
                $remaining = $totalLeft - 10;
                $text .= "... dan {$remaining} mahasiswa lainnya.\n";
            }
            $text .= "\n";
        }

        $text .= "📌 *Yuk gunakan 3 hak suaramu sekarang juga!*\n";
        $text .= "1. BEM Polbeng (Presma)\n";
        $text .= "2. MPM Polbeng (Legislatif)\n";
        $text .= "3. Himpunan Mahasiswa Jurusan (HMJ)\n\n";
        $text .= "👉 Akses Bilik Suara: " . config('app.url') . "/login\n";
        $text .= "Mari berpartisipasi dan sukseskan pesta demokrasi kampus kita! 🎓🇮🇩";

        return $text;
    }
}
