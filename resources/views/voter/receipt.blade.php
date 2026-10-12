@extends('layouts.app')

@section('title', 'Kuitansi Pemilihan - ' . $receipt['category_name'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Card Kuitansi Kriptografis (Bukti Suara Sah) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden print:border-none print:shadow-none" id="printableReceipt">
        
        <!-- Header Dokumen Kuitansi Resmi -->
        <div class="bg-slate-900 p-6 sm:p-7 text-white text-center border-b border-slate-800">
            <div class="flex justify-center mb-2">
                <x-app-logo size="md" variant="mark" theme="dark" />
            </div>
            <div class="inline-flex items-center space-x-1.5 px-3 py-0.5 rounded-full bg-slate-800 text-slate-300 text-[11px] font-mono font-semibold uppercase tracking-wider mb-2 border border-slate-700">
                <span>Dokumen Sah &bull; Terverifikasi KPR (Komisi Pemilihan Raya) Politeknik Negeri Bengkalis</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight">Kuitansi Bukti Partisipasi Pemira</h1>
            <p class="text-xs text-slate-400 mt-1">Kategori: <strong class="text-white">{{ $receipt['category_name'] }}</strong></p>
        </div>

        <!-- Body Detail Kuitansi -->
        <div class="p-6 sm:p-7 space-y-5">
            
            <!-- Kode Verifikasi Publik -->
            <div class="p-4 sm:p-5 rounded-xl bg-slate-50 border border-slate-200 text-center">
                <span class="text-[11px] text-slate-500 uppercase font-bold tracking-wider block">Kode Verifikasi Audit Kotak Suara:</span>
                <div class="text-xl sm:text-3xl font-mono font-bold text-slate-900 tracking-wider mt-1 select-all break-all">
                    {{ $receipt['verification_code'] }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1">
                    Gunakan kode unik ini pada menu <strong>Audit Kuitansi</strong> untuk memverifikasi bahwa suara Anda telah tercatat sah di kotak suara digital.
                </p>
            </div>

            <!-- Detail Hash Kriptografi -->
            <div class="space-y-2 text-xs">
                <div class="flex flex-col sm:flex-row justify-between py-2 border-b border-slate-100 gap-1">
                    <span class="text-slate-500">Waktu Pencoblosan (WIB):</span>
                    <span class="font-bold text-slate-800 font-mono">{{ $receipt['voted_at'] }}</span>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-between py-2 border-b border-slate-100 gap-1">
                    <span class="text-slate-500">Status Kerahasiaan (Secret Ballot):</span>
                    <span class="font-semibold text-slate-800">Anonim &amp; Terpisah Secara Kriptografis</span>
                </div>

                <div class="py-2">
                    <span class="text-slate-500 block mb-1">Bukti Hash SHA-256 Kertas Suara:</span>
                    <div class="p-2.5 rounded-lg bg-slate-900 text-slate-300 font-mono text-[10px] break-all select-all">
                        {{ $receipt['ballot_hash'] }}
                    </div>
                </div>
            </div>

            <!-- Pernyataan Privasi -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs leading-relaxed flex items-start space-x-2.5">
                <svg class="w-4 h-4 text-slate-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>
                    <strong>Jaminan Privasi:</strong> Identitas NIM Anda dicatat telah memberikan suara untuk mencegah coblos ganda. Namun pilihan paslon Anda dipisahkan secara acak tanpa jejak identitas agar pilihan Anda tetap rahasia.
                </span>
            </div>

        </div>

        <!-- Tombol Cetak / PDF -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
            <span class="text-slate-500">Simpan atau cetak sebagai bukti partisipasi</span>
            <button type="button" 
                    onclick="window.print()" 
                    class="px-3.5 py-1.5 rounded-lg border border-slate-300 hover:bg-white text-slate-800 font-semibold transition flex items-center space-x-1.5 shadow-sm">
                <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Bukti (PDF)</span>
            </button>
        </div>
    </div>

    <!-- CARD HASIL PEMILIHAN SUARA SEMENTARA (LIVE QUICK COUNT) -->
    @if($categoryResults)
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden print:hidden">
        
        <!-- Header Hasil -->
        <div class="p-5 sm:p-6 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Hasil Real-Time</span>
                <h2 class="text-base font-bold text-slate-900 tracking-tight">
                    Suara Sementara: {{ $categoryResults['category']->name }}
                </h2>
                <p class="text-xs text-slate-500">Perolehan suara yang telah masuk ke kotak suara digital.</p>
            </div>

            <div class="text-right sm:text-right">
                <span class="text-xs font-mono font-bold text-slate-800">{{ number_format($categoryResults['total_votes']) }} Suara Masuk</span>
            </div>
        </div>

        <!-- Daftar Paslon & Perolehan Suara -->
        <div class="p-5 sm:p-6 space-y-3">
            @forelse($categoryResults['candidates'] as $candidate)
            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/60 space-y-2">
                <div class="flex items-center justify-between gap-3">
                    
                    <!-- Info Kandidat -->
                    <div class="flex items-center space-x-3 min-w-0">
                        <div class="w-8 h-8 rounded-lg bg-slate-900 text-white font-mono font-bold flex items-center justify-center text-xs flex-shrink-0">
                            {{ str_pad($candidate['number'], 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <div class="min-w-0">
                            <span class="text-xs font-bold text-slate-900 block truncate">
                                {{ $candidate['leader_name'] }}
                            </span>
                            @if($candidate['vice_leader_name'])
                                <span class="text-[11px] text-slate-500 block truncate">
                                    Wakil: {{ $candidate['vice_leader_name'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Angka Perolehan & Persentase -->
                    <div class="text-right flex-shrink-0 font-mono">
                        <span class="text-sm font-bold text-slate-900 block">
                            {{ $candidate['percentage'] }}%
                        </span>
                        <span class="text-[10px] text-slate-500">
                            {{ number_format($candidate['total_votes']) }} Suara
                        </span>
                    </div>

                </div>

                <!-- Progress Bar Suara -->
                <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-slate-900 h-1.5 rounded-full transition-all duration-700 ease-out"
                         style="width: {{ max($candidate['percentage'], 2) }}%;"></div>
                </div>
            </div>
            @empty
            <div class="text-center py-4 text-slate-400 text-xs">
                Belum ada data perolehan suara di kategori ini.
            </div>
            @endforelse
        </div>

        <!-- Footer Info -->
        <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span>Perolehan suara dihitung otomatis dari kotak suara digital</span>
            <a href="{{ route('quick_count') }}" class="font-semibold text-slate-800 hover:text-slate-900 flex items-center space-x-1">
                <span>Seluruh Kategori &rarr;</span>
            </a>
        </div>
    </div>
    @endif

    <!-- CARD LANGKAH SELANJUTNYA / NAVIGASI PEMILIH -->
    <div class="bg-white border border-slate-200/90 rounded-2xl p-5 sm:p-6 shadow-sm print:hidden space-y-4">
        @if($remainingCount > 0 && $nextCategory)
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-500 block">
                        Tersisa {{ $remainingCount }} Hak Suara Belum Digunakan
                    </span>
                    <h3 class="text-base font-bold text-slate-900">
                        Lanjut ke Pemilihan: {{ $nextCategory->name }}
                    </h3>
                    <p class="text-xs text-slate-500 max-w-xl">
                        Pastikan seluruh hak suara Anda tersalurkan sebelum meninggalkan sesi pemilihan.
                    </p>
                </div>

                <a href="{{ route('voter.ballot', $nextCategory->id) }}" 
                   class="w-full sm:w-auto justify-center px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-semibold text-xs transition flex items-center space-x-2 flex-shrink-0 shadow-sm">
                    <span>Lanjut Memilih</span>
                    <span class="font-mono">&rarr;</span>
                </a>
            </div>
        @else
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-emerald-700 block">
                        ✓ Seluruh Hak Suara Selesai Digunakan
                    </span>
                    <h3 class="text-base font-bold text-slate-900">
                        Terima Kasih Atas Partisipasi Anda
                    </h3>
                    <p class="text-xs text-slate-500 max-w-xl">
                        Hak suara Anda telah tersimpan secara resmi dan terverifikasi dalam Pemira Politeknik Negeri Bengkalis.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-2.5 flex-shrink-0 w-full sm:w-auto">
                    <a href="{{ route('quick_count') }}" class="w-full sm:w-auto text-center px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition border border-slate-200">
                        Quick Count
                    </a>
                    <form action="{{ route('voter.logout') }}" method="POST" class="w-full sm:w-auto inline">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition">
                            Selesai &amp; Keluar
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500 text-center sm:text-left">
            <a href="{{ route('voter.dashboard') }}" class="hover:text-slate-800 transition flex items-center space-x-1">
                <span>&larr; Kembali ke Dashboard Bilik Suara</span>
            </a>
            <a href="{{ route('verify_ballot') }}" class="hover:text-slate-800 transition">
                Cek Bukti di Menu Audit Suara &rarr;
            </a>
        </div>
    </div>

</div>
@endsection
