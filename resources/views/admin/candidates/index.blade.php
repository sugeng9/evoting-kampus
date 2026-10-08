@extends('layouts.app')

@section('title', 'Manajemen Paslon - KPU Kampus')

@section('content')
<div class="space-y-8 w-full">
    
    <!-- Header Paslon -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-navy-900 text-white p-6 sm:p-8 rounded-3xl shadow-xl">
        <div>
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-teal-500/20 text-teal-300 text-xs font-bold border border-teal-500/30 mb-2">
                <span>Manajemen Kandidat</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Daftar Pasangan Calon (Paslon)</h1>
            <p class="text-xs text-slate-300 mt-1">Kelola data calon ketua, calon wakil, nomor urut, foto, serta visi-misi pemilihan.</p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.candidates.create') }}" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-emerald-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Tambah Paslon Baru</span>
            </a>
        </div>
    </div>

    <!-- Kelompok Kategori -->
    @forelse($categories as $category)
        <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">{{ $category->name }}</h2>
                    <span class="text-xs text-slate-400">Kode: <strong class="font-mono text-slate-600">{{ $category->code }}</strong></span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 self-start sm:self-auto">
                        {{ $category->allCandidates->count() }} Paslon Terdaftar
                    </span>
                    <a href="{{ route('admin.candidates.create') }}?category_id={{ $category->id }}" class="px-3 py-1 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold transition flex items-center space-x-1" title="Tambah Paslon untuk {{ $category->name }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>+ Paslon</span>
                    </a>
                </div>
            </div>

            <!-- Grid Paslon -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($category->allCandidates as $cand)
                    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-5 flex flex-col justify-between hover:border-slate-300 transition">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="w-10 h-10 rounded-xl bg-navy-900 text-white font-black text-base flex items-center justify-center">
                                    0{{ $cand->candidate_number }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $cand->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $cand->is_active ? 'AKTIF' : 'NON-AKTIF' }}
                                </span>
                            </div>

                            <!-- Foto & Nama -->
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="w-14 h-14 rounded-xl bg-white border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                    @if($cand->photo_path)
                                        <img src="{{ asset($cand->photo_path) }}" alt="{{ $cand->leader_name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs font-bold text-slate-400">Foto</span>
                                    @endif
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-slate-900 leading-snug">{{ $cand->leader_name }}</h3>
                                    @if($cand->leader_nim)
                                        <p class="text-[11px] font-mono text-slate-400">NIM: {{ $cand->leader_nim }}</p>
                                    @endif
                                    @if($cand->vice_leader_name)
                                        <p class="text-xs text-slate-600 mt-1 font-semibold">&amp; {{ $cand->vice_leader_name }}</p>
                                        @if($cand->vice_leader_nim)
                                            <p class="text-[11px] font-mono text-slate-400">NIM: {{ $cand->vice_leader_nim }}</p>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <!-- Visi Ringkas -->
                            <div class="p-3 bg-white rounded-xl border border-slate-100 text-xs text-slate-600 mb-4">
                                <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Visi:</span>
                                <p class="line-clamp-2 italic">"{{ $cand->vision }}"</p>
                            </div>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="pt-3 border-t border-slate-200/80 flex items-center justify-between gap-2">
                            <a href="{{ route('admin.candidates.edit', $cand->id) }}" class="flex-1 py-2 px-3 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 rounded-xl text-center text-xs font-bold transition">
                                Edit Paslon
                            </a>

                            <form action="{{ route('admin.candidates.destroy', $cand->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus Paslon 0{{ $cand->candidate_number }} ({{ $cand->leader_name }})?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="py-2 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition" title="Hapus Paslon">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-slate-400 text-xs space-y-2">
                        <p>Belum ada paslon yang didaftarkan pada organisasi ini.</p>
                        <a href="{{ route('admin.candidates.create') }}?category_id={{ $category->id }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold transition border border-emerald-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>+ Tambah Paslon untuk {{ $category->name }}</span>
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    @empty
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center shadow-sm space-y-4 max-w-xl mx-auto">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto text-2xl font-black">
                10
            </div>
            <div>
                <h3 class="text-base font-black text-slate-900">Belum Ada Kategori Ormawa / HMJ</h3>
                <p class="text-xs text-slate-500 mt-1">Sebelum menambahkan pasangan calon, siapkan terlebih dahulu kategori pemilihan untuk 10 Ormawa (Presma, DPM, BEM-F, &amp; HMJ).</p>
            </div>
            <div class="flex items-center justify-center gap-3 pt-2">
                <form action="{{ route('admin.categories.seed_preset') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-indigo-600/20">
                        ⚡ Siapkan 10 Preset Ormawa Otomatis
                    </button>
                </form>
                <a href="{{ route('admin.categories.create') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                    + Buat Kategori Manual
                </a>
            </div>
        </div>
    @endforelse

</div>
@endsection
