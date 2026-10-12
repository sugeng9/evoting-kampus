@extends('layouts.app')

@section('title', 'Dashboard Pemilih - Bilik Suara')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto">

    <!-- ======================================================== -->
    <!-- 1. KREDENSIAL RESMI PEMILIH (INSTITUTIONAL HEADER)       -->
    <!-- ======================================================== -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-7 shadow-sm">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            
            <!-- Identitas Mahasiswa -->
            <div class="flex items-start sm:items-center space-x-3.5 sm:space-x-4 min-w-0 w-full lg:w-auto">
                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl bg-slate-900 text-white font-mono font-bold text-lg sm:text-2xl flex items-center justify-center flex-shrink-0 border border-slate-800 shadow-sm">
                    {{ strtoupper(substr($voter->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-lg sm:text-2xl font-bold tracking-tight text-slate-900 truncate">{{ $voter->name }}</h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 flex-shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            <span>DPT Sah Terverifikasi</span>
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 break-words">
                        NIM <span class="font-mono font-semibold text-slate-800">{{ $voter->nim }}</span>
                        <span class="text-slate-300 mx-1">&bull;</span>
                        <span class="text-slate-700 font-medium">{{ $voter->study_program }}</span>
                        @if($voter->angkatan)
                            <span class="text-slate-300 mx-1">&bull;</span>
                            <span class="text-slate-600">Angkatan {{ $voter->angkatan }}</span>
                        @endif
                        <span class="text-slate-300 mx-1">&bull;</span>
                        <span class="text-slate-600">{{ $voter->faculty }}</span>
                    </p>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 mt-2 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $setting->isOpen() ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            <span class="font-medium text-slate-700">{{ $setting->isOpen() ? 'Sesi Pemira Dibuka' : 'Sesi Pemira Ditutup' }}</span>
                        </span>
                        <span class="text-slate-300 hidden sm:inline">&bull;</span>
                        <span class="w-full sm:w-auto">Batas Waktu: <strong class="text-slate-800 font-semibold">{{ $setting->voting_ends_at ? $setting->voting_ends_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Hingga Sesi Ditutup Panitia' }}</strong></span>
                    </div>
                </div>
            </div>

            <!-- Progres Bilik Suara Mahasiswa -->
            <div class="w-full lg:w-auto bg-slate-50 border border-slate-200/80 rounded-xl p-4 sm:p-5 flex flex-col justify-between min-w-full sm:min-w-[240px]">
                <div class="flex items-center justify-between text-xs text-slate-500 font-medium mb-1">
                    <span>Partisipasi Hak Suara</span>
                    <span class="font-mono font-bold text-slate-900">{{ $totalVoted }} dari {{ $totalEligible }} Bilik</span>
                </div>
                
                <!-- Progress Bar -->
                @php
                    $progressPct = $totalEligible > 0 ? round(($totalVoted / $totalEligible) * 100) : 0;
                @endphp
                <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden my-1.5">
                    <div class="bg-slate-900 h-2 rounded-full transition-all duration-500" style="width: {{ $progressPct }}%"></div>
                </div>

                <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1">
                    <span>{{ $allCompleted ? 'Hak suara telah lengkap digunakan' : ($totalEligible - $totalVoted) . ' bilik suara tersisa' }}</span>
                    @if(isset($aklamasiCount) && $aklamasiCount > 0)
                        <span class="font-semibold text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200">
                            {{ $aklamasiCount }} Aklamasi
                        </span>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- ======================================================== -->
    <!-- 2. LAYOUT UTAMA (KOLOM INFORMASI & KOLOM BILIK SUARA)    -->
    <!-- ======================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- ==================================================== -->
        <!-- KOLOM KIRI (SIDEBAR INFORMASI & REGULASI RESMI)      -->
        <!-- Pada mobile tampil di bawah surat suara (order-2)    -->
        <!-- ==================================================== -->
        <div class="lg:col-span-4 order-2 lg:order-1 space-y-6 lg:sticky lg:top-24">
            
            <!-- Banner Tautan Live Quick Count -->
            <a href="{{ route('quick_count') }}" class="group flex items-center justify-between p-4 bg-white hover:bg-slate-50 rounded-2xl border border-slate-200 shadow-sm transition">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center flex-shrink-0 border border-slate-200 group-hover:bg-slate-900 group-hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Penghitungan Suara</span>
                        <span class="text-xs font-bold text-slate-900 group-hover:text-slate-700">Lihat Live Quick Count Publik</span>
                    </div>
                </div>
                <span class="text-xs font-mono text-slate-400 group-hover:text-slate-900 transition">&rarr;</span>
            </a>

            <!-- Regulasi Alokasi 3 Hak Suara Mahasiswa -->
            @php
                $hmjInfo = collect($categoriesData)->first(fn($i) => $i['category']->getPillarType() === 'HMJ');
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Ketetapan KPR (Komisi Pemilihan Raya)</span>
                    <h3 class="text-sm font-bold text-slate-900">Alokasi 3 Pilar Hak Suara</h3>
                </div>

                <div class="space-y-2.5 text-xs">
                    <!-- Pilar 1 -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start space-x-3">
                        <span class="w-5 h-5 rounded-md bg-slate-900 text-white font-mono text-[11px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                        <div>
                            <strong class="text-slate-900 block font-semibold">BEM Politeknik Negeri Bengkalis</strong>
                            <span class="text-slate-500 text-[11px] leading-tight block">Presiden &amp; Wakil Presiden Mahasiswa</span>
                        </div>
                    </div>

                    <!-- Pilar 2 -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start space-x-3">
                        <span class="w-5 h-5 rounded-md bg-slate-900 text-white font-mono text-[11px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                        <div>
                            <strong class="text-slate-900 block font-semibold">MPM Politeknik Negeri Bengkalis</strong>
                            <span class="text-slate-500 text-[11px] leading-tight block">Ketua Umum Badan Legislatif Kampus</span>
                        </div>
                    </div>

                    <!-- Pilar 3 -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start space-x-3">
                        <span class="w-5 h-5 rounded-md bg-slate-900 text-white font-mono text-[11px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                        <div>
                            <strong class="text-slate-900 block font-semibold">HMJ ({{ $voter->study_program }})</strong>
                            @if($hmjInfo && $hmjInfo['is_aklamasi'])
                                <span class="text-[11px] text-amber-700 font-semibold block">Ditetapkan Aklamasi (1 Calon Tunggal)</span>
                            @elseif($hmjInfo && $hmjInfo['is_nihil'])
                                <span class="text-[11px] text-slate-500 font-semibold block">Pemilihan Ditunda / Nihil</span>
                            @else
                                <span class="text-slate-500 text-[11px] leading-tight block">Ketua Himpunan Mahasiswa Jurusan</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transparansi Partisipasi Kampus & Jurusan -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Transparansi Pemilihan</span>
                    <h3 class="text-sm font-bold text-slate-900">Partisipasi Hak Suara</h3>
                </div>

                <!-- Kampus Polbeng -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-800">Tingkat Kampus</span>
                        <span class="font-mono text-slate-500 text-[11px]">{{ number_format($participationTransparency['global']['total_dpt']) }} DPT</span>
                    </div>

                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden flex">
                        <div class="bg-slate-900 h-2 transition-all duration-700" style="width: {{ $participationTransparency['global']['voted_pct'] }}%"></div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] font-mono text-slate-600">
                        <span>Sudah: <strong class="text-slate-900">{{ number_format($participationTransparency['global']['voted']) }}</strong> ({{ $participationTransparency['global']['voted_pct'] }}%)</span>
                        <span>Belum: {{ number_format($participationTransparency['global']['not_voted']) }}</span>
                    </div>
                </div>

                <!-- Jurusan Pemilih -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-semibold text-slate-800 truncate pr-2">{{ $participationTransparency['department']['name'] }}</span>
                        <span class="font-mono text-slate-500 text-[11px] flex-shrink-0">{{ number_format($participationTransparency['department']['total_dpt']) }} DPT</span>
                    </div>

                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden flex">
                        <div class="bg-slate-900 h-2 transition-all duration-700" style="width: {{ $participationTransparency['department']['voted_pct'] }}%"></div>
                    </div>

                    <div class="flex items-center justify-between text-[11px] font-mono text-slate-600">
                        <span>Rekan Sudah: <strong class="text-slate-900">{{ number_format($participationTransparency['department']['voted']) }}</strong> ({{ $participationTransparency['department']['voted_pct'] }}%)</span>
                        <span>Belum: {{ number_format($participationTransparency['department']['not_voted']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Tautan Verifikasi Bukti Kuitansi -->
            <a href="{{ route('verify_ballot') }}" class="group flex items-center justify-between p-4 bg-white hover:bg-slate-50 rounded-2xl border border-slate-200 shadow-sm transition">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center flex-shrink-0 border border-slate-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div>
                        <strong class="text-xs font-bold text-slate-900 block group-hover:text-slate-700">Audit Kuitansi Suara</strong>
                        <span class="text-[11px] text-slate-500">Verifikasi kode bukti suara Anda</span>
                    </div>
                </div>
                <span class="text-xs font-mono text-slate-400 group-hover:text-slate-900 transition">&rarr;</span>
            </a>

        </div>

        <!-- ==================================================== -->
        <!-- KOLOM KANAN (LEMBAR SURAT SUARA / BILIK PEMILIHAN)   -->
        <!-- Pada mobile tampil pertama kali (order-1)            -->
        <!-- ==================================================== -->
        <div class="lg:col-span-8 order-1 lg:order-2 space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-200">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Surat Suara Pemira</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tinjau profil kandidat dan gunakan hak suara Anda secara rahasia dan independen.</p>
                </div>
                <div class="text-xs font-mono text-slate-600 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm self-start sm:self-auto font-medium">
                    Partisipasi: <strong class="text-slate-900 font-bold">{{ $totalVoted }}</strong> / {{ $totalEligible }} Selesai
                </div>
            </div>

            <!-- Daftar Kategori Pemilihan -->
            <div class="space-y-6">
                @foreach($categoriesData as $item)
                    @php
                        $cat = $item['category'];
                        $hasVoted = $item['has_voted'];
                        $schedStatus = $cat->getScheduleStatus();
                        $isScheduleOpen = $cat->isScheduleOpen();
                        $targetTimestamp = $cat->getTargetTimestamp();
                        $canVote = (!$hasVoted && $setting->isOpen() && $isScheduleOpen && !empty($item['requires_voting']));
                        $pillar = $cat->getPillarBadge();
                        $isAklamasi = !empty($item['is_aklamasi']);
                        $isNihil = !empty($item['is_nihil']);
                    @endphp

                    @if($isAklamasi)
                        @php
                            $singleCand = $item['aklamasi_candidate'] ?? $cat->candidates->first();
                        @endphp
                        <!-- ======================================== -->
                        <!-- CARD KATEGORI AKLAMASI (KETETAPAN RESMI) -->
                        <!-- ======================================== -->
                        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-7 shadow-sm">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                        <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-mono text-[11px] font-bold">
                                            {{ $cat->code }}
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">
                                            {{ $pillar['tag'] }}: {{ $pillar['title'] }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">{{ $cat->name }}</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Kategori ini memiliki 1 Calon Tunggal yang memenuhi seluruh syarat verifikasi KPU Mahasiswa.
                                    </p>
                                </div>

                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-300">
                                    <span>Ketetapan Aklamasi (Sah)</span>
                                </span>
                            </div>

                            <!-- Keterangan Penetapan Sidang Pleno -->
                            <div class="my-4 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 space-y-1">
                                <span class="font-bold text-slate-900 block text-[11px] uppercase tracking-wider">Ketetapan Sidang Pleno KPU:</span>
                                <p class="text-slate-600 leading-relaxed text-[12px]">
                                    Sesuai Peraturan &amp; Tata Tertib Pemira Polbeng, calon tunggal secara sah <strong>Ditetapkan Terpilih</strong> tanpa pemungutan suara di bilik suara digital. Hak suara Anda pada kategori ini tercatat <strong>Sah &amp; Lengkap</strong> dengan mencoblos pilar lainnya.
                                </p>
                            </div>

                            <!-- Profil Calon Tunggal -->
                            @if($singleCand)
                                <div class="p-4 rounded-xl bg-slate-50/60 border border-slate-200 flex flex-col sm:flex-row items-center sm:items-start gap-4">
                                    <div class="w-16 h-16 rounded-xl bg-slate-200 border border-slate-300 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                        @if($singleCand->photo_path)
                                            <img src="{{ asset($singleCand->photo_path) }}" alt="{{ $singleCand->leader_name }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-base font-bold font-mono text-slate-600">01</span>
                                        @endif
                                    </div>
                                    <div class="flex-1 text-center sm:text-left">
                                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                            <span class="px-1.5 py-0.5 rounded bg-slate-900 text-white font-mono text-[10px] font-bold">
                                                Calon Terpilih
                                            </span>
                                            <h4 class="text-sm font-bold text-slate-900">{{ $singleCand->leader_name }}</h4>
                                            @if($singleCand->leader_nim)
                                                <span class="text-xs text-slate-500 font-mono">({{ $singleCand->leader_nim }})</span>
                                            @endif
                                        </div>
                                        @if($singleCand->vice_leader_name)
                                            <p class="text-xs text-slate-600 mt-0.5">
                                                Wakil: <strong class="text-slate-800">{{ $singleCand->vice_leader_name }}</strong>
                                                @if($singleCand->vice_leader_nim)
                                                    <span class="text-slate-400 font-mono">({{ $singleCand->vice_leader_nim }})</span>
                                                @endif
                                            </p>
                                        @endif
                                        @if($singleCand->vision)
                                            <p class="text-xs text-slate-600 italic mt-1.5 bg-white p-2.5 rounded-lg border border-slate-200">
                                                "{{ $singleCand->vision }}"
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                                <span>Mandat kepengurusan sah tercatat dalam Berita Acara Rekapitulasi KPU Polbeng.</span>
                                <span class="font-medium text-slate-700">Selesai</span>
                            </div>
                        </div>

                    @elseif($isNihil)
                        <!-- ======================================== -->
                        <!-- CARD KATEGORI NIHIL (0 PASLON)           -->
                        <!-- ======================================== -->
                        <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-7 shadow-sm">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                        <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-mono text-[11px] font-bold">
                                            {{ $cat->code }}
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">
                                            {{ $pillar['tag'] }}: {{ $pillar['title'] }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">{{ $cat->name }}</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Tidak ada calon yang mendaftar atau lolos verifikasi berkas KPU untuk jurusan ini.
                                    </p>
                                </div>
                                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 text-xs font-semibold">
                                    Pemilihan Ditunda
                                </span>
                            </div>
                            <div class="my-3 text-xs text-slate-600 leading-relaxed">
                                Mandat kepengurusan himpunan mahasiswa jurusan diserahkan kepada mekanisme Kongres Mahasiswa / Musyawarah Jurusan. Hak suara Anda tetap sah &amp; lengkap dengan memilih pilar lainnya.
                            </div>
                        </div>

                    @else
                        <!-- ======================================== -->
                        <!-- CARD KATEGORI DENGAN VOTING RESMI        -->
                        <!-- ======================================== -->
                        <div class="bg-white rounded-2xl border {{ $hasVoted ? 'border-emerald-200/90 bg-emerald-50/10' : 'border-slate-200/90 hover:border-slate-400' }} p-6 sm:p-7 shadow-sm transition">
                            
                            <!-- Header Kategori -->
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                        <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-mono text-[11px] font-bold">
                                            {{ $cat->code }}
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-slate-700 bg-slate-100 border border-slate-200">
                                            {{ $pillar['tag'] }}: {{ $pillar['title'] }}
                                        </span>
                                        <span class="text-xs text-slate-400">
                                            &bull; {{ $pillar['level'] }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">{{ $cat->name }}</h3>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        {{ $cat->description ?: 'Gunakan hak suara Anda secara bijak untuk menentukan kepemimpinan ormawa.' }}
                                    </p>
                                </div>

                                <!-- Status Badge -->
                                <div class="flex flex-col sm:items-end gap-1 flex-shrink-0">
                                    @if($hasVoted)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <svg class="w-3.5 h-3.5 text-emerald-700" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Suara Sah Telah Digunakan</span>
                                        </span>
                                    @elseif(!$setting->isOpen())
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            <span>Sesi Pemira Ditutup</span>
                                        </span>
                                    @elseif($cat->status_override === 'FORCE_CLOSED')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                                            <span>Ditutup oleh Panitia</span>
                                        </span>
                                    @elseif($schedStatus === 'OPEN')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            <span>Bilik Suara Dibuka</span>
                                        </span>
                                    @elseif($schedStatus === 'UPCOMING')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                            <span>Belum Dimulai</span>
                                        </span>
                                    @elseif($schedStatus === 'ENDED')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            <span>Waktu Pemilihan Selesai</span>
                                        </span>
                                    @endif
                                    <span class="text-[11px] text-slate-400 font-mono">
                                        {{ $cat->candidates->count() }} Pasangan Calon
                                    </span>
                                </div>
                            </div>

                            <!-- Bar Jadwal & Timer -->
                            <div class="my-4 p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 text-xs">
                                <div class="text-slate-600 space-y-0.5">
                                    <span class="font-semibold text-slate-800 block text-[11px] uppercase tracking-wider">Jadwal Sesi Pemilihan:</span>
                                    <div>
                                        Mulai: <strong class="text-slate-800">{{ $cat->start_at ? $cat->start_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Langsung Terbuka' }}</strong>
                                        <span class="text-slate-300 mx-1">&bull;</span>
                                        Berakhir: <strong class="text-slate-800">{{ $cat->end_at ? $cat->end_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Hingga Panitia Menutup' }}</strong>
                                    </div>
                                </div>

                                @if($targetTimestamp)
                                    <div class="category-timer-wrapper bg-white px-3.5 py-1.5 rounded-lg border border-slate-200 text-right flex-shrink-0 shadow-sm"
                                         data-target="{{ $targetTimestamp }}"
                                         data-status="{{ $schedStatus }}"
                                         id="timer-cat-{{ $cat->id }}">
                                        <span class="text-[10px] uppercase font-bold text-slate-500 block">
                                            {{ $schedStatus === 'OPEN' ? 'Sisa Waktu Memilih' : 'Dibuka Dalam' }}
                                        </span>
                                        <span class="font-mono font-bold text-sm text-slate-900 timer-display">
                                            -- : -- : --
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Daftar Kandidat Paslon -->
                            <div class="space-y-3">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">
                                    {{ $cat->getPillarType() === 'BEM' ? 'Pasangan Calon Kandidat' : 'Kandidat Calon Ketua' }}
                                </span>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                    @foreach($cat->candidates as $c)
                                        <div class="p-3.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition flex items-center space-x-3.5">
                                            <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex-shrink-0 overflow-hidden flex items-center justify-center">
                                                @if($c->photo_path)
                                                    <img src="{{ asset($c->photo_path) }}" alt="{{ $c->leader_name }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-sm font-bold font-mono text-slate-700">0{{ $c->candidate_number }}</span>
                                                @endif
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center space-x-1.5">
                                                    <span class="px-1.5 py-0.2 rounded bg-slate-900 text-white font-mono text-[10px] font-bold">
                                                        0{{ $c->candidate_number }}
                                                    </span>
                                                    <h4 class="text-xs font-bold text-slate-900 truncate">{{ $c->leader_name }}</h4>
                                                </div>
                                                @if($c->vice_leader_name)
                                                    <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                                        Wakil: <span class="text-slate-700 font-medium">{{ $c->vice_leader_name }}</span>
                                                    </p>
                                                @endif
                                                @if($c->vision)
                                                    <p class="text-[10px] text-slate-500 italic truncate mt-0.5">
                                                        "{{ Str::limit($c->vision, 45) }}"
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Tombol Aksi Masuk Bilik Suara -->
                            <div class="mt-5 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 text-xs">
                                <div class="text-slate-500 text-center sm:text-left">
                                    @if($canVote)
                                        <span class="text-slate-800 font-medium">Bilik suara siap digunakan. Siapkan PIN Anda untuk validasi.</span>
                                    @elseif($hasVoted)
                                        <span class="text-emerald-700 font-medium">Pilihan Anda telah tercatat sah di kotak suara digital.</span>
                                    @elseif($schedStatus === 'UPCOMING')
                                        <span>Bilik suara akan dibuka sesuai jadwal yang tertera.</span>
                                    @else
                                        <span>Sesi pemilihan tidak sedang menerima suara baru.</span>
                                    @endif
                                </div>

                                <div class="w-full sm:w-auto flex-shrink-0">
                                    @if($hasVoted)
                                        <div class="w-full sm:w-auto justify-center py-2.5 px-4 rounded-xl bg-slate-100 text-slate-600 font-medium text-xs flex items-center space-x-1.5 border border-slate-200">
                                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            <span>Selesai Memilih</span>
                                        </div>
                                    @elseif($canVote)
                                        <a href="{{ route('voter.ballot', $cat->id) }}" class="w-full sm:w-auto justify-center py-2.5 px-5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-semibold text-xs flex items-center space-x-2 transition shadow-sm">
                                            <span>Masuk ke Bilik Suara</span>
                                            <span class="font-mono text-xs">&rarr;</span>
                                        </a>
                                    @elseif($schedStatus === 'UPCOMING')
                                        <button disabled class="w-full sm:w-auto justify-center py-2.5 px-4 rounded-xl bg-slate-100 text-slate-400 font-medium text-xs cursor-not-allowed">
                                            Belum Dibuka
                                        </button>
                                    @else
                                        <button disabled class="w-full sm:w-auto justify-center py-2.5 px-4 rounded-xl bg-slate-100 text-slate-400 font-medium text-xs cursor-not-allowed">
                                            Sesi Ditutup
                                        </button>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @endif
                @endforeach
            </div>

        </div>

    </div>

    <!-- ======================================================== -->
    <!-- 3. JAMINAN KERAHASIAAN HAK SUARA (INSTITUTIONAL FOOTER)  -->
    <!-- ======================================================== -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200 text-xs text-slate-600 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center flex-shrink-0 font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-slate-800">Prinsip Kerahasiaan Hak Suara (Secret Ballot Engine)</p>
                <p class="text-[11px] text-slate-500">Pilihan suara Anda dipisahkan secara kriptografis dari data identitas NIM menggunakan pengacakan UUIDv4 anonim.</p>
            </div>
        </div>
        <a href="{{ route('verify_ballot') }}" class="font-semibold text-slate-800 hover:text-slate-900 underline flex-shrink-0">
            Audit Bukti Kuitansi &rarr;
        </a>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function padZero(num) {
        return num.toString().padStart(2, '0');
    }

    function updateCategoryTimers() {
        const now = Math.floor(Date.now() / 1000);

        document.querySelectorAll('.category-timer-wrapper').forEach(wrapper => {
            const target = parseInt(wrapper.getAttribute('data-target'));
            const status = wrapper.getAttribute('data-status');
            const display = wrapper.querySelector('.timer-display');

            if (!target || !display) return;

            const diff = target - now;

            if (diff <= 0) {
                if (status === 'UPCOMING') {
                    display.innerText = '00 : 00 : 00';
                    setTimeout(() => window.location.reload(), 2500);
                } else {
                    display.innerText = 'Waktu Selesai';
                }
                return;
            }

            const days = Math.floor(diff / 86400);
            const hours = Math.floor((diff % 86400) / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            if (days > 0) {
                display.innerText = `${days}h ${padZero(hours)}:${padZero(minutes)}:${padZero(seconds)}`;
            } else {
                display.innerText = `${padZero(hours)} : ${padZero(minutes)} : ${padZero(seconds)}`;
            }
        });
    }

    updateCategoryTimers();
    setInterval(updateCategoryTimers, 1000);
</script>
@endpush
