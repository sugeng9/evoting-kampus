<?php $__env->startSection('title', 'Radar Partisipasi & Mobilisasi Pemilih - KPU Polbeng'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-8 w-full">

    <!-- Header Panel Radar Partisipasi Lapangan -->
    <div class="bg-gradient-to-r from-navy-950 via-slate-900 to-indigo-950 rounded-3xl p-6 sm:p-8 text-white shadow-2xl relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative z-10">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 text-xs font-bold border border-rose-500/30 mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-400 animate-ping"></span>
                    <span>Operasional Lapangan Hari-H &bull; Anti-Golput</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Radar Partisipasi &amp; Mobilisasi Pemilih</h1>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Pantau capaian kuorum suara seluruh Program Studi secara real-time. Temukan jurusan dengan tingkat partisipasi terendah dan ekspor daftar mahasiswa yang belum memilih untuk digerakkan oleh Komti/Panitia.
                </p>
            </div>

            <!-- Header Action Controls -->
            <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                <a href="<?php echo e(route('admin.radar.export_summary')); ?>" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center space-x-1.5 border border-slate-700 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>📊 Ekspor Ringkasan Prodi (Excel)</span>
                </a>

                <a href="<?php echo e(route('admin.radar.export_unvoted', ['prodi' => $selectedProdi, 'status' => $filterStatus])); ?>" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-black transition flex items-center space-x-1.5 shadow-md shadow-rose-600/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>📥 Ekspor DPT Belum Memilih (CSV)</span>
                </a>

                <button type="button" onclick="document.getElementById('modal-wa-blast').classList.remove('hidden')" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black transition flex items-center space-x-1.5 shadow-md shadow-emerald-600/30">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                    </svg>
                    <span>💬 Format WA Pengingat Komti</span>
                </button>
            </div>
        </div>

        <!-- Info Bar Waktu & Monitoring -->
        <div class="mt-6 pt-4 border-t border-white/10 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
            <div class="flex items-center space-x-3">
                <span class="flex items-center space-x-1.5 text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Monitoring Langsung: <strong><?php echo e(count($allProdis)); ?> Program Studi Polbeng</strong></span>
                </span>
                <span>&bull;</span>
                <span>Waktu Acuan: <strong class="text-white"><?php echo e(now()->timezone('Asia/Jakarta')->translatedFormat('H:i')); ?> WIB</strong></span>
            </div>
            <div class="font-mono text-slate-300 text-[11px]">
                Data dihitung otomatis dari Kotak Suara KPU-M &amp; Presensi Pemilih
            </div>
        </div>
    </div>

    <!-- Ringkasan Statistik Partisipasi Kampus -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total DPT Kampus -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-navy-900 text-white flex items-center justify-center font-black text-lg shadow-sm">
                🎓
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total DPT Pemira</span>
                <span class="text-xl font-black text-slate-900 font-mono"><?php echo e(number_format($totalDptGlobal)); ?></span>
                <span class="text-[10px] text-slate-500 block">Mahasiswa Aktif Polbeng</span>
            </div>
        </div>

        <!-- Card 2: Sudah Memilih (Turnout) -->
        <div class="bg-emerald-50/70 p-5 rounded-3xl border border-emerald-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-lg shadow-sm">
                🗳️
            </div>
            <div>
                <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider block">Sudah Menggunakan Suara</span>
                <span class="text-xl font-black text-emerald-950 font-mono"><?php echo e(number_format($totalVotedGlobal)); ?></span>
                <span class="text-[11px] font-bold text-emerald-700 block">Partisipasi Kampus: <?php echo e($globalTurnoutPct); ?>%</span>
            </div>
        </div>

        <!-- Card 3: Belum Memilih (Target Mobilisasi) -->
        <div class="bg-rose-50/70 p-5 rounded-3xl border border-rose-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center font-black text-lg shadow-sm">
                📢
            </div>
            <div>
                <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wider block">Belum Memilih (Golput)</span>
                <span class="text-xl font-black text-rose-950 font-mono"><?php echo e(number_format($totalNotVotedGlobal)); ?></span>
                <span class="text-[11px] font-bold text-rose-700 block"><?php echo e($totalDptGlobal > 0 ? round(($totalNotVotedGlobal / $totalDptGlobal) * 100, 1) : 0); ?>% Mahasiswa Belum Nyoblos</span>
            </div>
        </div>

        <!-- Card 4: Radar Kuorum Prodi -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-900 text-white flex items-center justify-center font-black text-lg shadow-sm">
                🎯
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Radar Status Jurusan</span>
                <div class="flex items-center space-x-2 mt-0.5 text-xs font-black">
                    <span class="px-2 py-0.5 rounded-lg bg-rose-100 text-rose-800" title="Partisipasi < 30%"><?php echo e($criticalProdisCount); ?> Kritis</span>
                    <span class="px-2 py-0.5 rounded-lg bg-amber-100 text-amber-800" title="Partisipasi 30-60%"><?php echo e($moderateProdisCount); ?> Sedang</span>
                    <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800" title="Partisipasi >= 60%"><?php echo e($goodProdisCount); ?> Aman</span>
                </div>
                <span class="text-[10px] text-slate-500 block mt-0.5">Dari <?php echo e(count($allProdis)); ?> Program Studi</span>
            </div>
        </div>
    </div>

    <!-- Leaderboard Radar Partisipasi Seluruh Program Studi -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-7 shadow-sm space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-navy-100 text-navy-800 text-[10px] font-bold mb-1">
                    <span>Prioritas Mobilisasi Suara</span>
                </div>
                <h2 class="text-lg font-black text-slate-900 tracking-tight">Peringkat Partisipasi Seluruh Program Studi</h2>
                <p class="text-xs text-slate-500 mt-0.5">Diurutkan berdasarkan persentase partisipasi terendah agar panitia segera menghubungi Komti/Kajur terkait.</p>
            </div>

            <!-- Sorting Dropdown -->
            <form method="GET" action="<?php echo e(route('admin.radar.index')); ?>" class="flex items-center space-x-2">
                <input type="hidden" name="prodi" value="<?php echo e($selectedProdi); ?>">
                <input type="hidden" name="status" value="<?php echo e($filterStatus); ?>">
                <input type="hidden" name="search" value="<?php echo e($search); ?>">
                
                <span class="text-xs text-slate-400 font-bold whitespace-nowrap">Urutkan:</span>
                <select name="sort" onchange="this.form.submit()" class="py-1.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="turnout_asc" <?php echo e($sortBy === 'turnout_asc' ? 'selected' : ''); ?>>Partisipasi Terendah (Prioritas Mobilisasi)</option>
                    <option value="turnout_desc" <?php echo e($sortBy === 'turnout_desc' ? 'selected' : ''); ?>>Partisipasi Tertinggi</option>
                    <option value="name_asc" <?php echo e($sortBy === 'name_asc' ? 'selected' : ''); ?>>Nama Program Studi (A-Z)</option>
                    <option value="dpt_desc" <?php echo e($sortBy === 'dpt_desc' ? 'selected' : ''); ?>>DPT Terbanyak</option>
                </select>
            </form>
        </div>

        <!-- Grid Progress Bar Per Jurusan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php $__currentLoopData = $sortedRadar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $prodiItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-4 rounded-2xl border <?php echo e($selectedProdi === $prodiItem['study_program'] ? 'border-emerald-500 bg-emerald-50/30 ring-2 ring-emerald-500/20' : 'border-slate-200 bg-slate-50/50 hover:bg-white hover:border-slate-300'); ?> transition space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-6 h-6 rounded-lg bg-navy-900 text-white font-mono font-bold text-xs flex items-center justify-center flex-shrink-0">
                                #<?php echo e($index + 1); ?>

                            </span>
                            <div>
                                <h3 class="text-sm font-black text-slate-900 leading-snug"><?php echo e($prodiItem['study_program']); ?></h3>
                                <span class="text-[11px] text-slate-500 font-mono">DPT: <strong><?php echo e(number_format($prodiItem['total_dpt'])); ?></strong> mahasiswa</span>
                            </div>
                        </div>

                        <!-- Urgency Badge -->
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold border <?php echo e($prodiItem['badge_class']); ?>">
                            <?php echo e($prodiItem['urgency_label']); ?>

                        </span>
                    </div>

                    <!-- Progress Bar Partisipasi -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700 font-mono">
                                Sudah: <strong class="text-emerald-700"><?php echo e(number_format($prodiItem['voted_count'])); ?></strong> / <?php echo e(number_format($prodiItem['total_dpt'])); ?>

                            </span>
                            <span class="font-black text-sm font-mono <?php echo e($prodiItem['turnout_pct'] < 30 ? 'text-rose-600' : ($prodiItem['turnout_pct'] < 60 ? 'text-amber-600' : 'text-emerald-600')); ?>">
                                <?php echo e($prodiItem['turnout_pct']); ?>%
                            </span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-3 overflow-hidden flex">
                            <div class="h-3 transition-all duration-700 <?php echo e($prodiItem['turnout_pct'] < 30 ? 'bg-rose-500' : ($prodiItem['turnout_pct'] < 60 ? 'bg-amber-500' : 'bg-emerald-500')); ?>" style="width: <?php echo e(min(100, $prodiItem['turnout_pct'])); ?>%"></div>
                        </div>
                    </div>

                    <!-- Detail Rincian & Tombol Aksi Cepat Filter -->
                    <div class="flex items-center justify-between text-[11px] pt-1 border-t border-slate-200/60">
                        <div class="text-slate-500 space-x-2">
                            <span>Belum: <strong class="text-rose-600 font-bold font-mono"><?php echo e(number_format($prodiItem['not_voted_count'])); ?></strong></span>
                            <span>&bull;</span>
                            <span>Tuntas 3 Suara: <strong class="text-emerald-700 font-mono"><?php echo e(number_format($prodiItem['fully_voted_count'])); ?></strong></span>
                        </div>

                        <a href="<?php echo e(route('admin.radar.index', ['prodi' => $prodiItem['study_program'], 'status' => $filterStatus])); ?>" class="px-2.5 py-1 rounded-lg <?php echo e($selectedProdi === $prodiItem['study_program'] ? 'bg-emerald-700 text-white font-bold' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-300'); ?> text-[10px] font-bold transition flex items-center space-x-1">
                            <span>🎯 Lihat Mahasiswa</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TABEL & FILTER MAHASISWA YANG BELUM MENGGUNAKAN HAK SUARA -->
    <!-- ======================================================== -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden" id="daftar-belum-memilih">
        
        <!-- Table Header & Filter Toolbar -->
        <div class="p-6 sm:p-7 border-b border-slate-100 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-bold mb-1">
                        <span>Alat Mobilisasi Lapangan</span>
                    </div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">
                        Daftar Mahasiswa yang Belum Memilih
                        <?php if($selectedProdi !== 'all'): ?>
                            <span class="text-emerald-700">(<?php echo e($selectedProdi); ?>)</span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Gunakan daftar ini untuk menghubungi ketua kelas/komti agar mahasiswa segera mencoblos.</p>
                </div>

                <!-- Export Specific Buttons -->
                <div class="flex items-center space-x-2">
                    <a href="<?php echo e(route('admin.radar.export_unvoted', ['prodi' => $selectedProdi, 'status' => $filterStatus])); ?>" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition flex items-center space-x-1.5 shadow-sm">
                        <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Download Excel Data Ini</span>
                    </a>
                </div>
            </div>

            <!-- Filter Controls -->
            <form method="GET" action="<?php echo e(route('admin.radar.index')); ?>#daftar-belum-memilih" class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2">
                <!-- Dropdown Program Studi -->
                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Filter Program Studi:</label>
                    <select name="prodi" onchange="this.form.submit()" class="w-full py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="all" <?php echo e($selectedProdi === 'all' ? 'selected' : ''); ?>>-- Seluruh Program Studi Polbeng --</option>
                        <?php $__currentLoopData = $allProdis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p); ?>" <?php echo e($selectedProdi === $p ? 'selected' : ''); ?>><?php echo e($p); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <!-- Dropdown Status Partisipasi -->
                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Hak Suara:</label>
                    <select name="status" onchange="this.form.submit()" class="w-full py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="zero_votes" <?php echo e($filterStatus === 'zero_votes' ? 'selected' : ''); ?>>🔴 Belum Memilih Sama Sekali (0 dari 3)</option>
                        <option value="partial_votes" <?php echo e($filterStatus === 'partial_votes' ? 'selected' : ''); ?>>🟡 Baru Sebagian (1 atau 2 Hak Suara)</option>
                        <option value="all_incomplete" <?php echo e($filterStatus === 'all_incomplete' ? 'selected' : ''); ?>>⚪ Seluruh yang Belum Tuntas (< 3 Suara)</option>
                    </select>
                </div>

                <!-- Input Pencarian NIM / Nama -->
                <div class="sm:col-span-4">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari NIM / Nama:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Ketik NIM atau nama..." class="w-full py-2 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <button type="submit" class="px-3.5 py-2 bg-navy-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                            Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Pemilih Belum Memilih -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-6 text-center w-12">No</th>
                        <th class="py-3 px-6">NIM Mahasiswa</th>
                        <th class="py-3 px-6">Nama Lengkap</th>
                        <th class="py-3 px-6">Program Studi / Jurusan</th>
                        <th class="py-3 px-6 text-center">Hak Suara Terpakai</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-right">Login Terakhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $unvotedList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $voter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-6 text-center font-mono text-slate-400 font-bold">
                                <?php echo e($unvotedList->firstItem() + $idx); ?>

                            </td>
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 font-mono font-bold text-slate-800 border border-slate-200">
                                        <?php echo e($voter->nim); ?>

                                    </span>
                                    <button type="button" onclick="navigator.clipboard.writeText('<?php echo e($voter->nim); ?>'); alert('NIM <?php echo e($voter->nim); ?> berhasil disalin!');" title="Salin NIM" class="text-slate-400 hover:text-emerald-600 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 font-bold text-slate-900">
                                <?php echo e($voter->name); ?>

                            </td>
                            <td class="py-3.5 px-6">
                                <span class="font-semibold text-slate-700"><?php echo e($voter->study_program ?: '-'); ?></span>
                                <span class="block text-[10px] text-slate-400"><?php echo e($voter->faculty); ?></span>
                            </td>
                            <td class="py-3.5 px-6 text-center font-mono font-bold">
                                <span class="<?php echo e($voter->participations_count === 0 ? 'text-rose-600' : 'text-amber-600'); ?>">
                                    <?php echo e($voter->participations_count); ?> / 3
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-center whitespace-nowrap">
                                <?php if($voter->participations_count === 0): ?>
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Belum Memilih</span>
                                    </span>
                                <?php elseif($voter->participations_count < 3): ?>
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        <span>Baru Sebagian</span>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <span>Selesai</span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3.5 px-6 text-right whitespace-nowrap font-mono text-[11px] text-slate-500">
                                <?php echo e($voter->last_login_at ? $voter->last_login_at->timezone('Asia/Jakarta')->translatedFormat('d M, H:i') : 'Belum Pernah'); ?>

                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="py-12 px-6 text-center text-slate-400">
                                <div class="max-w-sm mx-auto space-y-1">
                                    <span class="text-2xl block">🎉</span>
                                    <p class="font-bold text-slate-700">Tidak ada mahasiswa yang ditemukan dengan filter ini.</p>
                                    <p class="text-[11px] text-slate-400">Seluruh mahasiswa pada kategori ini mungkin telah menyelesaikan hak suara mereka.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($unvotedList->hasPages()): ?>
            <div class="p-4 border-t border-slate-100">
                <?php echo e($unvotedList->links()); ?>

            </div>
        <?php endif; ?>

    </div>

