@extends('layouts.app')

@section('title', 'Tambah Paslon Baru - KPU Kampus')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Form -->
    <div class="flex items-center justify-between bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Tambah Pasangan Calon Baru</h1>
            <p class="text-xs text-slate-500 mt-1">Lengkapi data paslon, calon ketua, calon wakil, dan visi-misi pemilihan.</p>
        </div>
        <a href="{{ route('admin.candidates.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
            &larr; Kembali
        </a>
    </div>

    <!-- Form Body -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('admin.candidates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            @if($categories->isEmpty())
                <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start space-x-3 text-amber-800 text-xs">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="space-y-1">
                        <span class="font-bold block">Belum ada Kategori Pemilihan / Organisasi!</span>
                        <p>Silakan tambahkan kategori himpunan atau gunakan Preset 10 Ormawa terlebih dahulu.</p>
                        <div class="pt-2">
                            <a href="{{ route('admin.categories.index') }}" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-bold inline-block">
                                &rarr; Kelola Kategori Ormawa
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Kategori & Nomor Urut -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <label for="election_category_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Kategori Pemilihan (Ormawa / HMJ) *
                        </label>
                        <a href="{{ route('admin.categories.create') }}" class="text-[11px] font-bold text-emerald-600 hover:underline">
                            + Tambah Kategori Baru
                        </a>
                    </div>
                    <select name="election_category_id" id="election_category_id" required class="w-full py-3 px-4 bg-slate-50 border @error('election_category_id') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Kategori / Himpunan --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (old('election_category_id', request('category_id')) == $cat->id) ? 'selected' : '' }}>
                                [{{ $cat->code }}] {{ $cat->name }} 
                                @if($cat->study_program_filter)
                                    (HMJ: {{ $cat->study_program_filter }})
                                @elseif($cat->faculty_filter)
                                    (Fakultas: {{ $cat->faculty_filter }})
                                @else
                                    (Seluruh Kampus)
                                @endif
                                @if(!$cat->is_active)
                                    — [STATUS: NON-AKTIF]
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('election_category_id')
                        <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="candidate_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Nomor Urut *
                    </label>
                    <input type="number" name="candidate_number" id="candidate_number" min="1" value="{{ old('candidate_number', 1) }}" required class="w-full py-3 px-4 bg-slate-50 border @error('candidate_number') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('candidate_number')
                        <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Calon Ketua (Leader) -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800 block">
                    Data Calon Ketua
                </span>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="leader_name" class="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap Calon Ketua *</label>
                        <input type="text" name="leader_name" id="leader_name" value="{{ old('leader_name') }}" required placeholder="Contoh: Arya Pratama" class="w-full py-2.5 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                        @error('leader_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="leader_nim" class="block text-xs font-bold text-slate-600 mb-1">NIM Calon Ketua</label>
                        <input type="text" name="leader_nim" id="leader_nim" value="{{ old('leader_nim') }}" placeholder="Contoh: 120140012" class="w-full py-2.5 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                        @error('leader_nim') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Calon Wakil (Vice Leader) -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800">
                        Data Calon Wakil (Opsional)
                    </span>
                    <span class="text-[11px] text-slate-400">Dapat dikosongkan jika calon tunggal</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="vice_leader_name" class="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap Calon Wakil</label>
                        <input type="text" name="vice_leader_name" id="vice_leader_name" value="{{ old('vice_leader_name') }}" placeholder="Contoh: Annisa Zahra" class="w-full py-2.5 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                        @error('vice_leader_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="vice_leader_nim" class="block text-xs font-bold text-slate-600 mb-1">NIM Calon Wakil</label>
                        <input type="text" name="vice_leader_nim" id="vice_leader_nim" value="{{ old('vice_leader_nim') }}" placeholder="Contoh: 120130045" class="w-full py-2.5 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                        @error('vice_leader_nim') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Upload Foto Paslon -->
            <div>
                <label for="photo" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Foto Resmi Paslon (JPG / PNG / WebP, Maks. 2MB)
                </label>
                <input type="file" name="photo" id="photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                @error('photo')
                    <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Visi -->
            <div>
                <label for="vision" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Visi Paslon *
                </label>
                <textarea name="vision" id="vision" rows="3" required placeholder="Tuliskan visi utama pasangan calon..." class="w-full py-3 px-4 bg-slate-50 border @error('vision') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('vision') }}</textarea>
                @error('vision')
                    <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Misi & Program Kerja -->
            <div>
                <label for="mission" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Misi &amp; Program Kerja Utama *
                </label>
                <textarea name="mission" id="mission" rows="5" required placeholder="1. Poin misi pertama&#10;2. Poin misi kedua&#10;3. Poin misi ketiga" class="w-full py-3 px-4 bg-slate-50 border @error('mission') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none">{{ old('mission') }}</textarea>
                @error('mission')
                    <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Submit -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.candidates.index') }}" class="py-3 px-5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="py-3 px-6 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 transition">
                    Simpan Paslon Baru
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
