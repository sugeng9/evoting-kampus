@extends('layouts.app')

@section('title', 'Grafik Quick Count Real-Time - KPU Polbeng')

@section('content')
<div class="space-y-8 w-full">

    <!-- Header Panel Quick Count Admin -->
    <div class="bg-gradient-to-r from-navy-950 via-slate-900 to-navy-900 rounded-3xl p-6 sm:p-8 text-white shadow-2xl relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative z-10">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                        <span>Live Quick Count Grafik Engine</span>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $setting->is_quick_count_public ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-amber-500/20 text-amber-300 border border-amber-500/30' }}">
                        {{ $setting->is_quick_count_public ? '🔓 Terbuka untuk Publik' : '🔒 Khusus Internal Panitia' }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Pusat Grafik &amp; Analisis Perolehan Suara</h1>
                <p class="text-xs text-slate-300 mt-1">Visualisasi Interaktif Rekapitulasi Suara 10 Organisasi Mahasiswa Politeknik Negeri Bengkalis</p>
            </div>

            <!-- Header Action Controls -->
            <div class="flex flex-wrap items-center gap-2.5">
                <form action="{{ route('admin.quick_count.toggle') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3.5 py-2.5 rounded-xl font-bold text-xs transition flex items-center space-x-2 {{ $setting->is_quick_count_public ? 'bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40' : 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-md' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>{{ $setting->is_quick_count_public ? 'Tutup Akses Publik' : 'Buka Akses Publik' }}</span>
                    </button>
                </form>

                <a href="{{ route('admin.reports.index') }}" class="px-3.5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition flex items-center space-x-1.5 shadow-md shadow-purple-600/30">
                    <svg class="w-4 h-4 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>📑 Berita Acara &amp; Cetak Laporan Tetap</span>
                </a>

                <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center space-x-1.5 border border-slate-700">
                    <span>&larr; Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Baris Status & Polling -->
        <div class="mt-6 pt-4 border-t border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-400">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Auto-refresh real-time aktif (setiap 4 detik)</span>
            </div>
            <div class="flex items-center space-x-3">
                <span class="font-mono text-slate-300" id="syncStatus">Diperbarui: {{ now()->timezone('Asia/Jakarta')->translatedFormat('H:i:s') }} WIB</span>
                <button type="button" onclick="fetchLiveData()" class="text-emerald-400 hover:text-emerald-300 underline font-semibold">
                    Segarkan Sekarang
                </button>
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik Utama -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total DPT Mahasiswa</span>
            <div class="text-2xl font-black text-slate-900 mt-2 font-mono" id="statTotalDpt">
                {{ number_format($results['total_dpt']) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Pemilih Terdaftar di 8 Jurusan</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-emerald-200 bg-emerald-50/20 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Mahasiswa Sudah Memilih</span>
            <div class="text-2xl font-black text-emerald-600 mt-2 font-mono" id="statVotersVoted">
                {{ number_format($results['voters_voted']) }}
                <span class="text-xs font-semibold text-emerald-700">({{ $results['voters_voted_pct'] }}%)</span>
            </div>
            <span class="text-[11px] text-emerald-700 font-medium mt-1 block">Telah Menggunakan Hak Suara</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-amber-200 bg-amber-50/20 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-700">Mahasiswa Belum Memilih</span>
            <div class="text-2xl font-black text-amber-600 mt-2 font-mono" id="statVotersNotVoted">
                {{ number_format($results['voters_not_voted']) }}
                <span class="text-xs font-semibold text-amber-700">({{ $results['voters_not_voted_pct'] }}%)</span>
            </div>
            <span class="text-[11px] text-amber-700 font-medium mt-1 block">Belum Menggunakan Hak Suara</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Partisipasi Suara</span>
            <div class="text-2xl font-black text-indigo-600 mt-2 font-mono" id="statTotalParticipations">
                {{ number_format($results['total_participations']) }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Presensi Surat Suara Sah Masuk</span>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Organisasi Aktif</span>
            <div class="text-2xl font-black text-purple-600 mt-2 font-mono">
                {{ count($results['categories']) }} Ormawa
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">BEM, MPM, &amp; 8 HMJ Polbeng</span>
        </div>
    </div>

    <!-- Grafik Komparasi Utama: Bar Chart Partisipasi Seluruh 10 Ormawa -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-black text-slate-900 tracking-tight flex items-center space-x-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Grafik Batang: Perolehan Suara Masuk di 10 Organisasi Mahasiswa</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Membandingkan total suara pemilih yang masuk di setiap bilik suara pemilihan</p>
            </div>
            <div class="flex items-center space-x-2 text-xs font-bold text-slate-500">
                <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                <span>Suara Masuk</span>
            </div>
        </div>

        <div class="h-72 sm:h-80 w-full relative">
            <canvas id="overviewBarChart"></canvas>
        </div>
    </div>

    <!-- Filter Tab Kategori Ormawa -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2 text-xs font-bold">
            <button type="button" onclick="filterCategoryCards('all')" id="btnFilterAll" class="filter-tab-btn active px-4 py-2 rounded-xl bg-navy-900 text-white shadow-sm transition">
                🌟 Semua 10 Ormawa
            </button>
            <button type="button" onclick="filterCategoryCards('BEM')" id="btnFilterBem" class="filter-tab-btn px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition">
                🏛️ BEM Polbeng
            </button>
            <button type="button" onclick="filterCategoryCards('MPM')" id="btnFilterMpm" class="filter-tab-btn px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition">
                ⚖️ MPM Polbeng (Ketua Umum)
            </button>
            <button type="button" onclick="filterCategoryCards('HMJ')" id="btnFilterHmj" class="filter-tab-btn px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition">
                🎓 8 Himpunan Jurusan (HMJ)
            </button>
        </div>

        <div class="text-xs text-slate-400">
            Menampilkan: <strong class="text-slate-800" id="filterCountLabel">{{ count($results['categories']) }} Organisasi</strong>
        </div>
    </div>

    <!-- Grid Kartu Donut Chart Per Kategori -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="categoryCardsGrid">
        @foreach($results['categories'] as $cat)
            @php
                $pillar = $cat['pillar_badge'];
                $pillarType = $cat['pillar_type'];
                $candidates = $cat['candidates'];
                $leadId = $cat['leader_candidate_id'];
            @endphp
            <div class="category-card bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm hover:shadow-md transition space-y-5"
                 data-pillar="{{ $pillarType }}"
                 data-catid="{{ $cat['category_id'] }}">
                
                <!-- Card Header -->
                <div class="flex items-start justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="px-2.5 py-0.5 rounded-lg bg-navy-900 text-white font-mono text-[10px] font-bold">
                                {{ $cat['category_code'] }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $pillar['badge_color'] }}">
                                {{ $pillar['tag'] }}
                            </span>
                        </div>
                        <h4 class="text-base font-black text-slate-900 tracking-tight leading-snug">
                            {{ $cat['category_name'] }}
                        </h4>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $cat['study_program'] ? 'Khusus Mahasiswa: ' . $cat['study_program'] : 'Terbuka Seluruh Mahasiswa Polbeng' }}
                        </p>
                    </div>

                    <!-- Status Pill -->
                    @if(!empty($cat['is_ended']))
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-900 border border-emerald-300 flex items-center space-x-1 whitespace-nowrap">
                            <svg class="w-3 h-3 text-emerald-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                            <span>🔒 HASIL TETAP (WAKTU HABIS)</span>
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-100 text-amber-900 border border-amber-300 flex items-center space-x-1 whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>⏳ HASIL SEMENTARA (BERJALAN)</span>
                        </span>
                    @endif
                </div>

                <!-- Turnout & Rekapitulasi Pemilih (Sudah vs Belum) -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2 text-xs">
                    <div class="flex flex-wrap items-center justify-between gap-1">
                        <span class="font-mono text-emerald-700 font-bold" id="cat-voted-label-{{ $cat['category_id'] }}">
                            Sudah: {{ number_format($cat['total_votes']) }} ({{ $cat['turnout_percentage'] }}%)
                        </span>
                        <span class="font-mono text-amber-700 font-bold" id="cat-not-voted-label-{{ $cat['category_id'] }}">
                            Belum: {{ number_format($cat['not_voted_count']) }} ({{ $cat['not_voted_percentage'] }}%)
                        </span>
                    </div>
                    <div class="w-full bg-amber-100 rounded-full h-2.5 overflow-hidden flex">
                        <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-2.5 transition-all duration-700 ease-out"
                             id="turnout-bar-{{ $cat['category_id'] }}"
                             style="width: {{ min(100, $cat['turnout_percentage']) }}%"></div>
                        <div class="bg-amber-400 h-2.5 transition-all duration-700 ease-out"
                             id="not-voted-bar-{{ $cat['category_id'] }}"
                             style="width: {{ min(100, $cat['not_voted_percentage']) }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <span>Basis DPT: <strong class="text-slate-700 font-mono">{{ number_format($cat['category_dpt']) }}</strong></span>
                        <span id="turnout-label-{{ $cat['category_id'] }}" class="text-slate-600 font-medium">Partisipasi: <strong class="text-emerald-600">{{ $cat['turnout_percentage'] }}%</strong></span>
                    </div>
                </div>

                <!-- Donut Chart & Legend Visual -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center pt-2">
                    <!-- Donut Canvas -->
                    <div class="sm:col-span-5 h-44 w-full flex items-center justify-center relative">
                        <canvas id="chart-cat-{{ $cat['category_id'] }}"></canvas>
                        <!-- Center Label -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Suara</span>
                            <span class="text-lg font-black text-slate-900 font-mono" id="donut-center-{{ $cat['category_id'] }}">
                                {{ number_format($cat['total_votes']) }}
                            </span>
                        </div>
                    </div>

                    <!-- Candidates List -->
                    <div class="sm:col-span-7 space-y-3">
                        @foreach($candidates as $idx => $cand)
                            @php
                                $isLeader = ($cand['id'] === $leadId && $cand['total_votes'] > 0);
                                $colorHex = $idx === 0 ? '#10b981' : ($idx === 1 ? '#6366f1' : '#f59e0b');
                                $bgClass = $idx === 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-indigo-50 border-indigo-200';
                            @endphp
                            <div class="p-3 rounded-2xl border transition {{ $isLeader ? $bgClass . ' shadow-sm' : 'bg-slate-50 border-slate-200' }} relative"
                                 id="cand-box-{{ $cand['id'] }}">
                                @if($isLeader)
                                    <span class="absolute -top-2 right-2 px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-wider {{ !empty($cat['is_ended']) ? 'bg-emerald-600 text-white' : 'bg-amber-600 text-white' }} shadow-sm flex items-center space-x-1" id="badge-lead-{{ $cand['id'] }}">
                                        <span>{{ !empty($cat['is_ended']) ? '👑 Pemenang Terpilih (Hasil Tetap)' : '⚡ Unggul Sementara' }}</span>
                                    </span>
                                @endif

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2.5">
                                        <span class="w-7 h-7 rounded-lg text-white font-mono font-black text-xs flex items-center justify-center shadow-sm" style="background-color: {{ $colorHex }}">
                                            0{{ $cand['number'] }}
                                        </span>
                                        <div>
                                            <h5 class="text-xs font-black text-slate-900 leading-tight">{{ $cand['leader_name'] }}</h5>
                                            @if($cand['vice_leader_name'])
                                                <p class="text-[10px] text-slate-500">&amp; {{ $cand['vice_leader_name'] }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <div class="text-base font-black font-mono" style="color: {{ $colorHex }}" id="pct-{{ $cand['id'] }}">
                                            {{ $cand['percentage'] }}%
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono" id="votes-{{ $cand['id'] }}">
                                            {{ number_format($cand['total_votes']) }} suara
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        @endforeach
    </div>

</div>
@endsection

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    // Palet Warna Calon
    const candidateColors = ['#10b981', '#6366f1', '#f59e0b', '#ec4899', '#06b6d4'];
    const candidateColorsHover = ['#059669', '#4f46e5', '#d97706', '#db2777', '#0891b2'];

    // Simpan instance chart per kategori
    const donutCharts = {};
    let overviewChartInstance = null;

    // Data Awal dari Server Blade
    const initialCategoriesData = @json($results['categories']);

    // Inisialisasi Chart saat DOM Siap
    document.addEventListener('DOMContentLoaded', function () {
        initOverviewBarChart(initialCategoriesData);
        initCategoryDonutCharts(initialCategoriesData);

        // Polling Real-Time setiap 4 detik
        setInterval(fetchLiveData, 4000);
    });

    /**
     * Inisialisasi Grafik Batang Overview Seluruh 10 Ormawa
     */
    function initOverviewBarChart(categories) {
        const ctx = document.getElementById('overviewBarChart').getContext('2d');
        const labels = categories.map(c => {
            // Label ringkas
            if (c.pillar_type === 'BEM') return 'BEM';
            if (c.pillar_type === 'MPM') return 'MPM';
            return c.category_code.replace('KAHIM_', '').replace('_2026', '');
        });

        const votesData = categories.map(c => c.total_votes);
        const dptData = categories.map(c => c.category_dpt);

        overviewChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Suara Masuk',
                        data: votesData,
                        backgroundColor: '#10b981',
                        borderRadius: 8,
                        maxBarThickness: 36,
                    },
                    {
                        label: 'Target DPT',
                        data: dptData,
                        backgroundColor: '#e2e8f0',
                        borderRadius: 8,
                        maxBarThickness: 36,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'inherit', size: 11, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ` ${ctx.dataset.label}: ${ctx.raw} pemilih`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'inherit', size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'inherit', size: 11, weight: 'bold' } }
                    }
                }
            }
        });
    }

    /**
     * Inisialisasi Donut Chart untuk Setiap Kategori
     */
    function initCategoryDonutCharts(categories) {
        categories.forEach(cat => {
            const canvas = document.getElementById('chart-cat-' + cat.category_id);
            if (!canvas) return;

            const labels = cat.candidates.map(c => '0' + c.number + ' ' + c.leader_name);
            let data = cat.candidates.map(c => c.total_votes);

            // Jika belum ada suara yang masuk, render placeholder abu-abu tipis
            const totalVotes = cat.total_votes;
            const isEmpty = (totalVotes === 0);

            const chart = new Chart(canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: isEmpty ? ['Belum ada suara'] : labels,
                    datasets: [{
                        data: isEmpty ? [1] : data,
                        backgroundColor: isEmpty ? ['#f1f5f9'] : candidateColors.slice(0, cat.candidates.length),
                        hoverBackgroundColor: isEmpty ? ['#e2e8f0'] : candidateColorsHover.slice(0, cat.candidates.length),
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: !isEmpty,
                            callbacks: {
                                label: function (ctx) {
                                    const val = ctx.raw;
                                    const pct = totalVotes > 0 ? ((val / totalVotes) * 100).toFixed(1) : 0;
                                    return ` ${val} suara (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });

            donutCharts[cat.category_id] = chart;
        });
    }

    /**
     * Polling Data Live via AJAX API Tanpa Kedip / Refresh Halaman
     */
    function fetchLiveData() {
        fetch('{{ route("api.quick_count.live") }}')
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' && res.data) {
                    updateDashboardData(res.data);
                    document.getElementById('syncStatus').innerText = 'Diperbarui: ' + res.last_updated + ' WIB';
                }
            })
            .catch(err => {
                console.warn('Gagal sinkronisasi quick count:', err);
            });
    }

    /**
     * Perbarui UI dan Chart.js dengan Data Baru
     */
    function updateDashboardData(data) {
        // 1. Update Kartu Ringkasan
        if (data.total_dpt !== undefined && document.getElementById('statTotalDpt')) {
            document.getElementById('statTotalDpt').innerText = data.total_dpt.toLocaleString('id-ID');
        }
        if (data.voters_voted !== undefined && document.getElementById('statVotersVoted')) {
            document.getElementById('statVotersVoted').innerHTML = `${data.voters_voted.toLocaleString('id-ID')} <span class="text-xs font-semibold text-emerald-700">(${data.voters_voted_pct}%)</span>`;
        }
        if (data.voters_not_voted !== undefined && document.getElementById('statVotersNotVoted')) {
            document.getElementById('statVotersNotVoted').innerHTML = `${data.voters_not_voted.toLocaleString('id-ID')} <span class="text-xs font-semibold text-amber-700">(${data.voters_not_voted_pct}%)</span>`;
        }
        if (data.total_participations !== undefined && document.getElementById('statTotalParticipations')) {
            document.getElementById('statTotalParticipations').innerText = data.total_participations.toLocaleString('id-ID');
        }

        // 2. Update Bar Chart Utama
        if (overviewChartInstance && data.categories) {
            overviewChartInstance.data.datasets[0].data = data.categories.map(c => c.total_votes);
            overviewChartInstance.data.datasets[1].data = data.categories.map(c => c.category_dpt);
            overviewChartInstance.update('none'); // Update mulus tanpa flicker
        }

        // 3. Update Donut Chart & Nilai Setiap Kategori
        if (data.categories) {
            data.categories.forEach(cat => {
                const catId = cat.category_id;

                // Update Turnout & Rekapitulasi Pemilih
                const turnoutLabel = document.getElementById('turnout-label-' + catId);
                const turnoutBar = document.getElementById('turnout-bar-' + catId);
                if (turnoutLabel) {
                    turnoutLabel.innerHTML = `Partisipasi: <strong class="text-emerald-600">${cat.turnout_percentage}%</strong>`;
                }
                if (turnoutBar) {
                    turnoutBar.style.width = Math.min(100, cat.turnout_percentage) + '%';
                }

                const votedLabel = document.getElementById('cat-voted-label-' + catId);
                if (votedLabel) {
                    votedLabel.innerText = `Sudah: ${cat.total_votes.toLocaleString('id-ID')} (${cat.turnout_percentage}%)`;
                }
                const notVotedLabel = document.getElementById('cat-not-voted-label-' + catId);
                if (notVotedLabel) {
                    notVotedLabel.innerText = `Belum: ${cat.not_voted_count.toLocaleString('id-ID')} (${cat.not_voted_percentage}%)`;
                }
                const notVotedBar = document.getElementById('not-voted-bar-' + catId);
                if (notVotedBar) {
                    notVotedBar.style.width = Math.min(100, cat.not_voted_percentage) + '%';
                }

                // Update Donut Center
                const donutCenter = document.getElementById('donut-center-' + catId);
                if (donutCenter) {
                    donutCenter.innerText = cat.total_votes.toLocaleString('id-ID');
                }

                // Update Chart.js Donut Data
                const chart = donutCharts[catId];
                if (chart) {
                    const totalVotes = cat.total_votes;
                    const isEmpty = (totalVotes === 0);

                    if (isEmpty) {
                        chart.data.labels = ['Belum ada suara'];
                        chart.data.datasets[0].data = [1];
                        chart.data.datasets[0].backgroundColor = ['#f1f5f9'];
                        chart.options.plugins.tooltip.enabled = false;
                    } else {
                        chart.data.labels = cat.candidates.map(c => '0' + c.number + ' ' + c.leader_name);
                        chart.data.datasets[0].data = cat.candidates.map(c => c.total_votes);
                        chart.data.datasets[0].backgroundColor = candidateColors.slice(0, cat.candidates.length);
                        chart.options.plugins.tooltip.enabled = true;
                    }
                    chart.update('none');
                }

                // Update Elemen Calon & Badge Memimpin
                cat.candidates.forEach(cand => {
                    const pctEl = document.getElementById('pct-' + cand.id);
                    const votesEl = document.getElementById('votes-' + cand.id);
                    if (pctEl) pctEl.innerText = cand.percentage + '%';
                    if (votesEl) votesEl.innerText = cand.total_votes.toLocaleString('id-ID') + ' suara';
                });
            });
        }
    }

    /**
     * Filter Tampilan Kategori Tab
     */
    function filterCategoryCards(type) {
        // Update styling tombol tab
        document.querySelectorAll('.filter-tab-btn').forEach(btn => {
            btn.classList.remove('bg-navy-900', 'text-white', 'shadow-sm');
            btn.classList.add('bg-white', 'text-slate-700', 'border', 'border-slate-200');
        });

        const activeBtn = type === 'all' ? document.getElementById('btnFilterAll') :
                          type === 'BEM' ? document.getElementById('btnFilterBem') :
                          type === 'MPM' ? document.getElementById('btnFilterMpm') :
                          document.getElementById('btnFilterHmj');

        if (activeBtn) {
            activeBtn.classList.add('bg-navy-900', 'text-white', 'shadow-sm');
            activeBtn.classList.remove('bg-white', 'text-slate-700', 'border', 'border-slate-200');
        }

        // Filter kartu
        const cards = document.querySelectorAll('.category-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const pillar = card.getAttribute('data-pillar');
            if (type === 'all' || pillar === type) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('filterCountLabel').innerText = visibleCount + ' Organisasi';
    }
</script>
@endpush
