<?php $__env->startSection('title', 'Dashboard Pemilih'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-8 max-w-7xl mx-auto">

    <!-- Card Profil Pemilih (Desktop & Mobile Responsive) -->
    <div class="bg-gradient-to-r from-navy-900 via-slate-900 to-navy-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute right-0 top-0 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-2xl sm:text-3xl font-black text-white shadow-lg shadow-emerald-500/30 flex-shrink-0">
                    <?php echo e(strtoupper(substr($voter->name, 0, 1))); ?>

                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white"><?php echo e($voter->name); ?></h1>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            DPT Terverifikasi
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1">
                        NIM: <span class="font-mono font-semibold text-white"><?php echo e($voter->nim); ?></span> &bull; 
                        <span class="text-emerald-300 font-semibold"><?php echo e($voter->study_program); ?></span>
                        <?php if($voter->angkatan): ?>
                            &bull; <span class="text-slate-200 font-medium">Angkatan <?php echo e($voter->angkatan); ?></span>
                        <?php endif; ?>
                        &bull; <?php echo e($voter->faculty); ?>

                    </p>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-xs text-slate-400">
                        <span class="flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full <?php echo e($setting->isOpen() ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400'); ?>"></span>
                            <span class="text-slate-300"><?php echo e($setting->isOpen() ? 'Sesi Pemira Berlangsung' : 'Sesi Pemira Ditutup'); ?></span>
                        </span>
                        <span>&bull;</span>
                        <span>Batas: <strong class="text-slate-200"><?php echo e($setting->voting_ends_at ? $setting->voting_ends_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Hingga Panitia Menutup'); ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Progress Box -->
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/10 text-center w-full sm:w-auto min-w-[220px] flex-shrink-0">
                <span class="text-xs text-slate-300 block font-medium uppercase tracking-wider">Partisipasi Hak Suara</span>
                <span class="text-2xl sm:text-3xl font-black text-emerald-400 mt-0.5 block font-mono">
                    <?php echo e($totalVoted); ?> / <?php echo e($totalEligible); ?>

                </span>
                <span class="text-[11px] text-slate-300 block mt-1 font-semibold">
                    <?php echo e($allCompleted ? '🎉 Seluruh hak suara telah digunakan' : 'Masih ada kategori yang belum dicoblos'); ?>

                </span>
            </div>
        </div>
    </div>

    <!-- 2-COLUMN WORKSTATION GRID (DESKTOP: 2-COLUMN, MOBILE: 1-COLUMN NATURAL FLOW) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- ============================================== -->
        <!-- LEFT COLUMN: SIDEBAR (DESKTOP) / TOP (MOBILE)  -->
        <!-- ============================================== -->
        <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-24">
            
            <!-- Quick Count Live Banner -->
            <a href="<?php echo e(route('quick_count')); ?>" class="flex items-center justify-between gap-3 p-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-navy-900 rounded-2xl text-white shadow-lg shadow-emerald-600/20 hover:shadow-xl transition transform hover:-translate-y-0.5">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-200 block">Hasil Real-Time</span>
                        <span class="text-xs font-black text-white">Live Quick Count Suara &rarr;</span>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-white text-emerald-800 text-[11px] font-black shadow-sm flex-shrink-0">
                    Buka
                </span>
            </a>

            <!-- Panduan 3 Hak Suara Anda -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
                <div class="flex items-center space-x-2 pb-2 border-b border-slate-100">
                    <div class="w-6 h-6 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
                        🏛️
                    </div>
                    <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Ketentuan 3 Hak Suara Anda</h3>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Sesuai ketentuan KPU Mahasiswa Politeknik Negeri Bengkalis, setiap mahasiswa terdaftar memiliki 3 hak suara sah:
                </p>
                <div class="space-y-2 text-xs">
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start space-x-2.5">
                        <span class="px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800 font-mono text-[10px] font-bold">1</span>
                        <div>
                            <strong class="text-slate-800 text-[11px] block">BEM Polbeng (Universitas)</strong>
                            <span class="text-[10px] text-slate-500">Memilih Pasangan Presiden &amp; Wakil Presiden Mahasiswa</span>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start space-x-2.5">
                        <span class="px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-800 font-mono text-[10px] font-bold">2</span>
                        <div>
                            <strong class="text-slate-800 text-[11px] block">MPM Polbeng (Legislatif)</strong>
                            <span class="text-[10px] text-slate-500">Memilih Ketua Umum MPM (Tanpa Wakil)</span>
                        </div>
                    </div>
                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-start space-x-2.5">
                        <span class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-800 font-mono text-[10px] font-bold">3</span>
                        <div>
                            <strong class="text-slate-800 text-[11px] block">HMJ (<?php echo e($voter->study_program); ?>)</strong>
                            <span class="text-[10px] text-slate-500">Memilih Ketua Himpunan Mahasiswa Jurusan</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Transparansi Pemilih: Mahasiswa Sudah Memilih vs Belum Memilih -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <span class="inline-flex items-center space-x-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 mb-1">
                        <span>Transparansi Asas Luber Jurdil</span>
                    </span>
                    <h3 class="text-sm font-black text-slate-900 tracking-tight">Partisipasi Mahasiswa</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Komparasi pemilih yang telah dan belum mencoblos secara real-time.</p>
                </div>

                <!-- 1. Tingkat Kampus Polbeng -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Tingkat Kampus</span>
                            <h4 class="text-xs font-black text-slate-800">Politeknik Negeri Bengkalis</h4>
                        </div>
                        <span class="text-[11px] font-mono font-bold text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200 shadow-sm">
                            <?php echo e(number_format($participationTransparency['global']['total_dpt'])); ?> DPT
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200">
                            <span class="text-[10px] font-bold text-emerald-800 block">Sudah</span>
                            <div class="text-base font-black text-emerald-600 font-mono">
                                <?php echo e(number_format($participationTransparency['global']['voted'])); ?>

                                <span class="text-[10px] font-semibold">(<?php echo e($participationTransparency['global']['voted_pct']); ?>%)</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200">
                            <span class="text-[10px] font-bold text-amber-800 block">Belum</span>
                            <div class="text-base font-black text-amber-600 font-mono">
                                <?php echo e(number_format($participationTransparency['global']['not_voted'])); ?>

                                <span class="text-[10px] font-semibold">(<?php echo e($participationTransparency['global']['not_voted_pct']); ?>%)</span>
                            </div>
                        </div>
                    </div>

                    <div class="w-full bg-amber-100 rounded-full h-2 overflow-hidden flex">
                        <div class="bg-emerald-500 h-2 transition-all duration-700" style="width: <?php echo e($participationTransparency['global']['voted_pct']); ?>%"></div>
                        <div class="bg-amber-400 h-2 transition-all duration-700" style="width: <?php echo e($participationTransparency['global']['not_voted_pct']); ?>%"></div>
                    </div>
                </div>

                <!-- 2. Tingkat Jurusan Mahasiswa -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block">Tingkat Jurusan Anda</span>
                            <h4 class="text-xs font-black text-slate-800"><?php echo e($participationTransparency['department']['name']); ?></h4>
                        </div>
                        <span class="text-[11px] font-mono font-bold text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200 shadow-sm">
                            <?php echo e(number_format($participationTransparency['department']['total_dpt'])); ?> DPT
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200">
                            <span class="text-[10px] font-bold text-emerald-800 block">Rekan Sudah</span>
                            <div class="text-base font-black text-emerald-600 font-mono">
                                <?php echo e(number_format($participationTransparency['department']['voted'])); ?>

                                <span class="text-[10px] font-semibold">(<?php echo e($participationTransparency['department']['voted_pct']); ?>%)</span>
                            </div>
                        </div>
                        <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200">
                            <span class="text-[10px] font-bold text-amber-800 block">Rekan Belum</span>
                            <div class="text-base font-black text-amber-600 font-mono">
                                <?php echo e(number_format($participationTransparency['department']['not_voted'])); ?>

                                <span class="text-[10px] font-semibold">(<?php echo e($participationTransparency['department']['not_voted_pct']); ?>%)</span>
                            </div>
                        </div>
                    </div>

                    <div class="w-full bg-amber-100 rounded-full h-2 overflow-hidden flex">
                        <div class="bg-emerald-500 h-2 transition-all duration-700" style="width: <?php echo e($participationTransparency['department']['voted_pct']); ?>%"></div>
                        <div class="bg-amber-400 h-2 transition-all duration-700" style="width: <?php echo e($participationTransparency['department']['not_voted_pct']); ?>%"></div>
                    </div>
                </div>

            </div>

            <!-- Tautan Verifikasi Kuitansi -->
            <a href="<?php echo e(route('verify_ballot')); ?>" class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-emerald-300 transition shadow-sm flex items-center justify-between text-xs group">
                <div class="flex items-center space-x-2.5">
                    <span class="text-base">🔍</span>
                    <div>
                        <strong class="text-slate-800 block group-hover:text-emerald-700 transition">Audit Kuitansi Suara</strong>
                        <span class="text-[11px] text-slate-400">Verifikasi tanda terima digital Anda</span>
                    </div>
                </div>
                <span class="text-slate-400 group-hover:text-emerald-600">&rarr;</span>
            </a>

        </div>

        <!-- ============================================== -->
        <!-- RIGHT COLUMN: MAIN VOTING BOOTHS               -->
        <!-- ============================================== -->
        <div class="lg:col-span-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">3 Bilik Suara Pemira Anda</h2>
                    <p class="text-xs text-slate-500 mt-0.5">BEM Polbeng &bull; MPM Polbeng &bull; HMJ (<?php echo e($voter->study_program); ?>)</p>
                </div>
                <div class="text-xs font-mono font-bold text-slate-600 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm self-start sm:self-auto">
                    Partisipasi: <strong class="text-emerald-600"><?php echo e($totalVoted); ?></strong> / <?php echo e($totalEligible); ?> Selesai
                </div>
            </div>

        <div class="space-y-6">
            <?php $__currentLoopData = $categoriesData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $cat = $item['category'];
                    $hasVoted = $item['has_voted'];
                    $schedStatus = $cat->getScheduleStatus();
                    $isScheduleOpen = $cat->isScheduleOpen();
                    $targetTimestamp = $cat->getTargetTimestamp();
                    $canVote = (!$hasVoted && $setting->isOpen() && $isScheduleOpen);
                    $pillar = $cat->getPillarBadge();
                ?>
                <div class="bg-white rounded-3xl border-2 <?php echo e($hasVoted ? 'border-emerald-200 bg-emerald-50/10' : ($canVote ? 'border-slate-200 hover:border-emerald-500 shadow-sm hover:shadow-lg' : 'border-slate-200 bg-slate-50/40')); ?> p-6 sm:p-8 transition duration-200">
                    
                    <!-- Top Category Header -->
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-5 border-b border-slate-100">
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <span class="px-2.5 py-0.5 rounded-lg bg-navy-900 text-white font-mono text-[11px] font-bold">
                                    <?php echo e($cat->code); ?>

                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border <?php echo e($pillar['badge_color']); ?>">
                                    <?php echo e($pillar['tag']); ?>: <?php echo e($pillar['title']); ?>

                                </span>
                                <span class="text-[11px] text-slate-400 font-medium hidden sm:inline">
                                    &bull; <?php echo e($pillar['level']); ?>

                                </span>
                            </div>
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight"><?php echo e($cat->name); ?></h3>
                            <p class="text-xs text-slate-500 mt-1">
                                <?php echo e($cat->description ?: 'Gunakan hak suara Anda secara bijak untuk menentukan arah kepemimpinan organisasi mahasiswa.'); ?>

                            </p>
                        </div>

                        <!-- Status Badge Hak Suara & Jadwal -->
                        <div class="flex flex-col sm:items-end gap-1 flex-shrink-0">
                            <?php if($hasVoted): ?>
                                <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    <svg class="w-4 h-4 text-emerald-700" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>HAK SUARA TELAH DIGUNAKAN</span>
                                </span>
                            <?php elseif(!$setting->isOpen()): ?>
                                <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    <span>SESI PEMIRA DITUTUP</span>
                                </span>
                            <?php elseif($cat->status_override === 'FORCE_CLOSED'): ?>
                                <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    <span>DITUTUP OLEH PANITIA</span>
                                </span>
                            <?php elseif($schedStatus === 'OPEN'): ?>
                                <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>SEDANG BERLANGSUNG</span>
                                </span>
                            <?php elseif($schedStatus === 'UPCOMING'): ?>
                                <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span>BELUM DIMULAI (AKAN DATANG)</span>
                                </span>
                            <?php elseif($schedStatus === 'ENDED'): ?>
                                <span class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-200 text-slate-700 border border-slate-300">
                                    <span>WAKTU PEMILIHAN BERAKHIR</span>
                                </span>
                            <?php endif; ?>
                            <span class="text-[11px] text-slate-400 font-medium">
                                <?php echo e($cat->candidates->count()); ?> Pasangan Calon Terdaftar
                            </span>
                        </div>
                    </div>

                    <!-- Jadwal Waktu & Live Countdown Box -->
                    <div class="my-5 p-4 sm:p-5 rounded-2xl <?php echo e($schedStatus === 'OPEN' ? 'bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50/30 border border-emerald-200' : ($schedStatus === 'UPCOMING' ? 'bg-amber-50 border border-amber-200' : 'bg-slate-100 border border-slate-200')); ?> flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        
                        <!-- Rincian Waktu Mulai & Selesai -->
                        <div class="space-y-1 text-xs">
                            <span class="font-extrabold uppercase tracking-wider <?php echo e($schedStatus === 'OPEN' ? 'text-emerald-800' : ($schedStatus === 'UPCOMING' ? 'text-amber-800' : 'text-slate-600')); ?> block">
                                Jadwal Waktu Pemilihan
                            </span>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-slate-700">
                                <div class="flex items-center space-x-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>Mulai: <strong class="font-semibold text-slate-900"><?php echo e($cat->start_at ? $cat->start_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Langsung Buka'); ?></strong></span>
                                </div>
                                <span class="hidden sm:inline text-slate-300">&bull;</span>
                                <div class="flex items-center space-x-1.5">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>Berakhir: <strong class="font-semibold text-slate-900"><?php echo e($cat->end_at ? $cat->end_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : 'Hingga Panitia Menutup'); ?></strong></span>
                                </div>
                            </div>
                        </div>

                        <!-- Live Timer Hitung Mundur -->
                        <?php if($targetTimestamp): ?>
                            <div class="category-timer-wrapper bg-white/90 backdrop-blur-sm px-4 py-2.5 rounded-xl border <?php echo e($schedStatus === 'OPEN' ? 'border-emerald-300' : 'border-amber-300'); ?> shadow-sm text-right flex-shrink-0"
                                 data-target="<?php echo e($targetTimestamp); ?>"
                                 data-status="<?php echo e($schedStatus); ?>"
                                 id="timer-cat-<?php echo e($cat->id); ?>">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider block <?php echo e($schedStatus === 'OPEN' ? 'text-emerald-700' : 'text-amber-700'); ?>">
                                    <?php echo e($schedStatus === 'OPEN' ? 'Sisa Waktu Memilih' : 'Dibuka Dalam'); ?>

                                </span>
                                <div class="font-mono font-black text-base sm:text-lg text-slate-900 tracking-wider timer-display">
                                    -- : -- : --
                                </div>
                            </div>
                        <?php elseif($schedStatus === 'ENDED'): ?>
                            <div class="bg-slate-200 px-3.5 py-2 rounded-xl text-slate-600 text-xs font-bold flex-shrink-0">
                                Sesi Pemilihan Telah Berakhir
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Transparansi Kategori: Sudah Memilih vs Belum Memilih -->
                    <div class="my-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Partisipasi Kategori:</span>
                            <span class="font-mono text-emerald-700 font-bold">
                                Sudah: <?php echo e(number_format($item['stats']['voted_count'])); ?> (<?php echo e($item['stats']['voted_pct']); ?>%)
                            </span>
                            <span class="text-slate-300">&bull;</span>
                            <span class="font-mono text-amber-700 font-bold">
                                Belum: <?php echo e(number_format($item['stats']['not_voted_count'])); ?> (<?php echo e($item['stats']['not_voted_pct']); ?>%)
                            </span>
                        </div>
                        <div class="w-full sm:w-48 bg-amber-100 rounded-full h-2.5 overflow-hidden flex flex-shrink-0">
                            <div class="bg-emerald-500 h-2.5 transition-all duration-500" style="width: <?php echo e($item['stats']['voted_pct']); ?>%" title="Sudah Memilih: <?php echo e($item['stats']['voted_pct']); ?>%"></div>
                            <div class="bg-amber-400 h-2.5 transition-all duration-500" style="width: <?php echo e($item['stats']['not_voted_pct']); ?>%" title="Belum Memilih: <?php echo e($item['stats']['not_voted_pct']); ?>%"></div>
                        </div>
                    </div>

                    <!-- Preview Paslon & Wapaslon -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                <?php echo e($cat->getPillarType() === 'BEM' ? 'Pasangan Calon Kandidat (Paslon & Wapaslon)' : 'Kandidat Calon Ketua'); ?>

                            </span>
                            <span class="text-[11px] text-slate-400 font-medium">
                                Pelajari visi misi sebelum mencoblos
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php $__currentLoopData = $cat->candidates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/70 hover:bg-white hover:border-slate-300 transition flex items-center space-x-3.5">
                                    <div class="w-14 h-14 rounded-2xl bg-white border-2 border-slate-200 flex-shrink-0 overflow-hidden flex items-center justify-center shadow-sm">
                                        <?php if($c->photo_path): ?>
                                            <img src="<?php echo e(asset($c->photo_path)); ?>" alt="<?php echo e($c->leader_name); ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <span class="text-base font-black text-slate-700">0<?php echo e($c->candidate_number); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center space-x-1.5">
                                            <span class="px-1.5 py-0.5 rounded bg-navy-900 text-white font-mono text-[10px] font-black">
                                                0<?php echo e($c->candidate_number); ?>

                                            </span>
                                            <h4 class="text-xs font-black text-slate-900 truncate"><?php echo e($c->leader_name); ?></h4>
                                        </div>
                                        <?php if($c->vice_leader_name): ?>
                                            <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                                Wakil: <span class="font-semibold text-slate-700"><?php echo e($c->vice_leader_name); ?></span>
                                            </p>
                                        <?php endif; ?>
                                        <p class="text-[10px] text-slate-400 italic truncate mt-1">
                                            "<?php echo e(Str::limit($c->vision, 45)); ?>"
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <!-- Action Button Bar -->
                    <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="text-xs text-slate-400">
                            <?php if($canVote): ?>
                                <span class="text-emerald-700 font-semibold flex items-center space-x-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                    <span>Bilik suara siap digunakan. Siapkan PIN Anda untuk validasi.</span>
                                </span>
                            <?php elseif($hasVoted): ?>
                                <span class="text-slate-500 font-medium">Partisipasi Anda telah tercatat secara sah di blockchain/kuitansi digital.</span>
                            <?php elseif($schedStatus === 'UPCOMING'): ?>
                                <span class="text-amber-700 font-medium">Pintu bilik suara akan terbuka otomatis saat jam mulai tercapai.</span>
                            <?php elseif($schedStatus === 'ENDED'): ?>
                                <span class="text-slate-500 font-medium">Batas waktu pemungutan suara untuk kategori ini telah lewat.</span>
                            <?php endif; ?>
                        </div>

                        <div>
                            <?php if($hasVoted): ?>
                                <div class="py-2.5 px-5 rounded-xl bg-slate-100 border border-slate-200 text-slate-500 text-xs font-bold flex items-center space-x-2 cursor-not-allowed">
                                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Suara Tersimpan &amp; Terverifikasi</span>
                                </div>
                            <?php elseif($canVote): ?>
                                <a href="<?php echo e(route('voter.ballot', $cat->id)); ?>" class="py-3 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-extrabold flex items-center space-x-2 shadow-lg shadow-emerald-600/25 transition transform hover:-translate-y-0.5">
                                    <span>Masuk ke Bilik Suara &amp; Coblos</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            <?php elseif($schedStatus === 'UPCOMING'): ?>
                                <button disabled class="py-2.5 px-5 rounded-xl bg-amber-100 border border-amber-200 text-amber-800 text-xs font-bold flex items-center space-x-2 cursor-not-allowed">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span>Bilik Suara Belum Dibuka</span>
                                </button>
                            <?php elseif($schedStatus === 'ENDED'): ?>
                                <button disabled class="py-2.5 px-5 rounded-xl bg-slate-200 text-slate-500 text-xs font-bold cursor-not-allowed">
                                    Waktu Pemilihan Berakhir
                                </button>
                            <?php else: ?>
                                <button disabled class="py-2.5 px-5 rounded-xl bg-slate-200 text-slate-500 text-xs font-bold cursor-not-allowed">
                                    Sesi Ditutup
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>

    <!-- Security Guarantee Badge -->
    <div class="bg-slate-100/80 rounded-2xl p-5 border border-slate-200/60 text-xs text-slate-600 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 shadow-sm">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004 11a7.96 7.96 0 002.39 5.657"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-slate-800">Prinsip Asas LUBER JURDIL & Secret Ballot</p>
                <p class="text-[11px] text-slate-500">Pilihan Anda dipisahkan secara kriptografis dari data identitas NIM menggunakan UUIDv4 acak.</p>
            </div>
        </div>
        <a href="<?php echo e(route('verify_ballot')); ?>" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 underline flex-shrink-0">
            Cek Audit Bukti Suara &rarr;
        </a>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function padZero(num) {
        return num.toString().padStart(2, '0');
    }

    function updateCategoryTimers() {
        const now = Math.floor(Date.now() / 1000);

        document.querySelectorAll('.category-timer-wrapper').forEach(wrapper => {
            const target = parseInt(wrapper.getAttribute('data-target'));
            const status = wrapper.getAttribute('data-status');
            const display = wrapper.querySelector('.timer-display');

            if (!target || !display) return;

            const diff = target - now;

            if (diff <= 0) {
                if (status === 'UPCOMING') {
                    display.innerText = '00 : 00 : 00';
                    setTimeout(() => window.location.reload(), 2500);
                } else {
                    display.innerText = 'Waktu Selesai';
                }
                return;
            }

            const days = Math.floor(diff / 86400);
            const hours = Math.floor((diff % 86400) / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            if (days > 0) {
                display.innerText = `${days}h ${padZero(hours)}:${padZero(minutes)}:${padZero(seconds)}`;
            } else {
                display.innerText = `${padZero(hours)} : ${padZero(minutes)} : ${padZero(seconds)}`;
            }
        });
    }

    updateCategoryTimers();
    setInterval(updateCategoryTimers, 1000);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/voter/dashboard.blade.php ENDPATH**/ ?>