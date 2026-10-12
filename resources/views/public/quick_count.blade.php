@extends('layouts.app')

@section('title', 'Quick Count & Perolehan Suara Real-Time')

@section('content')
<div class="space-y-8 max-w-6xl mx-auto">

    <!-- Top Banner Quick Count -->
    <div class="bg-gradient-to-r from-navy-950 via-slate-900 to-navy-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center space-x-4">
                <x-app-logo size="lg" variant="mark" theme="dark" />
                <div>
                    <div class="inline-flex items-center space-x-2 px-3 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[11px] font-bold border border-emerald-500/30 mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span>Live Voting Count Aktif</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-heading font-black tracking-tight text-white">Hasil Rekapitulasi Suara Sementara</h1>
                    <p class="text-xs text-slate-300 font-sans mt-0.5">Rekapitulasi Suara Masuk Terenkripsi Pemira Kampus Secara Terbuka &amp; Real-Time</p>
                </div>
            </div>

            <!-- Stats Ringkasan Partisipasi Global -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full sm:w-auto">
                <div class="bg-white/10 rounded-2xl p-3.5 text-center border border-white/10 backdrop-blur-sm">
                    <span class="text-[10px] text-slate-300 uppercase font-bold block">Total DPT</span>
                    <span class="text-xl font-black text-white mt-0.5 block font-mono" id="globalTotalDPT">{{ number_format($results['total_dpt']) }}</span>
                </div>
                <div class="bg-emerald-500/20 rounded-2xl p-3.5 text-center border border-emerald-500/30 backdrop-blur-sm">
                    <span class="text-[10px] text-emerald-300 uppercase font-bold block">Sudah Memilih</span>
                    <span class="text-xl font-black text-emerald-400 mt-0.5 block font-mono" id="globalTotalVoted">
                        {{ number_format($results['voters_voted']) }} <span class="text-xs font-semibold text-emerald-300">({{ $results['voters_voted_pct'] }}%)</span>
                    </span>
                </div>
                <div class="bg-amber-500/20 rounded-2xl p-3.5 text-center border border-amber-500/30 backdrop-blur-sm">
                    <span class="text-[10px] text-amber-300 uppercase font-bold block">Belum Memilih</span>
                    <span class="text-xl font-black text-amber-400 mt-0.5 block font-mono" id="globalTotalNotVoted">
                        {{ number_format($results['voters_not_voted']) }} <span class="text-xs font-semibold text-amber-300">({{ $results['voters_not_voted_pct'] }}%)</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
            <span class="flex items-center space-x-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Sinkronisasi otomatis setiap 4 detik</span>
            </span>
            <span id="lastUpdatedTime" class="font-mono text-slate-300">Diperbarui: {{ now()->timezone('Asia/Jakarta')->format('H:i:s') }} WIB</span>
        </div>
    </div>

    <!-- Hasil Per Kategori -->
    @foreach($results['categories'] as $catData)
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            
            <!-- Category Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-black text-slate-900 tracking-tight">{{ $catData['category_name'] }}</h2>
                        @if(!empty($catData['is_ended']))
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-900 border border-emerald-300 flex items-center space-x-1">
                                <svg class="w-3 h-3 text-emerald-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/></svg>
                                <span>🔒 HASIL TETAP (WAKTU HABIS)</span>
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-amber-100 text-amber-900 border border-amber-300 flex items-center space-x-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>⏳ HASIL SEMENTARA (BERJALAN)</span>
                            </span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-400 mt-1 flex flex-wrap items-center gap-x-3">
                        <span>Basis DPT: <strong class="text-slate-700 font-mono">{{ number_format($catData['category_dpt']) }} pemilih</strong></span>
                        @if($catData['start_at'] || $catData['end_at'])
                            <span>&bull;</span>
                            <span>{{ !empty($catData['is_ended']) ? 'Selesai pada: ' : 'Batas Waktu: ' }}<strong class="text-slate-700">{{ $catData['end_at'] ?: 'Menunggu Jadwal' }}</strong></span>
                        @endif
                    </div>
                </div>
                <div class="text-left sm:text-right">
                    <span class="text-xs text-slate-400 font-medium">Partisipasi Bilik Suara:</span>
                    <div class="text-base font-extrabold text-emerald-600 font-mono" id="turnout-{{ $catData['category_id'] }}">{{ $catData['turnout_percentage'] }}%</div>
                </div>
            </div>

            <!-- Transparansi Kategori: Sudah Memilih vs Belum Memilih -->
            @if(!empty($catData['is_aklamasi']))
                @php
                    $aklamasiWinner = $catData['aklamasi_candidate'] ?? ($catData['candidates'][0] ?? null);
                @endphp
                <div class="p-6 rounded-2xl bg-gradient-to-r from-amber-50 via-yellow-50 to-amber-50 border-2 border-amber-300 space-y-4">
                    <div class="flex items-center space-x-3">
                        <span class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold text-xl shadow-md">
                            🏆
                        </span>
                        <div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-amber-200 text-amber-900 uppercase tracking-wider">
                                Ketetapan Aklamasi (Calon Tunggal)
                            </span>
                            <h3 class="text-base font-black text-slate-900 mt-0.5">
                                Terpilih: {{ $aklamasiWinner['leader_name'] ?? 'Calon Tunggal' }}
                                @if(!empty($aklamasiWinner['vice_leader_name']))
                                    &amp; {{ $aklamasiWinner['vice_leader_name'] }}
                                @endif
                            </h3>
                        </div>
                    </div>
                    <p class="text-xs text-amber-900/90 leading-relaxed bg-white/80 p-3.5 rounded-xl border border-amber-200">
                        Berdasarkan Keputusan Pleno KPR (Komisi Pemilihan Raya) Politeknik Negeri Bengkalis (Opsi Aklamasi), kategori pemilihan ini hanya memiliki <strong>1 Calon Tunggal</strong> sehingga secara sah ditetapkan sebagai <strong>Ketua Terpilih</strong> tanpa pemungutan suara di bilik suara digital.
                    </p>
                </div>
            @elseif(!empty($catData['is_nihil']))
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 text-center text-xs text-slate-500">
                    <span class="font-bold block text-slate-700">⚠️ Kategori Nihil (0 Pasangan Calon Terdaftar)</span>
                    <span>Pemilihan untuk kategori ini ditiadakan/ditunda dan diserahkan ke Kongres Mahasiswa.</span>
                </div>
            @else
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Rekapitulasi:</span>
                        <span class="font-mono text-emerald-700 font-bold" id="cat-voted-label-{{ $catData['category_id'] }}">
                            Sudah Memilih: {{ number_format($catData['total_votes']) }} ({{ $catData['turnout_percentage'] }}%)
                        </span>
                        <span class="text-slate-300">&bull;</span>
                        <span class="font-mono text-amber-700 font-bold" id="cat-not-voted-label-{{ $catData['category_id'] }}">
                            Belum Memilih: {{ number_format($catData['not_voted_count']) }} ({{ $catData['not_voted_percentage'] }}%)
                        </span>
                    </div>
                    <div class="w-full sm:w-56 bg-amber-100 rounded-full h-2.5 overflow-hidden flex flex-shrink-0">
                        <div class="bg-emerald-500 h-2.5 transition-all duration-500" id="cat-voted-bar-{{ $catData['category_id'] }}" style="width: {{ $catData['turnout_percentage'] }}%"></div>
                        <div class="bg-amber-400 h-2.5 transition-all duration-500" id="cat-not-voted-bar-{{ $catData['category_id'] }}" style="width: {{ $catData['not_voted_percentage'] }}%"></div>
                    </div>
                </div>

                <!-- Candidate Bars & Results -->
                <div class="grid grid-cols-1 gap-6">
                    @foreach($catData['candidates'] as $cand)
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3 hover:border-slate-300 transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <span class="w-10 h-10 rounded-xl bg-navy-900 text-white font-black text-sm flex items-center justify-center shadow-md">
                                        0{{ $cand['number'] }}
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $cand['leader_name'] }}</h3>
                                        @if($cand['vice_leader_name'])
                                            <p class="text-xs text-slate-500">&amp; {{ $cand['vice_leader_name'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="text-right">
                                    <span class="text-xl font-black text-emerald-600 font-mono" id="percentage-{{ $cand['id'] }}">{{ $cand['percentage'] }}%</span>
                                    <span class="text-xs text-slate-400 block font-mono" id="votes-{{ $cand['id'] }}">{{ number_format($cand['total_votes']) }} suara</span>
                                </div>
                            </div>

                            <!-- Progress Bar Visual Interaktif -->
                            <div class="w-full bg-slate-200 rounded-full h-4 overflow-hidden p-0.5">
                                <div class="bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-600 h-3 rounded-full transition-all duration-700 ease-out" 
                                     id="bar-{{ $cand['id'] }}"
                                     style="width: {{ $cand['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    @endforeach

</div>
@endsection

@push('scripts')
<script>
    // Live Polling setiap 5 detik untuk memperbarui perolehan suara tanpa reload halaman
    setInterval(function() {
        fetch("{{ route('api.quick_count.live') }}")
            .then(res => {
                if (res.ok) return res.json();
                throw new Error('Quick count tidak dapat diakses.');
            })
            .then(response => {
                if (response.status === 'success' && response.data) {
                    const data = response.data;
                    document.getElementById('lastUpdatedTime').innerText = 'Diperbarui: ' + response.last_updated + ' WIB';
                    
                    if (document.getElementById('globalTotalDPT') && data.total_dpt !== undefined) {
                        document.getElementById('globalTotalDPT').innerText = data.total_dpt.toLocaleString('id-ID');
                    }
                    if (document.getElementById('globalTotalVoted') && data.voters_voted !== undefined) {
                        document.getElementById('globalTotalVoted').innerHTML = `${data.voters_voted.toLocaleString('id-ID')} <span class="text-xs font-semibold text-emerald-300">(${data.voters_voted_pct}%)</span>`;
                    }
                    if (document.getElementById('globalTotalNotVoted') && data.voters_not_voted !== undefined) {
                        document.getElementById('globalTotalNotVoted').innerHTML = `${data.voters_not_voted.toLocaleString('id-ID')} <span class="text-xs font-semibold text-amber-300">(${data.voters_not_voted_pct}%)</span>`;
                    }

                    if (data.categories) {
                        data.categories.forEach(cat => {
                            const turnoutEl = document.getElementById('turnout-' + cat.category_id);
                            if (turnoutEl) turnoutEl.innerText = cat.turnout_percentage + '%';

                            const votedLabel = document.getElementById('cat-voted-label-' + cat.category_id);
                            if (votedLabel) votedLabel.innerText = `Sudah Memilih: ${cat.total_votes.toLocaleString('id-ID')} (${cat.turnout_percentage}%)`;

                            const notVotedLabel = document.getElementById('cat-not-voted-label-' + cat.category_id);
                            if (notVotedLabel) notVotedLabel.innerText = `Belum Memilih: ${cat.not_voted_count.toLocaleString('id-ID')} (${cat.not_voted_percentage}%)`;

                            const votedBar = document.getElementById('cat-voted-bar-' + cat.category_id);
                            if (votedBar) votedBar.style.width = cat.turnout_percentage + '%';

                            const notVotedBar = document.getElementById('cat-not-voted-bar-' + cat.category_id);
                            if (notVotedBar) notVotedBar.style.width = cat.not_voted_percentage + '%';

                            if (cat.candidates) {
                                cat.candidates.forEach(cand => {
                                    const percentEl = document.getElementById('percentage-' + cand.id);
                                    if (percentEl) percentEl.innerText = cand.percentage + '%';

                                    const votesEl = document.getElementById('votes-' + cand.id);
                                    if (votesEl) votesEl.innerText = cand.total_votes.toLocaleString('id-ID') + ' suara';

                                    const barEl = document.getElementById('bar-' + cand.id);
                                    if (barEl) barEl.style.width = cand.percentage + '%';
                                });
                            }
                        });
                    }
                }
            })
            .catch(err => console.log('Live polling paused'));
    }, 5000);
</script>
@endpush
