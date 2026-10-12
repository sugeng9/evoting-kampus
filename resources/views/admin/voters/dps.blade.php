@extends('layouts.app')

@section('title', 'Saringan DPS Kampus & Verifikasi Akademik - KPR Politeknik Negeri Bengkalis')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Panel Saringan DPS -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-navy-900 text-white p-6 sm:p-8 rounded-3xl shadow-xl">
        <div>
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold border border-indigo-500/30 mb-2">
                <span>Tahapan Pra-Pemilihan: Dari DPS Menuju DPT</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Penyaringan DPS &amp; Verifikasi Akademik</h1>
            <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                Transparansi penyaringan Data Pemilih Sementara (DPS) kampus. Mahasiswa Aktif dan Magang sah masuk DPT, sedangkan mahasiswa Cuti, DO, Wisuda, dan Bermasalah Akademik secara objektif dicoret.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.voters.import') }}" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-teal-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>📥 Import File DPS BAAK</span>
            </a>
            <a href="{{ route('admin.voters.index') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-emerald-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>📋 Buka DPT &amp; Akun Pemilih</span>
            </a>
            <form action="{{ route('admin.voters.dps_simulation') }}" method="POST" onsubmit="return confirm('Muat dataset simulasi 70 data DPS (60 Sah DPT Aktif/Magang, 10 TMS Cuti/DO/Wisuda/Kasus)?');" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-indigo-600/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>⚡ Muat Simulasi 70 DPS</span>
                </button>
            </form>
            <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Rekap Saringan</span>
            </button>
        </div>
    </div>

    <!-- Alur Kerja Lapangan: Tahapan Regulasi Pemira Politeknik Negeri Bengkalis -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center space-x-2">
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-800">Alur Penyaringan Data Pemilih Berdasarkan Regulasi Ormawa</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <!-- Tahap 1 -->
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full bg-slate-200 text-slate-700 font-bold text-[10px]">TAHAP 1</span>
                    <span class="text-slate-400 font-mono text-[11px]">BAAK Kampus</span>
                </div>
                <h3 class="font-extrabold text-slate-800 text-sm">Penyerahan Data DPS</h3>
                <p class="text-slate-500 text-[11px] leading-relaxed">
                    Pihak Rektorat/BAAK kampus menyerahkan Data Pemilih Sementara (DPS) mentah yang mencakup seluruh mahasiswa terdaftar di universitas.
                </p>
            </div>

            <!-- Tahap 2 -->
            <div class="bg-emerald-50/60 p-4 rounded-2xl border border-emerald-200 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-200 text-emerald-800 font-bold text-[10px]">TAHAP 2</span>
                    <span class="text-emerald-600 font-mono text-[11px]">KPR &amp; PanWas x Ormawa</span>
                </div>
                <h3 class="font-extrabold text-emerald-900 text-sm">Penyaringan Status Akademik</h3>
                <p class="text-emerald-700 text-[11px] leading-relaxed">
                    Panitia KPR (Komisi Pemilihan Raya) &amp; PanWas (Panitia Pengawas) memfilter status akademik resmi: <strong>Mahasiswa Aktif &amp; Magang</strong> sah ikut memilih. Mahasiswa <strong>Cuti, DO, Wisuda, dan Bermasalah Akademik</strong> dicoret (TMS).
                </p>
            </div>

            <!-- Tahap 3 -->
            <div class="bg-indigo-50/60 p-4 rounded-2xl border border-indigo-200 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-0.5 rounded-full bg-indigo-200 text-indigo-800 font-bold text-[10px]">TAHAP 3</span>
                    <span class="text-indigo-600 font-mono text-[11px]">Pleno Terkunci</span>
                </div>
                <h3 class="font-extrabold text-indigo-900 text-sm">Penetapan DPT &amp; Pendaftaran Mandiri</h3>
                <p class="text-indigo-700 text-[11px] leading-relaxed">
                    DPT disahkan dalam Berita Acara Pleno dan <strong>dikunci total dari CRUD</strong>. Mahasiswa yang sah masuk DPT langsung dapat mendaftar mandiri di portal web dengan membuat kata sandi pribadi.
                </p>
            </div>
        </div>
    </div>

    <!-- Ringkasan Hasil Saringan Akademik -->
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
        <!-- Total DPS -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Total DPS</span>
            <span class="text-xl font-black text-slate-900 mt-1 block">{{ number_format($totalDps) }}</span>
            <span class="text-[10px] text-slate-400 block">Data Mentah Kampus</span>
        </div>

        <!-- Aktif -->
        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 block">🟢 Kuliah Aktif</span>
            <span class="text-xl font-black text-emerald-700 mt-1 block">{{ number_format($aktifCount) }}</span>
            <span class="text-[10px] text-emerald-600 font-semibold block">Sah Masuk DPT</span>
        </div>

        <!-- Magang -->
        <div class="bg-white p-4 rounded-2xl border border-blue-200 bg-blue-50/20 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">🔵 Magang / MBKM</span>
            <span class="text-xl font-black text-blue-700 mt-1 block">{{ number_format($magangCount) }}</span>
            <span class="text-[10px] text-blue-600 font-semibold block">Sah Ikut E-Voting</span>
        </div>

        <!-- Cuti -->
        <div class="bg-white p-4 rounded-2xl border border-rose-200 bg-rose-50/20 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-500 block">🔴 Cuti Kuliah</span>
            <span class="text-xl font-black text-rose-600 mt-1 block">{{ number_format($cutiCount) }}</span>
            <span class="text-[10px] text-rose-600 font-semibold block">Dicoret (TMS)</span>
        </div>

        <!-- DO -->
        <div class="bg-white p-4 rounded-2xl border border-rose-200 bg-rose-50/20 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-500 block">🔴 Drop Out (DO)</span>
            <span class="text-xl font-black text-rose-600 mt-1 block">{{ number_format($doCount) }}</span>
            <span class="text-[10px] text-rose-600 font-semibold block">Dicoret (TMS)</span>
        </div>

        <!-- Wisuda -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">🎓 Wisuda / Lulus</span>
            <span class="text-xl font-black text-slate-700 mt-1 block">{{ number_format($wisudaCount) }}</span>
            <span class="text-[10px] text-slate-500 font-semibold block">Alumni (TMS)</span>
        </div>

        <!-- Skorsing / Kasus -->
        <div class="bg-white p-4 rounded-2xl border border-amber-200 bg-amber-50/20 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 block">⚠️ Skorsing / Kasus</span>
            <span class="text-xl font-black text-amber-700 mt-1 block">{{ number_format($bermasalahCount) }}</span>
            <span class="text-[10px] text-amber-600 font-semibold block">Dicoret (TMS)</span>
        </div>
    </div>

    <!-- Filter Tab & Pencarian DPS -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form action="{{ route('admin.voters.dps') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div class="sm:col-span-2">
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari NIM, Nama Mahasiswa, atau Program Studi..." 
                       class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <select name="status" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="all">Semua Status Akademik</option>
                    <option value="aktif" {{ $statusFilter === 'aktif' ? 'selected' : '' }}>🟢 Aktif (Masuk DPT)</option>
                    <option value="magang" {{ $statusFilter === 'magang' ? 'selected' : '' }}>🔵 Magang / MBKM (Masuk DPT)</option>
                    <option value="cuti" {{ $statusFilter === 'cuti' ? 'selected' : '' }}>🔴 Cuti Akademik (TMS)</option>
                    <option value="do" {{ $statusFilter === 'do' ? 'selected' : '' }}>🔴 Drop Out (TMS)</option>
                    <option value="wisuda" {{ $statusFilter === 'wisuda' ? 'selected' : '' }}>🎓 Wisuda / Alumni (TMS)</option>
                    <option value="bermasalah" {{ $statusFilter === 'bermasalah' ? 'selected' : '' }}>⚠️ Bermasalah Akademik (TMS)</option>
                </select>
            </div>

            <div class="flex items-center space-x-2">
                <button type="submit" class="w-full py-2.5 px-4 bg-navy-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                    Filter DPS
                </button>
                @if($search || ($statusFilter && $statusFilter !== 'all'))
                    <a href="{{ route('admin.voters.dps') }}" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition" title="Reset Filter">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Data Saringan DPS -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-slate-50/50">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800">Daftar Hasil Penyaringan Mahasiswa (DPS &rarr; DPT)</h3>
                <p class="text-[11px] text-slate-500">Menampilkan verifikasi kepatuhan syarat hak suara mahasiswa.</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                    {{ number_format($dptSahCount) }} DPT Sah
                </span>
                <span class="px-3 py-1 rounded-full bg-rose-100 text-rose-800 text-[11px] font-bold">
                    {{ number_format($tmsCount) }} Dicoret (TMS)
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-6">NIM</th>
                        <th class="py-3 px-6">Nama Mahasiswa</th>
                        <th class="py-3 px-6">Program Studi / Fakultas</th>
                        <th class="py-3 px-6 text-center">Status Akademik</th>
                        <th class="py-3 px-6 text-center">Hasil Saringan</th>
                        <th class="py-3 px-6 text-center">Hak Suara E-Voting</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dpsList as $item)
                        @php
                            $isEligible = $item->isEligible();
                            $academic = strtolower($item->academic_status ?? 'aktif');
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition {{ !$isEligible ? 'bg-rose-50/30' : '' }}">
                            <td class="py-4 px-6 font-mono font-bold text-slate-900">{{ $item->nim }}</td>
                            <td class="py-4 px-6 font-semibold text-slate-800">{{ $item->name }}</td>
                            <td class="py-4 px-6 text-slate-600">
                                <div class="font-medium text-slate-800">{{ $item->study_program }}</div>
                                <div class="text-[11px] text-slate-400">{{ $item->faculty }}</div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($academic === 'aktif')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        🟢 Kuliah Aktif
                                    </span>
                                @elseif($academic === 'magang')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        🔵 Sedang Magang / MBKM
                                    </span>
                                @elseif($academic === 'cuti')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        🔴 Cuti Akademik
                                    </span>
                                @elseif($academic === 'do')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        🔴 Drop Out (DO)
                                    </span>
                                @elseif($academic === 'wisuda')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-200 text-slate-800 border border-slate-300">
                                        🎓 Sudah Wisuda / Alumni
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        ⚠️ Bermasalah Akademik
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($isEligible)
                                    <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-500 text-white shadow-sm">
                                        ✓ MEMENUHI SYARAT (DPT)
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-[11px] font-extrabold bg-rose-500 text-white shadow-sm">
                                        ✕ TIDAK BERHAK (TMS)
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($isEligible)
                                    <span class="text-emerald-700 font-bold text-[11px] flex items-center justify-center space-x-1">
                                        <span>Aktif di Bilik Suara</span>
                                    </span>
                                @else
                                    <span class="text-rose-600 font-bold text-[11px] flex items-center justify-center space-x-1">
                                        <span>Akses Ditolak Sistem</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                Tidak ada data DPS yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dpsList->hasPages())
            <div class="p-5 border-t border-slate-100">
                {{ $dpsList->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
