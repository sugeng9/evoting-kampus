@extends('layouts.app')

@section('title', 'Import DPT Mahasiswa - KPR Politeknik Negeri Bengkalis')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Import -->
    <div class="flex items-center justify-between bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Import Massal DPT Mahasiswa</h1>
            <p class="text-xs text-slate-500 mt-1">Unggah ribuan data mahasiswa pemilih sekaligus dari file database/spreadsheet CSV.</p>
        </div>
        <a href="{{ route('admin.voters.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
            &larr; Kembali
        </a>
    </div>

    <!-- Download Template Card -->
    <div class="bg-gradient-to-r from-teal-500/10 via-emerald-500/10 to-transparent p-5 rounded-3xl border border-teal-500/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-teal-500 text-white flex items-center justify-center font-bold shadow-md shadow-teal-500/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-xs font-black text-teal-950 uppercase tracking-wide">Format Kolom Spreadsheet BAAK Kampus</h3>
                <p class="text-xs text-teal-800/80">Header yang didukung: <code>nim, nama, fakultas, prodi, angkatan, tanggal_lahir, status_akademik</code></p>
                <p class="text-[11px] text-teal-700 mt-1">Sistem otomatis menyaring: <strong>Aktif &amp; Magang</strong> sah DPT &amp; masuk Data Master, sedangkan <strong>Cuti, DO, &amp; Wisuda</strong> otomatis dicoret (TMS).</p>
            </div>
        </div>

        <a href="{{ route('admin.voters.template') }}" class="px-4 py-2 bg-white hover:bg-teal-50 text-teal-700 border border-teal-200 rounded-xl text-xs font-bold transition flex items-center space-x-2 shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>Unduh Contoh Template CSV</span>
        </a>
    </div>

    <!-- Form Upload CSV -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('admin.voters.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm" class="space-y-6">
            @csrf

            <!-- Upload File Input -->
            <div>
                <label for="excel_file" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Pilih File Excel (.xlsx) atau CSV (.csv) *
                </label>
                <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-emerald-500 bg-slate-50 transition">
                    <svg class="w-10 h-10 text-emerald-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls,.csv" required class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-2">Mendukung format file langsung dari Microsoft Excel (<strong>.xlsx</strong>) dan format <strong>.csv</strong>.</p>
                </div>
                @error('excel_file')
                    <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Mekanisme Otentikasi: Password Mandiri Mahasiswa (Tanpa Token) -->
            <div class="p-5 rounded-2xl bg-emerald-50/80 border border-emerald-200/80 space-y-2.5">
                <div class="flex items-center space-x-2 text-emerald-950">
                    <div class="w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                        🔑
                    </div>
                    <span class="text-xs font-black uppercase tracking-wider text-emerald-950">
                        Otentikasi Kata Sandi Mandiri (Bebas Beban Token Fisik)
                    </span>
                </div>
                <p class="text-xs text-emerald-800 leading-relaxed">
                    Sistem tidak lagi membebani panitia dengan pembuatan atau pembagian token fisik. Mahasiswa yang sah masuk DPT akan melakukan <strong>pendaftaran mandiri di web</strong> menggunakan <strong>NIM &amp; Tanggal Lahir</strong>, lalu membuat <strong>Kata Sandi pribadi</strong> masing-masing (dengan verifikasi OTP email).
                </p>
                <div class="flex items-center space-x-2 text-[11px] text-emerald-700 font-semibold pt-1">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Asas Luber Jurdil: Panitia tidak mengetahui kata sandi mahasiswa (terenkripsi Bcrypt).</span>
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.voters.index') }}" class="py-3 px-5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" id="btnSubmitImport" class="py-3 px-6 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 transition flex items-center space-x-2">
                    <span id="btnTextImport">Mulai Proses Import Database</span>
                    <svg id="spinnerImport" class="hidden animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.getElementById('importForm').addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmitImport');
        const text = document.getElementById('btnTextImport');
        const spinner = document.getElementById('spinnerImport');

        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        text.innerText = 'Mengimpor Data Mahasiswa...';
        spinner.classList.remove('hidden');
    });
</script>
@endpush
