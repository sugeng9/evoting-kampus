@extends('layouts.app')

@section('title', 'Kuitansi & Hasil Suara Sementara')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Card Kuitansi Kriptografis (Bukti Suara Sah) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden print:border-none print:shadow-none" id="printableReceipt">
        
        <!-- Header Banner Kuitansi -->
        <div class="bg-gradient-to-r from-navy-950 via-emerald-950 to-navy-900 p-6 sm:p-8 text-white text-center relative overflow-hidden border-b border-emerald-500/20">
            <div class="flex justify-center mb-2">
                <x-app-logo size="lg" variant="mark" theme="dark" />
            </div>
            <div class="inline-flex items-center space-x-1.5 px-3 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[11px] font-extrabold uppercase tracking-widest border border-emerald-500/30 mb-2">
                <span>✓ Suara Sah &bull; Terverifikasi KPU Polbeng</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-heading font-black tracking-tight">KUITANSI ELEKTRONIK HAK SUARA</h1>
            <p class="text-xs text-slate-300 font-sans mt-1">Kategori: <strong class="text-emerald-300 underline">{{ $receipt['category_name'] }}</strong></p>
        </div>

        <!-- Body Detail Kuitansi -->
        <div class="p-6 sm:p-8 space-y-6">
            
            <!-- Kode Verifikasi Publik -->
            <div class="p-5 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-300 text-center">
                <span class="text-[11px] text-slate-500 uppercase font-bold tracking-wider block">Kode Verifikasi Kotak Suara (Audit Code):</span>
                <div class="text-2xl sm:text-3xl font-mono font-black text-emerald-700 tracking-wider mt-1 select-all">
                    {{ $receipt['verification_code'] }}
                </div>
                <p class="text-[11px] text-slate-400 mt-1">
                    Gunakan kode ini di menu <strong>Audit Kuitansi</strong> untuk memeriksa bahwa suara Anda tersimpan di kotak suara secara sah.
                </p>
            </div>

            <!-- Cryptographic Audit Hash -->
            <div class="space-y-2.5 text-xs">
                <div class="flex flex-col sm:flex-row justify-between py-2 border-b border-slate-100 gap-1">
                    <span class="text-slate-500 font-medium">Waktu Transaksi (WIB):</span>
                    <span class="font-bold text-slate-800 font-mono">{{ $receipt['voted_at'] }}</span>
                </div>
                
                <div class="flex flex-col sm:flex-row justify-between py-2 border-b border-slate-100 gap-1">
                    <span class="text-slate-500 font-medium">Status Kerahasiaan (Ballot Status):</span>
                    <span class="font-bold text-emerald-600">TERISOLASI &amp; ANONIM (Secret Ballot)</span>
                </div>

                <div class="py-2">
                    <span class="text-slate-500 font-medium block mb-1">SHA-256 Ballot Hash Proof:</span>
                    <div class="p-2.5 rounded-lg bg-slate-900 text-emerald-400 font-mono text-[10px] break-all select-all">
                        {{ $receipt['ballot_hash'] }}
                    </div>
                </div>
            </div>

            <!-- Guarantee Note -->
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs leading-relaxed flex items-start space-x-2.5">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>
                    <strong>Pernyataan Privasi:</strong> Identitas NIM Anda dicatat telah menggunakan hak suara agar tidak terjadi coblos ganda. Namun pilihan paslon Anda dipisahkan secara kriptografis tanpa korelasi database sehingga pilihan Anda tetap rahasia.
                </span>
            </div>

        </div>

        <!-- Tombol Cetak / PDF -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
            <span class="text-slate-400">Simpan kuitansi sebagai bukti sah partisipasi</span>
            <button type="button" 
                    onclick="window.print()" 
                    class="px-3.5 py-1.5 rounded-lg border border-slate-300 hover:bg-white text-slate-700 font-bold transition flex items-center space-x-1.5 shadow-sm">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

    <!-- CARD HASIL PEMILIHAN SUARA SEMENTARA (LIVE QUICK COUNT) -->
    @if($categoryResults)
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden print:hidden">
        
        <!-- Header Hasil -->
        <div class="bg-navy-900 text-white p-6 sm:p-7 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Live Quick Count Kotak Suara</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight">
                    Hasil Suara Sementara: {{ $categoryResults['category']->name }}
                </h2>
                <p class="text-xs text-slate-300">
                    Perolehan suara terkini yang telah masuk dan dihitung dari kotak suara digital.
                </p>
            </div>

            <div class="bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/15 text-right sm:text-right">
                <span class="text-[10px] text-slate-300 uppercase tracking-wider block font-medium">Total Suara Masuk</span>
                <span class="text-xl font-black text-emerald-400 font-mono">{{ number_format($categoryResults['total_votes']) }}</span>
                <span class="text-[10px] text-slate-400 block">Suara Sah</span>
            </div>
        </div>

        <!-- Daftar Paslon & Perolehan Suara -->
        <div class="p-6 sm:p-8 space-y-4">
            @forelse($categoryResults['candidates'] as $candidate)
            <div class="p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-slate-50 transition space-y-3">
                <div class="flex items-center justify-between gap-3">
                    
                    <!-- Info Kandidat -->
                    <div class="flex items-center space-x-3.5 min-w-0">
                        <!-- Nomor Urut -->
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-navy-900 text-white font-black flex items-center justify-center text-sm sm:text-base flex-shrink-0 shadow">
                            {{ str_pad($candidate['number'], 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <!-- Foto / Avatar Paslon -->
                        <div class="w-12 h-12 rounded-xl bg-slate-200 overflow-hidden flex-shrink-0 border border-slate-300">
                            @if($candidate['photo_path'])
                                <img src="{{ asset('storage/' . $candidate['photo_path']) }}" alt="Paslon {{ $candidate['number'] }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Nama Paslon -->
                        <div class="min-w-0">
                            <span class="text-sm font-extrabold text-slate-900 block truncate">
                                {{ $candidate['leader_name'] }}
                            </span>
                            @if($candidate['vice_leader_name'])
                                <span class="text-xs text-slate-500 block truncate">
                                    &amp; {{ $candidate['vice_leader_name'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Angka Perolehan & Persentase -->
                    <div class="text-right flex-shrink-0">
                        <span class="text-lg sm:text-2xl font-black text-emerald-600 block">
                            {{ $candidate['percentage'] }}%
                        </span>
                        <span class="text-[11px] font-bold text-slate-500 font-mono">
                            {{ number_format($candidate['total_votes']) }} Suara
                        </span>
                    </div>

                </div>

                <!-- Progress Bar Suara -->
                <div class="w-full bg-slate-200/80 rounded-full h-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-teal-500 to-emerald-500 h-3 rounded-full transition-all duration-700 ease-out shadow-sm"
                         style="width: {{ max($candidate['percentage'], 2) }}%;"></div>
                </div>
            </div>
            @empty
            <div class="text-center py-6 text-slate-400 text-xs">
                Belum ada data paslon di kategori ini.
            </div>
            @endforelse
        </div>

        <!-- Footer Info -->
        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <span class="flex items-center space-x-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Data dihitung secara otomatis dari kotak suara digital terenkripsi</span>
            </span>
            <a href="{{ route('quick_count') }}" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline flex items-center space-x-1">
                <span>Lihat Seluruh Kategori &rarr;</span>
            </a>
        </div>
    </div>
    @endif

    <!-- CARD LANGKAH SELANJUTNYA / NAVIGASI PEMILIH -->
    <div class="bg-gradient-to-br from-slate-900 via-navy-900 to-slate-800 text-white rounded-3xl p-6 sm:p-8 shadow-xl print:hidden space-y-4">
        @if($remainingCount > 0 && $nextCategory)
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-bold border border-amber-500/30">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        <span>⚡ Masih Ada {{ $remainingCount }} Hak Suara Tersisa</span>
                    </div>
                    <h3 class="text-lg sm:text-xl font-black tracking-tight text-white">
                        Lanjut ke Pemilihan: {{ $nextCategory->name }}
                    </h3>
                    <p class="text-xs text-slate-300 max-w-xl">
                        Jangan tinggalkan bilik suara sebelum seluruh hak suara Anda digunakan. Segera tentukan pilihan Anda untuk kategori berikutnya.
                    </p>
                </div>

                <a href="{{ route('voter.ballot', $nextCategory->id) }}" 
                   class="px-6 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-black text-sm transition transform hover:-translate-y-0.5 shadow-lg shadow-emerald-600/30 flex items-center space-x-2 flex-shrink-0">
                    <span>Lanjut Memilih Sekarang</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        @else
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30">
                        <span>🎉 Seluruh Hak Suara Digunakan 100%</span>
                    </div>
                    <h3 class="text-lg sm:text-xl font-black tracking-tight text-white">
                        Terima Kasih Atas Partisipasi Anda!
                    </h3>
                    <p class="text-xs text-slate-300 max-w-xl">
                        Anda telah sukses menyalurkan seluruh hak suara dalam Pemira Kampus ini. Pilihan Anda sangat berarti bagi kemajuan ormawa kampus.
                    </p>
                </div>

                <div class="flex items-center space-x-2.5 flex-shrink-0">
                    <a href="{{ route('quick_count') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition border border-white/20">
                        📊 Quick Count Penuh
                    </a>
                    <form action="{{ route('voter.logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-md shadow-rose-600/20">
                            🚪 Selesai &amp; Keluar
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <div class="pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs text-slate-400">
            <a href="{{ route('voter.dashboard') }}" class="hover:text-emerald-400 transition flex items-center space-x-1">
                <span>&larr; Kembali ke Beranda Bilik Suara</span>
            </a>
            <a href="{{ route('verify_ballot') }}" class="hover:text-teal-400 transition">
                Cek Bukti di Menu Audit Suara &rarr;
            </a>
        </div>
    </div>

</div>
@endsection
