<?php if (isset($component)) { $__componentOriginal4619374cef299e94fd7263111d0abc69 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4619374cef299e94fd7263111d0abc69 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <?php echo e(__('Detail Sengketa NIM: ')); ?> <span class="font-mono"><?php echo e($sengketa->nim ?? ''); ?></span>
            </h2>
            <a href="<?php echo e(route('helpdesk.index')); ?>" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Kembali ke daftar</a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <?php if(session('kode_plain')): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-r-md shadow-sm">
                <h3 class="text-lg font-medium text-yellow-800 mb-2">Kode Daftar Ulang Berhasil Dibuat!</h3>
                <p class="text-sm text-yellow-700 mb-4">Berikan kode ini kepada mahasiswa yang bersangkutan untuk mendaftar kembali.</p>
                <div class="bg-white border border-yellow-300 rounded p-4 text-center">
                    <span class="text-3xl font-mono font-bold tracking-widest text-gray-900"><?php echo e(session('kode_plain')); ?></span>
                </div>
                <p class="text-xs text-red-600 mt-2 font-bold">PENTING: Kode ini HANYA DITAMPILKAN SEKALI INI SAJA. Segera salin/catat!</p>
            </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data Laporan -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Laporan</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?php if(($sengketa->status ?? '') == 'baru'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Baru</span>
                                    <?php elseif(($sengketa->status ?? '') == 'diproses'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Diproses</span>
                                    <?php elseif(($sengketa->status ?? '') == 'selesai'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                    <?php elseif(($sengketa->status ?? '') == 'ditolak'): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                    <?php endif; ?>
                                </dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Dilaporkan Pada</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e(isset($sengketa->created_at) ? $sengketa->created_at->format('d M Y H:i') : ''); ?></dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Kontak Pelapor</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($sengketa->pelapor_kontak ?? ''); ?></dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Alasan</dt>
                                <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded border border-gray-100"><?php echo e($sengketa->alasan ?? ''); ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Info Akun Saat Ini -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Akun Pemilih Saat Ini</h3>
                        <?php if(isset($voter)): ?>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Status Akun</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <?php if($voter->is_active): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending / Nonaktif</span>
                                        <?php endif; ?>
                                    </dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Mendaftar Pada</dt>
                                    <dd class="mt-1 text-sm text-gray-900"><?php echo e($voter->created_at->format('d M Y H:i')); ?></dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Email Terdaftar (Masked)</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono"><?php echo e($maskedEmail ?? ''); ?></dd>
                                </div>
                            </dl>
                        <?php else: ?>
                            <div class="bg-gray-50 p-4 rounded text-center text-sm text-gray-500">
                                Akun pemilih tidak ditemukan atau sudah dihapus sepenuhnya.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Panel Aksi -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Aksi Penanganan</h3>

                    <?php if(isset($faseFrozen) && $faseFrozen): ?>
                        <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <p class="text-sm text-yellow-700">Fase saat ini tidak mengizinkan perubahan data akun. Anda hanya dapat melihat data sengketa.</p>
                        </div>
                    <?php else: ?>
                        
                        <div class="space-y-6">
                            <?php if(($sengketa->status ?? '') == 'baru'): ?>
                                <form action="<?php echo e(route('helpdesk.sengketa.proses', $sengketa->id ?? 0)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Tandai Sedang Diproses</button>
                                </form>
                            <?php endif; ?>

                            <?php if(in_array(($sengketa->status ?? ''), ['baru', 'diproses'])): ?>
                                <div class="border-t pt-4">
                                    <h4 class="font-medium text-sm text-gray-700 mb-2">Tindakan Cabut Akun (2 Petugas)</h4>
                                    
                                    <?php if(isset($voter) && ($voter->pendaftaran_status ?? '') !== 'dicabut'): ?>
                                        <form action="<?php echo e(route('helpdesk.sengketa.cabut', $sengketa->id ?? 0)); ?>" method="POST" class="space-y-4 max-w-md">
                                            <?php echo csrf_field(); ?>
                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input id="ktm_diverifikasi" name="ktm_diverifikasi" value="1" type="checkbox" required class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="ktm_diverifikasi" class="font-medium text-gray-700">KTM Asli Sudah Diverifikasi</label>
                                                    <p class="text-gray-500">Saya menyatakan bahwa pelapor telah menunjukkan bukti KTM asli secara fisik atau video call resmi.</p>
                                                </div>
                                            </div>
                                            
                                            <div class="space-y-2">
                                                <?php if(!$sengketa->disetujui_oleh): ?>
                                                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                                                        <strong>Tahap 1:</strong> Ditangani oleh: <em><?php echo e($sengketa->petugasA->name ?? 'Belum ada'); ?></em>.<br>
                                                        Diperlukan persetujuan dari petugas kedua yang berbeda.
                                                    </div>
                                                    <?php if($sengketa->ditangani_oleh && $sengketa->ditangani_oleh !== auth()->guard('web')->id()): ?>
                                                        <button type="submit" formaction="<?php echo e(route('helpdesk.sengketa.setujui', $sengketa->id)); ?>" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700">
                                                            ✓ Setujui Pencabutan (Sebagai Petugas 2)
                                                        </button>
                                                    <?php elseif(!$sengketa->ditangani_oleh): ?>
                                                        <p class="text-xs text-slate-500">Klik "Tandai Sedang Diproses" terlebih dahulu sebelum mengajukan pencabutan.</p>
                                                    <?php else: ?>
                                                        <p class="text-xs text-amber-700 font-semibold">Anda adalah Petugas 1 (yang menangani). Silakan minta petugas helpdesk lain untuk menyetujui laporan ini.</p>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-800 mb-2">
                                                        ✓ Telah disetujui oleh Petugas 2: <em><?php echo e($sengketa->petugasB->name ?? 'Petugas B'); ?></em>. Siap dieksekusi.
                                                    </div>
                                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-rose-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-700">
                                                        Cabut Akun Palsu Sekarang
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <p class="text-sm text-gray-500 italic">Akun pemilih sudah dalam status dicabut.</p>
                                    <?php endif; ?>
                                </div>

                                <div class="border-t pt-4">
                                    <h4 class="font-medium text-sm text-gray-700 mb-2">Tolak Laporan</h4>
                                    <form action="<?php echo e(route('helpdesk.sengketa.tolak', $sengketa->id ?? 0)); ?>" method="POST" class="max-w-md space-y-3">
                                        <?php echo csrf_field(); ?>
                                        <textarea name="alasan_tolak" rows="2" required placeholder="Alasan penolakan..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Tolak Sengketa</button>
                                    </form>
                                </div>
                            <?php endif; ?>

                            <?php if(($sengketa->status ?? '') == 'selesai'): ?>
                                <div class="border-t pt-4">
                                    <h4 class="font-medium text-sm text-gray-700 mb-2">Terbitkan Kode Daftar Ulang</h4>
                                    <p class="text-xs text-gray-500 mb-3">Jika akun lama telah berhasil dicabut, Anda dapat menerbitkan kode khusus agar mahasiswa ini bisa mendaftar ulang.</p>
                                    <form action="<?php echo e(route('helpdesk.sengketa.kode', $sengketa->id ?? 0)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Terbitkan Kode Daftar Ulang</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $attributes = $__attributesOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__attributesOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $component = $__componentOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__componentOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/helpdesk/show.blade.php ENDPATH**/ ?>