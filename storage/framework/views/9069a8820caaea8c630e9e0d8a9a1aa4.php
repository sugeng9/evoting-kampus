<?php $__env->startSection('title', 'Manajemen DPT & Akun Pemilih - KPU Kampus'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6 w-full">

    <!-- Header Panel DPT & Akun Pemilih -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-navy-900 text-white p-6 sm:p-8 rounded-3xl shadow-xl">
        <div>
            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold border border-emerald-500/30 mb-2">
                <span>Daftar Pemilih Tetap (DPT) &bull; Otentikasi Kata Sandi Mandiri</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight">DPT &amp; Status Akun Pemilih</h1>
            <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                Data DPT bersumber dari hasil saringan akademik resmi kampus. Mahasiswa yang sah masuk DPT dapat melakukan pendaftaran mandiri dengan Kata Sandi rahasia masing-masing.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="<?php echo e(route('admin.voters.import')); ?>" class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-teal-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>📥 Upload DPT / DPS BAAK</span>
            </a>
            <a href="<?php echo e(route('admin.voters.dps')); ?>" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition flex items-center space-x-2 shadow-lg shadow-indigo-600/30">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span>🔍 Saringan DPS Kampus</span>
            </a>
            <a href="<?php echo e(route('admin.voters.export_ormawa')); ?>" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center space-x-2" title="Unduh Salinan DPT Resmi untuk Saksi Paslon & Ormawa">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Salinan Saksi Ormawa</span>
            </a>
            <a href="<?php echo e(route('admin.voters.export')); ?>" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Unduh Rekap DPT</span>
            </a>

            <form action="<?php echo e(route('admin.voters.clear_dummy')); ?>" method="POST" onsubmit="return confirm('PERINGATAN SOSIALISASI: Ini akan mengosongkan seluruh data dummy pemilih, kuitansi suara simulasi, dan histori uji coba agar sistem bersih untuk presentasi. Lanjutkan?')">
                <?php echo csrf_field(); ?>
                <button type="submit" class="px-3.5 py-2.5 rounded-xl bg-rose-700/80 hover:bg-rose-600 text-white text-xs font-bold transition flex items-center space-x-1.5 shadow-sm" title="Bersihkan Seluruh Data Dummy & Riwayat Uji Coba">
                    <svg class="w-4 h-4 text-rose-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Bersihkan Data Dummy</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Banner Integritas: Sistem Pendaftaran Mandiri & Kerahasiaan Kata Sandi -->
    <div class="bg-gradient-to-r from-slate-900 via-navy-900 to-indigo-950 text-white p-5 sm:p-6 rounded-3xl border border-indigo-500/30 shadow-md">
        <div class="flex items-start space-x-4">
            <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0 border border-emerald-500/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div class="space-y-1">
                <div class="flex items-center space-x-2">
                    <h3 class="font-extrabold text-sm text-white">Prinsip Kerahasiaan Kata Sandi Mandiri (Asas Luber Jurdil)</h3>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold text-[10px] border border-emerald-500/30">Bcrypt Protected</span>
                </div>
                <p class="text-xs text-slate-300 leading-relaxed max-w-4xl">
                    Mahasiswa yang sah masuk DPT mendaftar secara mandiri menggunakan <strong>NIM &amp; Tanggal Lahir</strong>, lalu membuat <strong>Kata Sandi Pribadi</strong> yang dienkripsi secara aman. Panitia KPU tidak dapat melihat kata sandi pemilih, demi menjamin netralitas mutlak. Jika mahasiswa mengalami kendala lupa sandi di bilik suara, Panitia dapat memberikan bantuan melalui tombol <strong>Reset Sandi Darurat</strong>.
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Stats Rekapitulasi DPT & Status Akun Pemilih -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">DPT Sah Terkunci</span>
            <span class="text-2xl font-black text-slate-900 mt-1 block"><?php echo e(number_format($totalDPT)); ?></span>
            <span class="text-[10px] text-slate-400 block">Mahasiswa Berhak</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600 block">Sudah Aktif Mandiri</span>
            <span class="text-2xl font-black text-emerald-700 mt-1 block"><?php echo e(number_format($registeredCount)); ?></span>
            <span class="text-[10px] text-emerald-600 block">Membuat Password</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 block">Belum Daftar</span>
            <span class="text-2xl font-black text-amber-600 mt-1 block"><?php echo e(number_format($unregisteredCount)); ?></span>
            <span class="text-[10px] text-amber-600 block">Bisa Daftar di Web</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600 block">TMS Dicoret</span>
            <span class="text-2xl font-black text-rose-700 mt-1 block"><?php echo e(number_format($tmsCount)); ?></span>
            <span class="text-[10px] text-rose-600 block">Cuti / DO / Wisuda</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold uppercase tracking-wider text-teal-600 block">Partisipasi Suara</span>
            <span class="text-2xl font-black text-teal-700 mt-1 block"><?php echo e(number_format($votedCount)); ?></span>
            <span class="text-[10px] text-teal-600 block">Suara Masuk Bilik</span>
        </div>
    </div>

    <!-- Filter & Pencarian DPT -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm">
        <form action="<?php echo e(route('admin.voters.index')); ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            
            <!-- Input Cari NIM / Nama / Prodi -->
            <div class="sm:col-span-2">
                <input type="text" 
                       name="search" 
                       value="<?php echo e($search); ?>" 
                       placeholder="Cari berdasarkan NIM, Nama, atau Program Studi..." 
                       class="w-full py-2.5 px-3.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <!-- Filter Fakultas -->
            <div>
                <select name="faculty" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Jurusan / Fakultas</option>
                    <?php $__currentLoopData = $faculties; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($f); ?>" <?php echo e($facultyFilter === $f ? 'selected' : ''); ?>><?php echo e($f); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- Filter Status Akademik -->
            <div>
                <select name="academic_status" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Status Akademik</option>
                    <option value="aktif" <?php echo e($academicFilter === 'aktif' ? 'selected' : ''); ?>>🟢 Kuliah Aktif</option>
                    <option value="magang" <?php echo e($academicFilter === 'magang' ? 'selected' : ''); ?>>🔵 Sedang Magang / MBKM</option>
                </select>
            </div>

            <!-- Filter Status Akun & Tombol Cari -->
            <div class="flex items-center space-x-2">
                <select name="reg_status" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Status Akun</option>
                    <option value="aktif" <?php echo e(($regFilter ?? '') === 'aktif' ? 'selected' : ''); ?>>🟢 Sudah Aktif Mandiri</option>
                    <option value="pending" <?php echo e(($regFilter ?? '') === 'pending' ? 'selected' : ''); ?>>🟡 Menunggu Verifikasi</option>
                    <option value="unregistered" <?php echo e(($regFilter ?? '') === 'unregistered' ? 'selected' : ''); ?>>⚪ Belum Daftar</option>
                </select>

                <button type="submit" class="py-2.5 px-4 bg-navy-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex-shrink-0">
                    Cari
                </button>

                <?php if($search || $facultyFilter || $academicFilter || ($regFilter ?? '')): ?>
                    <a href="<?php echo e(route('admin.voters.index')); ?>" class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition flex-shrink-0" title="Reset Filter">
                        ✕
                    </a>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <!-- Tabel Daftar Pemilih Tetap & Akun Mahasiswa -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-6">NIM Mahasiswa</th>
                        <th class="py-3.5 px-6">Nama Lengkap</th>
                        <th class="py-3.5 px-6">Program Studi / Fakultas</th>
                        <th class="py-3.5 px-6 text-center">Status Akademik</th>
                        <th class="py-3.5 px-6 text-center">Status Akun &amp; Sandi</th>
                        <th class="py-3.5 px-6 text-center">Partisipasi Suara</th>
                        <th class="py-3.5 px-6 text-center">Bantuan &amp; Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__empty_1 = true; $__currentLoopData = $voters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $academic = strtolower($v->academic_status ?? 'aktif');
                            $statusPendaftaran = $v->pendaftaran_status;
                        ?>
                        <tr class="hover:bg-slate-50/70 transition">
                            <!-- NIM -->
                            <td class="py-4 px-6 font-mono font-bold text-slate-900 whitespace-nowrap">
                                <?php echo e($v->nim); ?>

                                <?php if($v->angkatan): ?>
                                    <span class="text-[10px] text-slate-400 block font-sans">Angkatan <?php echo e($v->angkatan); ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- Nama -->
                            <td class="py-4 px-6 font-semibold text-slate-800 whitespace-nowrap">
                                <?php echo e($v->name); ?>

                                <?php if($v->email): ?>
                                    <span class="text-[11px] text-slate-400 block font-normal"><?php echo e($v->email); ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- Prodi & Fakultas -->
                            <td class="py-4 px-6 text-slate-600">
                                <div class="font-medium text-slate-800"><?php echo e($v->study_program); ?></div>
                                <div class="text-[11px] text-slate-400"><?php echo e($v->faculty); ?></div>
                            </td>

                            <!-- Status Akademik -->
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <?php if($academic === 'magang'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200" title="Mahasiswa sedang magang industri / MBKM di luar kampus. Berhak memilih via e-voting.">
                                        🔵 Sedang Magang
                                    </span>
                                <?php elseif($academic === 'aktif'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200" title="Mahasiswa aktif kuliah reguler di kampus.">
                                        🟢 Kuliah Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        🔴 <?php echo e(strtoupper($academic)); ?> (TMS)
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Status Akun & Kata Sandi -->
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <?php if($statusPendaftaran === 'aktif'): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200" title="Mahasiswa sudah mendaftar mandiri dan memiliki Kata Sandi aktif.">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                        Aktif (Password Mandiri)
                                    </span>
                                    <div class="text-[9px] text-slate-400 mt-0.5">
                                        <?php echo e($v->registered_at ? $v->registered_at->timezone('Asia/Jakarta')->format('d/m/y H:i') . ' WIB' : 'Akun Aktif'); ?>

                                    </div>
                                <?php elseif($statusPendaftaran === 'pending'): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                        🟡 Menunggu Verifikasi
                                    </span>
                                <?php elseif($statusPendaftaran === 'dicabut'): ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        🔴 Akun Dicabut
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200" title="Mahasiswa belum melakukan registrasi mandiri di web.">
                                        ⚪ Belum Mendaftar
                                    </span>
                                    <div class="text-[9px] text-slate-400 mt-0.5">Bisa daftar mandiri di web</div>
                                <?php endif; ?>
                            </td>

                            <!-- Partisipasi -->
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <?php if($v->participations_count >= 3): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        3/3 Selesai (Lengkap)
                                    </span>
                                <?php elseif($v->participations_count > 0): ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        <?php echo e($v->participations_count); ?>/3 Selesai
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">
                                        Belum Memilih
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Bantuan & Aksi Admin -->
                            <td class="py-4 px-6 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <!-- Tombol Reset Password Darurat -->
                                    <button type="button" 
                                            onclick="openResetPasswordModal('<?php echo e($v->id); ?>', '<?php echo e($v->nim); ?>', '<?php echo e(addslashes($v->name)); ?>')"
                                            class="px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 text-[11px] font-bold transition flex items-center space-x-1 border border-amber-300 shadow-sm"
                                            title="Reset Kata Sandi jika mahasiswa lupa password saat hari pemilihan">
                                        <span>🔑 Reset Sandi</span>
                                    </button>

                                    <!-- Tombol Toggle Hak Suara -->
                                    <form action="<?php echo e(route('admin.voters.toggle', $v->id)); ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status hak suara untuk <?php echo e($v->name); ?> (NIM: <?php echo e($v->nim); ?>)?');" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" 
                                                class="p-1.5 rounded-xl <?php echo e($v->is_active ? 'bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-700' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700'); ?> border border-slate-200 transition" 
                                                title="<?php echo e($v->is_active ? 'Nonaktifkan Hak Suara' : 'Aktifkan Hak Suara'); ?>">
                                            <?php if($v->is_active): ?>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            <?php else: ?>
                                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                Tidak ada data pemilih DPT yang cocok dengan kriteria pencarian.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($voters->hasPages()): ?>
            <div class="p-5 border-t border-slate-100">
                <?php echo e($voters->links()); ?>

            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal Reset Sandi Darurat Admin -->
<div id="resetPasswordModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-200 space-y-5 animate-in fade-in zoom-in duration-200">
        <div class="flex items-start justify-between">
            <div class="space-y-1">
                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 font-bold text-[10px]">Bantuan TPS Darurat</span>
                <h3 class="text-lg font-black text-slate-900">Reset Kata Sandi Mahasiswa</h3>
                <p class="text-xs text-slate-500">Membantu mahasiswa yang lupa password akun pendaftarannya.</p>
            </div>
            <button onclick="closeResetPasswordModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                ✕
            </button>
        </div>

        <form id="resetPasswordForm" action="" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                <div class="font-bold text-slate-800" id="resetModalStudentName">-</div>
                <div class="text-slate-500 font-mono" id="resetModalStudentNim">-</div>
            </div>

            <div>
                <label for="new_password_input" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Kata Sandi Sementara Baru:
                </label>
                <input type="text" 
                       name="new_password" 
                       id="new_password_input" 
                       required 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-mono text-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
                <p class="text-[11px] text-slate-400 mt-1">Berikan kata sandi sementara ini kepada mahasiswa yang bersangkutan.</p>
            </div>

            <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200 text-[11px] text-amber-800 space-y-1">
                <span class="font-bold block">🔒 Catatan Keamanan:</span>
                <p>Tindakan reset sandi ini dicatat secara permanen di Log Audit KPU untuk mencegah penyalahgunaan akun pemilih.</p>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-2">
                <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-extrabold transition shadow-lg shadow-amber-600/30">
                    🔑 Simpan Sandi Baru
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openResetPasswordModal(id, nim, name) {
        document.getElementById('resetPasswordForm').action = "/admin/voters/" + id + "/reset-password";
        document.getElementById('resetModalStudentName').innerText = name;
        document.getElementById('resetModalStudentNim').innerText = "NIM: " + nim;
        
        // Generate default secure temp password: Polbeng@ + 4 digit terakhir NIM
        const lastDigits = nim.length >= 4 ? nim.slice(-4) : '1234';
        document.getElementById('new_password_input').value = "Polbeng@" + lastDigits;

        document.getElementById('resetPasswordModal').classList.remove('hidden');
    }

    function closeResetPasswordModal() {
        document.getElementById('resetPasswordModal').classList.add('hidden');
    }
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/admin/voters/index.blade.php ENDPATH**/ ?>