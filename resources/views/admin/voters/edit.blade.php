@extends('layouts.app')

@section('title', 'Edit Pemilih DPT - KPU Kampus')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header Form -->
    <div class="flex items-center justify-between bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Edit Data Pemilih (DPT)</h1>
            <p class="text-xs text-slate-500 mt-1">NIM: <strong class="font-mono text-slate-700">{{ $voter->nim }}</strong> &bull; {{ $voter->name }}</p>
        </div>
        <a href="{{ route('admin.voters.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
            &larr; Kembali
        </a>
    </div>

    <!-- Form Body -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('admin.voters.update', $voter->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Input NIM -->
            <div>
                <label for="nim" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Nomor Induk Mahasiswa (NIM) *
                </label>
                <input type="text" 
                       name="nim" 
                       id="nim" 
                       value="{{ old('nim', $voter->nim) }}" 
                       required 
                       class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-mono text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500">
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
                       value="{{ old('name', $voter->name) }}" 
                       required 
                       class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
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
                           value="{{ old('faculty', $voter->faculty) }}" 
                           required 
                           class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="study_program" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Program Studi *
                    </label>
                    <input type="text" 
                           name="study_program" 
                           id="study_program" 
                           value="{{ old('study_program', $voter->study_program) }}" 
                           required 
                           class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Status Hak Suara -->
            <div>
                <label for="is_active" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Status Hak Suara Pemilih *
                </label>
                <select name="is_active" id="is_active" class="py-2.5 px-4 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500">
                    <option value="1" {{ $voter->is_active ? 'selected' : '' }}>AKTIF (Dapat Login &amp; Menggunakan Hak Suara)</option>
                    <option value="0" {{ !$voter->is_active ? 'selected' : '' }}>NON-AKTIF (Hak Suara Dicabut / Diskorsing)</option>
                </select>
            </div>

            <!-- Reset PIN Baru (Opsional) -->
            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 space-y-2">
                <div class="flex items-center justify-between">
                    <label for="pin" class="block text-xs font-bold uppercase tracking-wider text-amber-900">
                        Reset PIN Baru (Opsional)
                    </label>
                    <button type="button" 
                            onclick="generateRandomPin()" 
                            class="text-xs font-bold text-emerald-700 hover:text-emerald-800 underline">
                        + Buat PIN Acak
                    </button>
                </div>
                <input type="text" 
                       name="pin" 
                       id="pin" 
                       placeholder="Kosongkan jika TIDAK ingin mengubah PIN mahasiswa" 
                       class="w-full py-2.5 px-3 bg-white border border-amber-300 rounded-xl text-slate-900 font-mono tracking-wider text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                <span class="text-[11px] text-amber-800/80 block">Isi hanya jika mahasiswa lupa PIN atau mengajukan permohonan reset token ke Helpdesk KPU.</span>
            </div>

            <!-- Tombol Submit -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.voters.index') }}" class="py-3 px-5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="py-3 px-6 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 transition">
                    Perbarui Data Pemilih
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function generateRandomPin() {
        const pin = Math.floor(100000 + Math.random() * 900000);
        document.getElementById('pin').value = pin;
    }
</script>
@endpush
