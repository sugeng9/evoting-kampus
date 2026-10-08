<?php $__env->startSection('title', 'Laporan & Berita Acara Hasil Tetap Ormawa - KPU Polbeng'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-8 w-full">

    <!-- Header Laporan Hasil Tetap -->
    <div class="bg-gradient-to-r from-navy-950 via-slate-900 to-navy-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative z-10">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30 mb-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Penetapan Pemenang Resmi Sidang Pleno KPU-M</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Laporan &amp; Berita Acara Hasil Tetap Pemira</h1>
                <p class="text-xs text-slate-300 mt-1 max-w-2xl leading-relaxed">
                    Setiap hasil pemilihan Ormawa memiliki batas waktu resmi. Selama waktu pemilihan berjalan, hasil bersifat <strong>Sementara (Quick Count)</strong>. Ketika <strong>Waktu Pemilihan Habis</strong> (atau ditutup panitia), hasil otomatis terkunci menjadi <strong>HASIL TETAP</strong> dan sistem menerbitkan Berita Acara Penetapan Pemenang Resmi.
                </p>
            </div>

            <!-- Master Action Toolbar -->
            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <a href="<?php echo e(route('admin.reports.all')); ?>" target="_blank" class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white text-xs font-black shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>🖨️ Cetak Buku Lengkap (10 Ormawa)</span>
                </a>

                <form action="<?php echo e(route('admin.reports.close_all')); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan dan menutup seluruh pemilihan 10 Ormawa sekarang? Seluruh hasil perolehan suara akan dikunci menjadi HASIL TETAP RESMI dan pemenang akan disahkan.');">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="w-full sm:w-auto px-4 py-3 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-black transition flex items-center justify-center space-x-1.5 shadow-md shadow-rose-600/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>🔒 Kunci &amp; Tetapkan Semua Hasil Tetap</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Info Bar Waktu & Integritas -->
        <div class="mt-6 pt-4 border-t border-white/10 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
            <div class="flex items-center space-x-3">
                <span class="flex items-center space-x-1.5 text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Status: <strong>Dokumen Terverifikasi Kriptografis SHA-256</strong></span>
                </span>
                <span>&bull;</span>
                <span>Zona Waktu: <strong class="text-white">Waktu Indonesia Barat (WIB)</strong></span>
            </div>
            <div class="font-mono text-slate-300 text-[11px]">
                Server: <?php echo e(now()->timezone('Asia/Jakarta')->translatedFormat('l, d F Y - H:i')); ?> WIB
            </div>
        </div>
    </div>

    <!-- Statistik Status Waktu Pemilihan & Kesiapan Hasil Tetap -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Ormawa -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-navy-900 text-white flex items-center justify-center font-black text-lg shadow-sm">
                <?php echo e($totalOrmawa); ?>

            </div>
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Total Ormawa</span>
                <span class="text-sm font-black text-slate-900">BEM, MPM, &amp; 8 HMJ Jurusan</span>
            </div>
        </div>

        <!-- Selesai (Hasil Tetap) -->
        <div class="bg-emerald-50/70 p-5 rounded-3xl border border-emerald-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-lg shadow-sm">
                <?php echo e($endedCount); ?>

            </div>
            <div>
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">Hasil Tetap Resmi</span>
                <span class="text-sm font-black text-emerald-950">Waktu Habis / Pemenang Terpilih</span>
            </div>
        </div>

        <!-- Masih Berjalan (Hasil Sementara) -->
        <div class="bg-amber-50/70 p-5 rounded-3xl border border-amber-200 shadow-sm flex items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-black text-lg shadow-sm">
                <?php echo e($ongoingCount); ?>

            </div>
            <div>
                <span class="text-xs font-bold text-amber-800 uppercase tracking-wider block">Hasil Sementara</span>
                <span class="text-sm font-black text-amber-950">Menunggu Waktu Pemilihan Habis</span>
            </div>
        </div>
    </div>

    <!-- Filter Tabs & Atur Batas Waktu Serentak -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Tabs Filter -->
        <div class="flex items-center space-x-2 w-full md:w-auto overflow-x-auto">
            <a href="<?php echo e(route('admin.reports.index', ['filter' => 'all'])); ?>" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap <?php echo e($filter === 'all' ? 'bg-navy-900 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'); ?>">
                Semua Ormawa (<?php echo e($totalOrmawa); ?>)
            </a>
            <a href="<?php echo e(route('admin.reports.index', ['filter' => 'ended'])); ?>" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap <?php echo e($filter === 'ended' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'); ?>">
                🔒 Hasil Tetap Resmi (<?php echo e($endedCount); ?>)
            </a>
            <a href="<?php echo e(route('admin.reports.index', ['filter' => 'ongoing'])); ?>" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap <?php echo e($filter === 'ongoing' ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'); ?>">
                ⏳ Masih Berjalan (<?php echo e($ongoingCount); ?>)
            </a>
        </div>

        <!-- Form Atur Batas Waktu Serentak -->
        <form action="<?php echo e(route('admin.reports.set_all_end_time')); ?>" method="POST" class="flex items-center space-x-2 w-full md:w-auto">
            <?php echo csrf_field(); ?>
            <span class="text-[11px] font-bold text-slate-500 whitespace-nowrap">Batas Waktu Serentak:</span>
            <input type="datetime-local" name="end_at" required class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <button type="submit" class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition whitespace-nowrap">
                Terapkan
            </button>
        </form>
    </div>

    <!-- Grid Kartu Berita Acara Per Ormawa -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <?php $__empty_1 = true; $__currentLoopData = $filteredReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $cat = $rep['category'];
                $winner = $rep['winner'];
                $pillar = $rep['pillar_badge'];
                $isEnded = $rep['is_ended'];
            ?>
            <div class="bg-white rounded-3xl border <?php echo e($isEnded ? 'border-emerald-300 ring-2 ring-emerald-500/10' : 'border-slate-200'); ?> p-6 shadow-sm hover:shadow-md transition flex flex-col justify-between space-y-6">
                
                <!-- Card Top Header -->
                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-8 h-8 rounded-xl <?php echo e($isEnded ? 'bg-emerald-700' : 'bg-navy-950'); ?> text-white font-mono font-bold text-xs flex items-center justify-center flex-shrink-0">
                                #<?php echo e($cat->display_order); ?>

                            </span>
                            <div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border <?php echo e($pillar['badge_color']); ?>">
                                    <?php echo e($pillar['tag']); ?>: <?php echo e($pillar['title']); ?>

                                </span>
                                <span class="text-[10px] text-slate-400 font-mono ml-1.5"><?php echo e($rep['ba_number']); ?></span>
                            </div>
                        </div>

                        <!-- Status Badge Waktu Pemilihan -->
                        <?php if($isEnded): ?>
                            <span class="inline-flex items-center space-x-1 px-3 py-1 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-900 border border-emerald-300">
                                <svg class="w-3.5 h-3.5 text-emerald-700" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                                </svg>
                                <span>HASIL TETAP (WAKTU HABIS)</span>
                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-[11px] font-black bg-amber-100 text-amber-900 border border-amber-300">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span>HASIL SEMENTARA (BERJALAN)</span>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h3 class="text-base font-black text-slate-900 leading-snug"><?php echo e($cat->name); ?></h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-1"><?php echo e($cat->description); ?></p>
                    </div>

                    <!-- Keterangan Batas Waktu Pemilihan -->
                    <div class="p-2.5 rounded-xl <?php echo e($isEnded ? 'bg-emerald-50/60 border border-emerald-200 text-emerald-900' : 'bg-amber-50/60 border border-amber-200 text-amber-900'); ?> text-xs flex items-center justify-between">
                        <div class="flex items-center space-x-1.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>
                                <?php if($isEnded): ?>
                                    Waktu pemilihan berakhir: <strong><?php echo e($rep['end_time_formatted']); ?></strong>
                                <?php else: ?>
                                    Batas waktu pemilihan: <strong><?php echo e($rep['end_time_formatted']); ?></strong>
                                <?php endif; ?>
                            </span>
                        </div>

                        <?php if(!$isEnded): ?>
                            <span class="text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded">
                                Masih Buka
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Highlight Pemenang Terpilih / Kandidat Unggul Sementara -->
                <div class="p-4 rounded-2xl <?php echo e($isEnded && $winner ? 'bg-gradient-to-r from-emerald-50 via-teal-50/70 to-emerald-50 border-2 border-emerald-300 shadow-sm' : ($winner ? 'bg-amber-50/50 border border-amber-200' : ($rep['is_tie'] ? 'bg-amber-50 border border-amber-200' : 'bg-slate-50 border border-slate-200'))); ?>">
                    <?php if($winner): ?>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-[11px] font-black uppercase tracking-wider <?php echo e($isEnded ? 'text-emerald-900' : 'text-amber-900'); ?> flex items-center space-x-1.5">
                                <?php if($isEnded): ?>
                                    <span>👑 PEMENANG RESMI TERPILIH (HASIL TETAP)</span>
                                <?php else: ?>
                                    <span>⚡ KANDIDAT UNGGUL SEMENTARA</span>
                                <?php endif; ?>
                            </span>
                            <span class="px-2 py-0.5 rounded <?php echo e($isEnded ? 'bg-emerald-600' : 'bg-amber-600'); ?> text-white font-mono text-[10px] font-bold">
                                Paslon 0<?php echo e($winner['number']); ?>

                            </span>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-xl bg-white border-2 <?php echo e($isEnded ? 'border-emerald-400' : 'border-amber-300'); ?> overflow-hidden flex-shrink-0 flex items-center justify-center shadow-sm">
                                <?php if($winner['photo_path']): ?>
                                    <img src="<?php echo e(asset($winner['photo_path'])); ?>" alt="<?php echo e($winner['leader_name']); ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="text-base font-black <?php echo e($isEnded ? 'text-emerald-800' : 'text-amber-800'); ?>">0<?php echo e($winner['number']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-black text-slate-900 truncate"><?php echo e($winner['leader_name']); ?></h4>
                                <?php if($winner['vice_leader_name']): ?>
                                    <p class="text-xs text-slate-600 truncate">&amp; <?php echo e($winner['vice_leader_name']); ?></p>
                                <?php else: ?>
                                    <p class="text-[11px] text-slate-500 truncate">NIM: <?php echo e($winner['leader_nim']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="text-base font-black <?php echo e($isEnded ? 'text-emerald-700' : 'text-amber-700'); ?> font-mono"><?php echo e(number_format($winner['votes'])); ?></div>
                                <span class="text-[10px] font-bold <?php echo e($isEnded ? 'text-emerald-600' : 'text-amber-600'); ?>">Suara (<?php echo e($winner['percentage']); ?>%)</span>
                            </div>
                        </div>

                        <?php if(!$isEnded): ?>
                            <div class="text-[10px] text-amber-700 italic mt-2 pt-2 border-t border-amber-200">
                                * Catatan: Perolehan suara masih dapat berubah hingga batas waktu pemilihan habis.
                            </div>
                        <?php else: ?>
                            <div class="text-[10px] text-emerald-800 font-semibold mt-2 pt-2 border-t border-emerald-200 flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Pemenang sah dan mengikat untuk Periode 2026/2027.</span>
                            </div>
                        <?php endif; ?>
                    <?php elseif($rep['is_tie']): ?>
                        <div class="text-center py-2">
                            <span class="text-xs font-bold text-amber-800">Perolehan Suara Seimbang (Imbang)</span>
                            <p class="text-[10px] text-amber-600 mt-0.5">Menunggu keputusan sidang pleno lanjutan KPU Mahasiswa.</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-2">
                            <span class="text-xs font-semibold text-slate-500">Belum ada suara masuk di bilik ini</span>
                            <p class="text-[10px] text-slate-400 mt-0.5">Menunggu mahasiswa menggunakan hak suara.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Statistik Partisipasi Ringkas -->
                <div class="space-y-2 pt-2 border-t border-slate-100 text-xs">
                    <div class="flex items-center justify-between text-slate-600">
                        <span>Partisipasi Pemilih:</span>
                        <span class="font-bold text-slate-900 font-mono">
                            <?php echo e(number_format($rep['total_ballots'])); ?> / <?php echo e(number_format($rep['category_dpt'])); ?>

                            <span class="text-emerald-600">(<?php echo e($rep['turnout_percentage']); ?>%)</span>
                        </span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden flex">
                        <div class="bg-emerald-500 h-2 transition-all duration-500" style="width: <?php echo e($rep['turnout_percentage']); ?>%"></div>
                        <div class="bg-amber-400 h-2 transition-all duration-500" style="width: <?php echo e($rep['not_voted_percentage']); ?>%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <span>Golput / Belum: <?php echo e(number_format($rep['not_voted_count'])); ?> (<?php echo e($rep['not_voted_percentage']); ?>%)</span>
                        <span class="font-mono text-[10px] text-slate-400">Hash: <?php echo e(substr($rep['document_checksum'], 0, 10)); ?>...</span>
                    </div>
                </div>

                <!-- Panel Aksi: Kontrol Waktu & Tombol Cetak -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    
                    <!-- Tombol Utama Cetak Berita Acara -->
                    <a href="<?php echo e(route('admin.reports.category', $cat->id)); ?>" target="_blank" class="w-full py-2.5 px-4 <?php echo e($isEnded ? 'bg-emerald-700 hover:bg-emerald-800' : 'bg-navy-900 hover:bg-slate-800'); ?> text-white rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2 shadow-sm">
                        <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        <span>
                            <?php if($isEnded): ?>
                                Cetak Berita Acara Penetapan Pemenang (Hasil Tetap) &rarr;
                            <?php else: ?>
                                Lihat Draft Rekapitulasi Sementara (Belum Tetap) &rarr;
                            <?php endif; ?>
                        </span>
                    </a>

                    <!-- Tombol Aksi Tambahan untuk Mengendalikan Waktu Habis -->
                    <div class="flex items-center gap-2 pt-1">
                        <?php if(!$isEnded): ?>
                            <!-- Tombol Kunci / Selesaikan Pemilihan Sekarang -->
                            <form action="<?php echo e(route('admin.reports.close_category', $cat->id)); ?>" method="POST" class="flex-1" onsubmit="return confirm('Akhiri waktu pemilihan untuk <?php echo e($cat->name); ?> sekarang? Kotak suara akan ditutup dan hasil dikunci sebagai HASIL TETAP RESMI.');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="w-full py-2 px-3 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-[11px] font-bold transition flex items-center justify-center space-x-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span>🔒 Kunci &amp; Tetapkan Pemenang</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <!-- Tombol Buka Kembali Pemilihan Jika Ingin Memperpanjang -->
                            <form action="<?php echo e(route('admin.reports.reopen_category', $cat->id)); ?>" method="POST" class="flex-1" onsubmit="return confirm('Buka kembali pemilihan <?php echo e($cat->name); ?> untuk menerima suara baru?');">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="w-full py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[11px] font-bold transition flex items-center justify-center space-x-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                    </svg>
                                    <span>🔓 Buka Kembali Pemilihan</span>
                                </button>
                            </form>
                        <?php endif; ?>

                        <!-- Form Cepat Set Waktu Selesai -->
                        <button type="button" onclick="document.getElementById('edit-time-<?php echo e($cat->id); ?>').classList.toggle('hidden')" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-[11px] font-bold transition flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Atur Waktu</span>
                        </button>
                    </div>

                    <!-- Dropdown Input Batas Waktu Tersembunyi -->
                    <div id="edit-time-<?php echo e($cat->id); ?>" class="hidden p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-2">
                        <form action="<?php echo e(route('admin.reports.set_end_time', $cat->id)); ?>" method="POST" class="space-y-2">
                            <?php echo csrf_field(); ?>
                            <label class="block font-bold text-slate-700 text-[11px]">Ubah Batas Waktu Pemilihan Habis:</label>
                            <div class="flex items-center space-x-2">
                                <input type="datetime-local" name="end_at" value="<?php echo e($cat->end_at ? $cat->end_at->format('Y-m-d\TH:i') : ''); ?>" required class="flex-1 px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-bold text-slate-800">
                                <button type="submit" class="px-3 py-1.5 bg-navy-900 text-white rounded-lg text-xs font-bold hover:bg-slate-800">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                </div>

            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-2 p-12 text-center bg-white rounded-3xl border border-slate-200 text-slate-400 font-medium">
                Tidak ada kategori pemilihan ormawa yang sesuai dengan filter yang dipilih.
            </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/admin/reports/index.blade.php ENDPATH**/ ?>