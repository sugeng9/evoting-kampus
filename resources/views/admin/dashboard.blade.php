@extends('layouts.app')

@section('title', 'Admin Panel - KPU Kampus')

@section('content')
<div class="space-y-8 w-full">
    
    <!-- Header Admin -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-navy-900 text-white p-6 sm:p-8 rounded-3xl shadow-xl">
        <div>
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30 mb-2">
                <span>Panitia KPU Kampus</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Pusat Kendali Pemira Digital</h1>
            <p class="text-xs text-slate-300 mt-1">Manajemen Sesi Pemilihan, Integritas DPT &amp; Audit Trail</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.voters.index') }}" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-teal-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Kelola DPT Pemilih</span>
            </a>
            <a href="{{ route('admin.candidates.index') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-emerald-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Kelola Paslon</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-indigo-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>Kelola Kategori Ormawa</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="px-4 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-purple-600/30">
                <svg class="w-4 h-4 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Berita Acara Tetap</span>
            </a>
            <a href="{{ route('admin.radar.index') }}" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition flex items-center space-x-2 shadow-sm">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                </svg>
                <span>🎯 Radar Partisipasi</span>
            </a>
            <a href="{{ route('admin.audit_logs') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Log Audit</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total DPT Mahasiswa</span>
            <div class="text-2xl font-black text-slate-900 mt-2">{{ number_format($totalDPT) }}</div>
            <span class="text-[11px] text-emerald-600 font-semibold mt-1 block">{{ number_format($activeDPT) }} DPT Aktif</span>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Suara Masuk</span>
            <div class="text-2xl font-black text-emerald-600 mt-2">{{ number_format($totalParticipations) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Presensi Terverifikasi</span>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Sesi Voting</span>
            <div class="text-xl font-black mt-2 flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full {{ $setting->status === 'OPEN' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                <span class="{{ $setting->status === 'OPEN' ? 'text-emerald-700' : 'text-rose-700' }}">{{ $setting->status }}</span>
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">{{ $setting->isOpen() ? 'Pemilih dapat mencoblos' : 'Akses bilik suara tertutup' }}</span>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Quick Count Publik</span>
            <div class="text-xl font-black mt-2 {{ $setting->is_quick_count_public ? 'text-emerald-600' : 'text-amber-600' }}">
                {{ $setting->is_quick_count_public ? 'TERBUKA' : 'DIRAHASIAKAN' }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Visibilitas Hasil Suara</span>
        </div>
    </div>

    <!-- Live Quick Count Visual Grafik Showcase -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>Visualisasi Real-Time</span>
                </div>
                <h3 class="text-xl font-black text-slate-900 tracking-tight flex items-center space-x-2">
                    <span>Grafik Perolehan Suara (Quick Count)</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Pantau perolehan suara mahasiswa secara grafis visual untuk BEM, MPM, dan seluruh 8 HMJ Jurusan.</p>
            </div>
            <div>
                <a href="{{ route('admin.quick_count') }}" class="px-4 py-2.5 rounded-xl bg-navy-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center space-x-2 shadow-md">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Buka Grafik Lengkap 10 Ormawa &rarr;</span>
                </a>
            </div>
        </div>

        <!-- Dual Chart Layout: Overview Bar + BEM & MPM Spotlight -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            
            <!-- Left: Bar Chart Komparasi 10 Ormawa -->
            <div class="lg:col-span-7 bg-slate-50/70 p-5 rounded-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-3 text-xs">
                    <span class="font-bold text-slate-700">Perbandingan Suara Masuk di 10 Ormawa</span>
                    <span class="text-[11px] text-slate-400">Target DPT vs Masuk</span>
                </div>
                <div class="h-64 relative">
                    <canvas id="dashboardBarChart"></canvas>
                </div>
            </div>

            <!-- Right: Spotlight BEM & MPM Donut Charts -->
            <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4">
                @php
                    $bemCat = collect($quickCountResults['categories'])->firstWhere('pillar_type', 'BEM');
                    $mpmCat = collect($quickCountResults['categories'])->firstWhere('pillar_type', 'MPM');
                @endphp

                <!-- BEM Card -->
                @if($bemCat)
                    <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200 flex items-center justify-between gap-4">
                        <div class="space-y-1">
                            <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                BEM Polbeng
                            </span>
                            <h4 class="text-xs font-black text-slate-900 leading-snug">Presma &amp; Wapresma</h4>
                            <div class="text-xs font-mono font-bold text-slate-700">
                                {{ number_format($bemCat['total_votes']) }} suara ({{ $bemCat['turnout_percentage'] }}%)
                            </div>
                        </div>
                        <div class="w-20 h-20 relative flex-shrink-0">
                            <canvas id="dashboardBemDonut"></canvas>
                        </div>
                    </div>
                @endif

                <!-- MPM Card -->
                @if($mpmCat)
                    <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-200 flex items-center justify-between gap-4">
                        <div class="space-y-1">
                            <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded bg-indigo-100 text-indigo-800">
                                MPM Polbeng
                            </span>
                            <h4 class="text-xs font-black text-slate-900 leading-snug">Ketua Umum MPM</h4>
                            <div class="text-xs font-mono font-bold text-slate-700">
                                {{ number_format($mpmCat['total_votes']) }} suara ({{ $mpmCat['turnout_percentage'] }}%)
                            </div>
                        </div>
                        <div class="w-20 h-20 relative flex-shrink-0">
                            <canvas id="dashboardMpmDonut"></canvas>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Panel Kontrol Sesi & Visibilitas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Saklar Status Sesi Pemira -->
        <div class="bg-white p-6 sm:p-7 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <div>
                <h3 class="text-base font-black text-slate-900">Saklar Sesi Pemilihan (Voting Switch)</h3>
                <p class="text-xs text-slate-500 mt-1">Mengubah status membuka/menutup seluruh akses bilik suara mahasiswa secara instan.</p>
            </div>

            <form action="{{ route('admin.session.status') }}" method="POST" class="flex flex-wrap items-center gap-3">
                @csrf
                <select name="status" class="py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="OPEN" {{ $setting->status === 'OPEN' ? 'selected' : '' }}>BUKA (OPEN) - Mahasiswa Dapat Memilih</option>
                    <option value="PAUSED" {{ $setting->status === 'PAUSED' ? 'selected' : '' }}>JEDA (PAUSED) - Ditangguhkan Sementara</option>
                    <option value="CLOSED" {{ $setting->status === 'CLOSED' ? 'selected' : '' }}>TUTUP (CLOSED) - Pemira Selesai</option>
                    <option value="DRAFT" {{ $setting->status === 'DRAFT' ? 'selected' : '' }}>DRAFT - Masa Uji Coba</option>
                </select>

                <button type="submit" class="px-4 py-2.5 bg-navy-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                    Terapkan Perubahan
                </button>
            </form>
        </div>

        <!-- Saklar Quick Count Publik -->
        <div class="bg-white p-6 sm:p-7 rounded-3xl border border-slate-200 shadow-sm space-y-4">
            <div>
                <h3 class="text-base font-black text-slate-900">Publikasi Quick Count</h3>
                <p class="text-xs text-slate-500 mt-1">Buka hasil rekapitulasi real-time kepada seluruh mahasiswa setelah sesi resmi selesai.</p>
            </div>

            <form action="{{ route('admin.quick_count.toggle') }}" method="POST">
                @csrf
                <button type="submit" class="w-full py-3 px-4 rounded-xl font-bold text-xs transition flex items-center justify-center space-x-2 {{ $setting->is_quick_count_public ? 'bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300' : 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-md shadow-emerald-600/20' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>{{ $setting->is_quick_count_public ? 'Tutup Quick Count Publik' : 'Buka Quick Count untuk Publik' }}</span>
                </button>
            </form>
        </div>

    </div>

    <!-- Manajemen Jadwal Waktu Pemilihan Per Kategori (Presma & HMJ) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 sm:p-7 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-navy-100 text-navy-800 text-xs font-bold mb-2">
                    <svg class="w-3.5 h-3.5 text-navy-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Jadwal Independen Presma &amp; HMJ</span>
                </div>
                <h3 class="text-lg font-black text-slate-900 tracking-tight">Atur Jadwal &amp; Waktu Pemilihan Per Kategori</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tentukan jam mulai dan jam berakhir yang berbeda untuk setiap kategori (misal: Presma pagi hari, HMJ siang hari). Bilik suara otomatis sinkron.</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.categories.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center space-x-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <span>Kelola Struktur 10 Ormawa &rarr;</span>
                </a>
                <div class="text-xs text-slate-400 bg-slate-50 px-3 py-2 rounded-xl border border-slate-200 hidden sm:block">
                    Server: <strong class="font-mono text-slate-700">{{ now()->timezone('Asia/Jakarta')->translatedFormat('H:i') }} WIB</strong>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-7 space-y-6">
            @foreach($categories as $cat)
                @php
                    $schedStatus = $cat->getScheduleStatus();
                @endphp
                <div class="border border-slate-200 rounded-2xl p-5 sm:p-6 bg-slate-50/50 hover:bg-white hover:border-emerald-300 transition shadow-sm">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 pb-5 border-b border-slate-200">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-lg bg-navy-900 text-white font-mono text-[11px] font-bold">
                                    {{ $cat->code }}
                                </span>
                                <h4 class="text-base font-black text-slate-900">{{ $cat->name }}</h4>
                            </div>
                            <p class="text-xs text-slate-500">
                                {{ $cat->description ?: 'Pemilihan kepengurusan organisasi mahasiswa.' }}
                                @if($cat->faculty_filter || $cat->study_program_filter)
                                    <span class="inline-block font-semibold text-emerald-700 ml-1">
                                        (Khusus: {{ $cat->study_program_filter ?: $cat->faculty_filter }})
                                    </span>
                                @else
                                    <span class="inline-block font-semibold text-blue-700 ml-1">
                                        (Terbuka Seluruh Mahasiswa)
                                    </span>
                                @endif
                            </p>
                        </div>

                        <!-- Status Badge Saat Ini -->
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Status Bilik Suara</span>
                                <div class="mt-0.5">
                                    @if($cat->status_override === 'FORCE_OPEN')
                                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                                            <span>PAKSA BUKA (OVERRIDE)</span>
                                        </span>
                                    @elseif($cat->status_override === 'FORCE_CLOSED')
                                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            <span>PAKSA TUTUP (OVERRIDE)</span>
                                        </span>
                                    @elseif($schedStatus === 'OPEN')
                                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>SEDANG BERLANGSUNG</span>
                                        </span>
                                    @elseif($schedStatus === 'UPCOMING')
                                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            <span>BELUM DIMULAI (AKAN DATANG)</span>
                                        </span>
                                    @elseif($schedStatus === 'ENDED')
                                        <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-200 text-slate-700 border border-slate-300">
                                            <span>TELAH BERAKHIR</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                            TIDAK AKTIF
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Pengaturan Jadwal Kategori -->
                    <form action="{{ route('admin.categories.schedule', $cat->id) }}" method="POST" class="mt-4 pt-2">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Input Waktu Mulai -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                    Waktu Mulai (WIB)
                                </label>
                                <input type="datetime-local" 
                                       name="start_at" 
                                       value="{{ $cat->start_at ? $cat->start_at->timezone('Asia/Jakarta')->format('Y-m-d\TH:i') : '' }}"
                                       class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <span class="text-[10px] text-slate-400 mt-1 block">Kosongkan jika bebas jam mulai</span>
                            </div>

                            <!-- Input Waktu Berakhir -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                    Waktu Selesai (WIB)
                                </label>
                                <input type="datetime-local" 
                                       name="end_at" 
                                       value="{{ $cat->end_at ? $cat->end_at->timezone('Asia/Jakarta')->format('Y-m-d\TH:i') : '' }}"
                                       class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <span class="text-[10px] text-slate-400 mt-1 block">Bilik suara akan otomatis tertutup</span>
                            </div>

                            <!-- Mode Override -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                    Mode Eksekusi
                                </label>
                                <select name="status_override" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                    <option value="AUTO" {{ $cat->status_override === 'AUTO' ? 'selected' : '' }}>Otomatis (Sesuai Jam)</option>
                                    <option value="FORCE_OPEN" {{ $cat->status_override === 'FORCE_OPEN' ? 'selected' : '' }}>Paksa Buka Sekarang</option>
                                    <option value="FORCE_CLOSED" {{ $cat->status_override === 'FORCE_CLOSED' ? 'selected' : '' }}>Paksa Tutup Sekarang</option>
                                </select>
                                <span class="text-[10px] text-slate-400 mt-1 block">Override mendahului timer jam</span>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-end">
                                <button type="submit" class="w-full py-2.5 px-4 bg-navy-900 hover:bg-emerald-600 text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-1.5 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Simpan Jadwal</span>
                                </button>
                            </div>
                        </div>

                        <!-- Current Schedule Info in Words -->
                        <div class="mt-3.5 flex flex-wrap items-center justify-between text-xs text-slate-500 bg-white px-3.5 py-2 rounded-xl border border-slate-200/80">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>
                                    Jadwal Aktif: 
                                    <strong class="text-slate-800 font-medium">
                                        {{ $cat->start_at ? $cat->start_at->translatedFormat('d F Y, H:i') : 'Tanpa batas awal' }}
                                    </strong>
                                    s/d 
                                    <strong class="text-slate-800 font-medium">
                                        {{ $cat->end_at ? $cat->end_at->translatedFormat('d F Y, H:i T') : 'Tanpa batas akhir' }}
                                    </strong>
                                </span>
                            </div>
                            <div class="flex items-center space-x-3 text-[11px] font-medium text-slate-400">
                                <span>{{ $cat->candidates_count }} Paslon Terdaftar</span>
                                <span>&bull;</span>
                                <span class="text-emerald-600 font-bold">{{ $cat->participations_count }} Suara Masuk</span>
                            </div>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Live Audit Log Feed -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-base font-black text-slate-900">Jejak Audit Keamanan Terkini (Security Audit Log)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Catatan kejadian sistem tanpa manipulasi (Immutable Logging)</p>
            </div>
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-6">Waktu</th>
                        <th class="py-3 px-6">Event</th>
                        <th class="py-3 px-6">Aktor</th>
                        <th class="py-3 px-6">IP Address</th>
                        <th class="py-3 px-6">Metadata</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($recentLogs as $log)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-6 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td class="py-3.5 px-6">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ str_contains($log->event_type, 'SUCCESS') ? 'bg-emerald-100 text-emerald-800' : (str_contains($log->event_type, 'FAIL') || str_contains($log->event_type, 'BLOCKED') ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-800') }}">
                                    {{ $log->event_type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-slate-700 capitalize font-sans">{{ $log->actor_type }} #{{ $log->actor_id ?: '-' }}</td>
                            <td class="py-3.5 px-6 text-slate-500">{{ $log->ip_address }}</td>
                            <td class="py-3.5 px-6 text-slate-600 truncate max-w-xs">{{ json_encode($log->payload) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">Belum ada aktivitas tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const catData = @json($quickCountResults['categories']);

        // 1. Dashboard Overview Bar Chart
        const barCanvas = document.getElementById('dashboardBarChart');
        if (barCanvas) {
            const labels = catData.map(c => {
                if (c.pillar_type === 'BEM') return 'BEM';
                if (c.pillar_type === 'MPM') return 'MPM';
                return c.category_code.replace('KAHIM_', '').replace('_2026', '');
            });
            const votes = catData.map(c => c.total_votes);
            const dpts = catData.map(c => c.category_dpt);

            new Chart(barCanvas.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Suara Masuk',
                            data: votes,
                            backgroundColor: '#10b981',
                            borderRadius: 6,
                            maxBarThickness: 24,
                        },
                        {
                            label: 'Target DPT',
                            data: dpts,
                            backgroundColor: '#e2e8f0',
                            borderRadius: 6,
                            maxBarThickness: 24,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { font: { family: 'inherit', size: 10, weight: 'bold' } }
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f8fafc' }, ticks: { font: { size: 10 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 10, weight: 'bold' } } }
                    }
                }
            });
        }

        // 2. Mini Donut BEM
        const bemCanvas = document.getElementById('dashboardBemDonut');
        if (bemCanvas) {
            const bem = catData.find(c => c.pillar_type === 'BEM');
            const data = (bem && bem.total_votes > 0) ? bem.candidates.map(c => c.total_votes) : [1];
            const colors = (bem && bem.total_votes > 0) ? ['#10b981', '#6366f1'] : ['#f1f5f9'];

            new Chart(bemCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: { legend: { display: false }, tooltip: { enabled: (bem && bem.total_votes > 0) } }
                }
            });
        }

        // 3. Mini Donut MPM
        const mpmCanvas = document.getElementById('dashboardMpmDonut');
        if (mpmCanvas) {
            const mpm = catData.find(c => c.pillar_type === 'MPM');
            const data = (mpm && mpm.total_votes > 0) ? mpm.candidates.map(c => c.total_votes) : [1];
            const colors = (mpm && mpm.total_votes > 0) ? ['#6366f1', '#f59e0b'] : ['#f1f5f9'];

            new Chart(mpmCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: data,
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: { legend: { display: false }, tooltip: { enabled: (mpm && mpm.total_votes > 0) } }
                }
            });
        }
    });
</script>
@endpush
