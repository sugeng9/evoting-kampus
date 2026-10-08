<?php $__env->startSection('title', 'Manajemen Kategori Ormawa - KPU Kampus'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-8 w-full">
    
    <!-- Header Kategori Ormawa -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-navy-900 text-white p-6 sm:p-8 rounded-3xl shadow-xl">
        <div>
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-xs font-bold border border-indigo-500/30 mb-2">
                <span>Manajemen Organisasi Mahasiswa</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Kategori Pemilihan (3 Hak Suara per NIM)</h1>
            <p class="text-xs text-slate-300 mt-1">Setiap 1 NIM memiliki 3 hak suara: <strong>BEM Universitas</strong>, <strong>MPM Universitas</strong>, dan <strong>HMJ</strong> sesuai jurusan masing-masing mahasiswa.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- Tombol Preset 3 Pilar -->
            <form action="<?php echo e(route('admin.categories.seed_preset')); ?>" method="POST" onsubmit="return confirm('Inisialisasi atau sinkronkan struktur 3 Hak Suara (BEM, MPM, dan 8 HMJ Jurusan Polbeng)?');">
                <?php echo csrf_field(); ?>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-navy-800 hover:bg-indigo-600 border border-indigo-500/40 text-indigo-200 hover:text-white text-xs font-bold transition flex items-center space-x-2 shadow-sm" title="Otomatis siapkan struktur 3 Pilar: BEM, MPM, dan HMJ Jurusan">
                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>+ Preset 3 Pilar (BEM, MPM, HMJ)</span>
                </button>
            </form>

            <a href="<?php echo e(route('admin.reports.index')); ?>" class="px-4 py-2.5 rounded-xl bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-purple-700/30">
                <svg class="w-4 h-4 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>📑 Laporan Hasil Tetap</span>
            </a>

            <a href="<?php echo e(route('admin.categories.create')); ?>" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-emerald-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Tambah Kategori Baru</span>
            </a>
        </div>
    </div>

    <!-- Stats Quick Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Organisasi / Kategori</span>
            <div class="text-2xl font-black text-slate-900 mt-2"><?php echo e($totalCategories); ?> Ormawa</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Tingkat Kampus, Fakultas &amp; HMJ</span>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Kategori Aktif</span>
            <div class="text-2xl font-black text-emerald-600 mt-2"><?php echo e($activeCategories); ?> Aktif</div>
            <span class="text-[11px] text-emerald-600 font-semibold mt-1 block">Dapat diakses di bilik suara</span>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Terbuka Seluruh Mahasiswa</span>
            <div class="text-2xl font-black text-indigo-600 mt-2">
                <?php echo e($categories->whereNull('faculty_filter')->whereNull('study_program_filter')->count()); ?> Kategori
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Presma &amp; DPM Universitas</span>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Khusus Fakultas / Prodi (HMJ)</span>
            <div class="text-2xl font-black text-purple-600 mt-2">
                <?php echo e($categories->filter(fn($c) => $c->faculty_filter || $c->study_program_filter)->count()); ?> Kategori
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Tersaring sesuai data NIM pemilih</span>
        </div>
    </div>

    <!-- Daftar Kategori Ormawa -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-black text-slate-900">Daftar Lengkap Kategori Pemilihan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Sistem memastikan mahasiswa hanya mencoblos kategori yang berhak sesuai Fakultas dan Program Studi masing-masing.</p>
            </div>
            <span class="text-xs font-bold text-slate-400">
                Diurutkan berdasarkan prioritas tampilan pemilih
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">Urutan &amp; Kode</th>
                        <th class="py-3.5 px-6">Nama Organisasi / Kategori</th>
                        <th class="py-3.5 px-6">Filter Hak Suara (Eligibilitas)</th>
                        <th class="py-3.5 px-6">Jadwal &amp; Status</th>
                        <th class="py-3.5 px-6 text-center">Paslon</th>
                        <th class="py-3.5 px-6 text-center">Suara</th>
                        <th class="py-3.5 px-6 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $schedStatus = $cat->getScheduleStatus();
                        ?>
                        <tr class="hover:bg-slate-50/80 transition <?php echo e(!$cat->is_active ? 'opacity-60 bg-slate-50/40' : ''); ?>">
                            
                            <!-- Urutan & Kode -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="w-6 h-6 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center">
                                        #<?php echo e($cat->display_order); ?>

                                    </span>
                                    <span class="px-2 py-0.5 rounded-md bg-navy-900 text-white font-mono text-[11px] font-bold">
                                        <?php echo e($cat->code); ?>

                                    </span>
                                </div>
                            </td>

                            <!-- Nama & Deskripsi -->
                            <td class="py-4 px-6 max-w-xs">
                                <h4 class="text-xs font-black text-slate-900 leading-tight"><?php echo e($cat->name); ?></h4>
                                <p class="text-[11px] text-slate-400 mt-1 line-clamp-1"><?php echo e($cat->description ?: 'Organisasi Mahasiswa'); ?></p>
                            </td>

                            <!-- Filter Hak Suara -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                <?php
                                    $pillar = $cat->getPillarBadge();
                                ?>
                                <?php if(!$cat->faculty_filter && !$cat->study_program_filter): ?>
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] font-bold border <?php echo e($pillar['badge_color']); ?>">
                                        <span><?php echo e($pillar['tag']); ?>: <?php echo e($pillar['title']); ?></span>
                                    </span>
                                <?php elseif($cat->study_program_filter): ?>
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] font-bold border <?php echo e($pillar['badge_color']); ?>">
                                        <span><?php echo e($pillar['tag']); ?>: HMJ <?php echo e($cat->study_program_filter); ?></span>
                                    </span>
                                <?php elseif($cat->faculty_filter): ?>
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-300 line-through" title="Tingkat Fakultas Dinonaktifkan (Aturan 3 Hak Suara)">
                                        <span>Fakultas: <?php echo e($cat->faculty_filter); ?> (Dinonaktifkan)</span>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Jadwal & Status -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                <div class="space-y-1">
                                    <div class="text-[11px] font-semibold text-slate-700">
                                        <?php echo e($cat->start_at ? $cat->start_at->timezone('Asia/Jakarta')->translatedFormat('d M, H:i') : 'Langsung'); ?> 
                                        s/d 
                                        <?php echo e($cat->end_at ? $cat->end_at->timezone('Asia/Jakarta')->translatedFormat('d M, H:i \W\I\B') : 'Selesai'); ?>

                                    </div>
                                    <div>
                                        <?php if($cat->status_override === 'FORCE_OPEN'): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">Paksa Buka</span>
                                        <?php elseif($cat->status_override === 'FORCE_CLOSED'): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Paksa Tutup</span>
                                        <?php elseif($schedStatus === 'OPEN'): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center space-x-1 w-max">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                <span>Berlangsung</span>
                                            </span>
                                        <?php elseif($schedStatus === 'UPCOMING'): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Akan Datang</span>
                                        <?php elseif($schedStatus === 'ENDED'): ?>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-600">Berakhir</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Paslon Count -->
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <a href="<?php echo e(route('admin.candidates.index')); ?>" class="font-bold text-slate-800 hover:text-emerald-600 underline">
                                    <?php echo e($cat->candidates_count); ?> Paslon
                                </a>
                            </td>

                            <!-- Suara Masuk -->
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <span class="font-mono font-bold <?php echo e($cat->ballots_count > 0 ? 'text-emerald-600' : 'text-slate-400'); ?>">
                                    <?php echo e(number_format($cat->ballots_count)); ?>

                                </span>
                            </td>

                            <!-- Status Toggle -->
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <form action="<?php echo e(route('admin.categories.toggle', $cat->id)); ?>" method="POST" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold transition <?php echo e($cat->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'); ?>">
                                        <?php echo e($cat->is_active ? 'AKTIF' : 'NON-AKTIF'); ?>

                                    </button>
                                </form>
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-6 text-right whitespace-nowrap space-x-1">
                                <a href="<?php echo e(route('admin.candidates.create')); ?>?category_id=<?php echo e($cat->id); ?>" class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold transition inline-flex items-center space-x-1" title="Tambah Paslon untuk <?php echo e($cat->name); ?>">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>+ Paslon</span>
                                </a>

                                <a href="<?php echo e(route('admin.categories.edit', $cat->id)); ?>" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition inline-flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Edit</span>
                                </a>

                                <a href="<?php echo e(route('admin.reports.category', $cat->id)); ?>" target="_blank" class="px-2.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-lg text-xs font-bold transition inline-flex items-center space-x-1" title="Cetak Berita Acara Resmi <?php echo e($cat->name); ?>">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Berita Acara</span>
                                </a>

                                <form action="<?php echo e(route('admin.categories.destroy', $cat->id)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori \'<?php echo e($cat->name); ?>\'? Catatan: Kategori tidak dapat dihapus jika sudah ada suara pemilih.');" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold transition inline-flex items-center space-x-1" <?php echo e($cat->ballots_count > 0 ? 'title=Terkunci_karena_ada_suara' : ''); ?>>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </td>

                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 text-xs">
                                Belum ada kategori pemilihan yang didaftarkan. Klik tombol <strong>"+ Preset 10 Ormawa"</strong> untuk menyiapkan struktur otomatis.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/admin/categories/index.blade.php ENDPATH**/ ?>