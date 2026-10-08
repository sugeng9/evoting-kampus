@extends('layouts.app')

@section('title', 'Tambah Pemilih DPT - KPU Kampus')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header Form -->
    <div class="flex items-center justify-between bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Tambah Pemilih Mahasiswa (DPT)</h1>
            <p class="text-xs text-slate-500 mt-1">Daftarkan mahasiswa baru yang memiliki hak suara pada Pemira.</p>
        </div>
        <a href="{{ route('admin.voters.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
            &larr; Kembali
        </a>
    </div>

    <!-- Form Body -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('admin.voters.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Input NIM -->
            <div>
                <label for="nim" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Nomor Induk Mahasiswa (NIM) *
                </label>
                <input type="text" 
                       name="nim" 
                       id="nim" 
                       value="{{ old('nim') }}" 
                       required 
                       placeholder="Contoh: 120140025" 
                       class="w-full py-3 px-4 bg-slate-50 border @error('nim') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 font-mono text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('nim')
                    <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Input Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Nama Lengkap Mahasiswa *
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}" 
                       required 
                       placeholder="Contoh: Dimas Setiawan" 
                       class="w-full py-3 px-4 bg-slate-50 border @error('name') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                @error('name')
                    <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fakultas & Program Studi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="faculty" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Fakultas *
                    </label>
                    <input type="text" 
                           name="faculty" 
                           id="faculty" 
                           value="{{ old('faculty') }}" 
                           required 
                           placeholder="Contoh: Fakultas Teknik" 
                           class="w-full py-3 px-4 bg-slate-50 border @error('faculty') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('faculty')
                        <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="study_program" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Program Studi *
                    </label>
                    <input type="text" 
                           name="study_program" 
                           id="study_program" 
                           value="{{ old('study_program') }}" 
                           required 
                           placeholder="Contoh: Teknik Informatika" 
                           class="w-full py-3 px-4 bg-slate-50 border @error('study_program') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('study_program')
                        <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- PIN / Token Rahasia -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="pin" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        PIN / Token Verifikasi Pemira *
                    </label>
                    <button type="button" 
                            onclick="generateRandomPin()" 
                            class="text-xs font-bold text-emerald-600 hover:text-emerald-700 underline">
                        + Buat PIN Acak 6-Digit
                    </button>
                </div>
                
                <input type="text" 
                       name="pin" 
                       id="pin" 
                       value="{{ old('pin') }}" 
                       required 
                       placeholder="Masukkan PIN (minimal 4 digit/karakter)" 
                       class="w-full py-3 px-4 bg-slate-50 border @error('pin') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 font-mono tracking-wider text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <span class="text-[11px] text-slate-400 mt-1 block">PIN ini digunakan oleh mahasiswa saat login ke bilik suara dan konfirmasi akhir coblos.</span>
                @error('pin')
                    <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Submit -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.voters.index') }}" class="py-3 px-5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="py-3 px-6 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 transition">
                    Simpan ke DPT
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function generateRandomPin() {
        // Hasilkan 6 angka acak
        const pin = Math.floor(100000 + Math.random() * 900000);
        document.getElementById('pin').value = pin;
    }
</script>
@endpush