</div>

<!-- ======================================================== -->
<!-- MODAL: TEMPLATE WHATSAPP BLAST PENGINGAT MAHASISWA       -->
<!-- ======================================================== -->
<div id="modal-wa-blast" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-4 shadow-2xl border border-slate-200 relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center space-x-2">
                <span class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold">
                    💬
                </span>
                <div>
                    <h3 class="text-base font-black text-slate-900 leading-snug">Format Pesan WhatsApp Pengingat Kelas</h3>
                    <p class="text-xs text-slate-400">Salin dan kirimkan ke grup WhatsApp angkatan/komti/dosen wali.</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modal-wa-blast').classList.add('hidden')" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 font-bold flex items-center justify-center transition">
                &times;
            </button>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Teks Pesan Siap Kirim:</label>
            <textarea id="wa-blast-textarea" rows="12" readonly class="w-full p-3.5 bg-slate-50 border border-slate-300 rounded-2xl font-mono text-xs text-slate-800 leading-relaxed focus:outline-none"><?php echo e($waBlastSample); ?></textarea>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-2">
            <button type="button" onclick="document.getElementById('modal-wa-blast').classList.add('hidden')" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                Tutup
            </button>
            <button type="button" onclick="copyWaBlastText()" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-md shadow-emerald-600/30 transition flex items-center space-x-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <span id="btn-copy-wa-label">Salin Teks Pesan WhatsApp</span>
            </button>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function copyWaBlastText() {
        const textarea = document.getElementById('wa-blast-textarea');
        textarea.select();
        textarea.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(textarea.value).then(() => {
            const btnLabel = document.getElementById('btn-copy-wa-label');
            btnLabel.innerText = '✅ Pesan Berhasil Disalin!';
            setTimeout(() => {
                btnLabel.innerText = 'Salin Teks Pesan WhatsApp';
            }, 3000);
        }).catch(err => {
            alert('Gagal menyalin teks, silakan salin manual.');
        });
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/admin/radar/index.blade.php ENDPATH**/ ?>