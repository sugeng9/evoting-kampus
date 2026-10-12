@extends('layouts.app')

@section('title', 'Tambah Kategori Ormawa - KPR Politeknik Negeri Bengkalis')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Form -->
    <div class="flex items-center justify-between bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-700 text-xs font-bold border border-emerald-500/30 mb-2">
                <span>Organisasi Mahasiswa Baru</span>
            </div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Tambah Kategori Pemilihan Organisasi</h1>
            <p class="text-xs text-slate-500 mt-1">Daftarkan ormawa baru (Presma, DPM, BEM Fakultas, atau HMJ Prodi) serta tentukan filter pemilih &amp; jadwal.</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
            &larr; Kembali
        </a>
    </div>

    <!-- Form Body -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Bagian 1: Identitas Organisasi -->
            <div class="space-y-4">
                <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900 border-b border-slate-100 pb-2">
                    1. Identitas Kategori Organisasi
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nama Kategori Pemilihan *
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name') }}" 
                               required 
                               placeholder="Contoh: Pemilihan Ketua Himpunan Mahasiswa Informatika (HMTI)" 
                               class="w-full py-2.5 px-4 bg-slate-50 border @error('name') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @error('name')
                            <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Kode Unik Kategori *
                        </label>
                        <input type="text" 
                               name="code" 
                               id="code" 
                               value="{{ old('code') }}" 
                               required 
                               placeholder="Contoh: KAHIM_HMTI_2026" 
                               class="w-full py-2.5 px-4 bg-slate-50 border @error('code') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 text-xs font-mono font-bold uppercase focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @error('code')
                            <p class="text-xs text-rose-600 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Deskripsi / Penjelasan Pemilihan (Opsional)
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="2" 
                              placeholder="Penjelasan singkat mengenai jabatan organisasi atau lingkup tugasnya..." 
                              class="w-full py-2.5 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Bagian 2: Filter Hak Suara Pemilih (Eligibilitas) -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">
                        2. Filter Hak Suara Pemilih (DPT)
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Biarkan kosong jika pemilihan terbuka untuk <strong>seluruh mahasiswa kampus</strong> (misal Presma &amp; DPM). Isi jika khusus tingkat Fakultas atau Prodi (HMJ).
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="faculty_filter" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Filter Fakultas (Opsional)
                        </label>
                        <input type="text" 
                               name="faculty_filter" 
                               id="faculty_filter" 
                               value="{{ old('faculty_filter') }}" 
                               placeholder="Contoh: Fakultas Teknologi Industri" 
                               class="w-full py-2.5 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        
                        @if($faculties->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-1.5 items-center">
                                <span class="text-[10px] text-slate-400 font-semibold">Pilih cepat:</span>
                                @foreach($faculties as $fac)
                                    <button type="button" 
                                            onclick="document.getElementById('faculty_filter').value = '{{ addslashes($fac) }}'"
                                            class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold">
                                        {{ $fac }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div>
                        <label for="study_program_filter" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Filter Program Studi / Jurusan (Opsional - HMJ)
                        </label>
                        <input type="text" 
                               name="study_program_filter" 
                               id="study_program_filter" 
                               value="{{ old('study_program_filter') }}" 
                               placeholder="Contoh: Teknik Informatika" 
                               class="w-full py-2.5 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        
                        @if($studyPrograms->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-1.5 items-center">
                                <span class="text-[10px] text-slate-400 font-semibold">Pilih cepat:</span>
                                @foreach($studyPrograms as $prodi)
                                    <button type="button" 
                                            onclick="document.getElementById('study_program_filter').value = '{{ addslashes($prodi) }}'"
                                            class="px-2 py-0.5 rounded-md bg-purple-50 hover:bg-purple-100 text-purple-700 text-[10px] font-bold">
                                        {{ $prodi }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bagian 3: Jadwal Waktu & Status -->
            <div class="space-y-4 pt-4 border-t border-slate-100">
                <div>
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-900">
                        3. Jadwal Waktu &amp; Eksekusi Sesi Bilik Suara
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Tentukan waktu mulai dan selesai khusus untuk kategori ini (misal sesi pagi atau siang).
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="start_at" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Waktu Mulai (WIB)
                        </label>
                        <input type="datetime-local" 
                               name="start_at" 
                               id="start_at" 
                               value="{{ old('start_at') }}" 
                               class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-slate-400 mt-1 block">Kosongkan jika langsung buka</span>
                    </div>

                    <div>
                        <label for="end_at" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Waktu Selesai (WIB)
                        </label>
                        <input type="datetime-local" 
                               name="end_at" 
                               id="end_at" 
                               value="{{ old('end_at') }}" 
                               class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-slate-400 mt-1 block">Bilik suara otomatis tertutup</span>
                    </div>

                    <div>
                        <label for="status_override" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Mode Eksekusi Status
                        </label>
                        <select name="status_override" id="status_override" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="AUTO" {{ old('status_override', 'AUTO') === 'AUTO' ? 'selected' : '' }}>Otomatis (Sesuai Jam)</option>
                            <option value="FORCE_OPEN" {{ old('status_override') === 'FORCE_OPEN' ? 'selected' : '' }}>Paksa Buka Sekarang</option>
                            <option value="FORCE_CLOSED" {{ old('status_override') === 'FORCE_CLOSED' ? 'selected' : '' }}>Paksa Tutup Sekarang</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div>
                        <label for="display_order" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Urutan Tampilan Pemilih *
                        </label>
                        <input type="number" 
                               name="display_order" 
                               id="display_order" 
                               min="1" 
                               value="{{ old('display_order', $nextOrder) }}" 
                               required 
                               class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <span class="text-[10px] text-slate-400 mt-1 block">Angka lebih kecil tampil lebih atas di dashboard</span>
                    </div>

                    <div class="flex items-center space-x-3 pt-6">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            <span class="ml-3 text-xs font-bold text-slate-700">Aktifkan Kategori Ini</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Tombol Submit -->
            <div class="pt-6 border-t border-slate-200 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.categories.index') }}" class="px-5 py-3 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-extrabold shadow-lg shadow-emerald-600/30 transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Kategori Baru</span>
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
