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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Panel Admin - Manajemen Fase')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Fase Saat Ini -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">Status Sistem Saat Ini</h3>
                            <div class="flex items-center mt-2">
                                <span class="px-4 py-2 inline-flex text-sm leading-5 font-bold rounded-full 
                                    <?php echo e(($currentPhase->fase ?? '') == 'pendaftaran' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                    <?php echo e(($currentPhase->fase ?? '') == 'sengketa' ? 'bg-yellow-100 text-yellow-800' : ''); ?>

                                    <?php echo e(($currentPhase->fase ?? '') == 'aktif' ? 'bg-green-100 text-green-800' : ''); ?>

                                    <?php echo e(($currentPhase->fase ?? '') == 'voting' ? 'bg-purple-100 text-purple-800' : ''); ?>

                                    uppercase border-2">
                                    <?php echo e(ucfirst($currentPhase->fase ?? 'Tidak Diketahui')); ?>

                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">Catatan: <?php echo e($currentPhase->catatan ?? '-'); ?></p>
                        </div>
                        
                        <div class="mt-4 md:mt-0 max-w-md w-full bg-gray-50 p-4 rounded border">
                            <form action="<?php echo e(route('admin.fase.ubah')); ?>" method="POST" class="space-y-3">
                                <?php echo csrf_field(); ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ubah Fase</label>
                                    <select name="fase" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="pendaftaran" <?php echo e(($currentPhase->fase ?? '') == 'pendaftaran' ? 'selected' : ''); ?>>Pendaftaran</option>
                                        <option value="sengketa" <?php echo e(($currentPhase->fase ?? '') == 'sengketa' ? 'selected' : ''); ?>>Sengketa & Verifikasi</option>
                                        <option value="aktif" <?php echo e(($currentPhase->fase ?? '') == 'aktif' ? 'selected' : ''); ?>>Aktif (Siap Voting)</option>
                                        <option value="voting" <?php echo e(($currentPhase->fase ?? '') == 'voting' ? 'selected' : ''); ?>>Pelaksanaan Voting</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                                    <input type="text" name="catatan" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div class="text-xs text-red-600 font-medium mt-1">
                                    Peringatan: Mengubah ke fase 'aktif' atau 'voting' tidak dapat dibatalkan (satu arah).
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Simpan Perubahan Fase</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistik -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Total DPT Master</div>
                        <div class="mt-2 text-3xl font-bold text-gray-900"><?php echo e($stats['total_master'] ?? 0); ?></div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Akun Aktif</div>
                        <div class="mt-2 text-3xl font-bold text-green-600"><?php echo e($stats['total_aktif'] ?? 0); ?></div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Menunggu Verifikasi</div>
                        <div class="mt-2 text-3xl font-bold text-yellow-600"><?php echo e($stats['total_pending'] ?? 0); ?></div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm font-medium text-gray-500">Belum Mendaftar</div>
                        <div class="mt-2 text-3xl font-bold text-gray-600"><?php echo e($stats['belum_daftar'] ?? 0); ?></div>
                    </div>
                </div>
            </div>

            <!-- Aktivasi Massal -->
            <?php if(($currentPhase->fase ?? '') == 'aktif'): ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-green-200">
                <div class="p-6 bg-green-50">
                    <h3 class="text-lg font-medium text-green-900 mb-2">Aktivasi Massal Akun PENDING</h3>
                    <p class="text-sm text-green-700 mb-4">Karena fase saat ini sudah 'Aktif', Anda dapat mengaktifkan seluruh akun yang masih berstatus PENDING secara bersamaan agar mereka dapat melakukan voting.</p>
                    <form action="<?php echo e(route('admin.fase.aktifkan')); ?>" method="POST" onsubmit="return confirm('Ini akan mengaktifkan seluruh akun pending. Lanjutkan?');">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-green-700 shadow-sm">
                            Aktifkan Semua Pending (<?php echo e($stats['total_pending'] ?? 0); ?> akun)
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Import Master -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Import Data Master DPT (CSV)</h3>
                    <div class="flex flex-col md:flex-row gap-8">
                        <div class="flex-1">
                            <form action="<?php echo e(route('admin.fase.import')); ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                                <?php echo csrf_field(); ?>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Pilih File CSV</label>
                                    <input type="file" name="csv_file" accept=".csv" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                <button type="submit" class="inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Import Data</button>
                            </form>
                        </div>
                        <div class="flex-1 bg-gray-50 p-4 rounded text-sm">
                            <p class="font-medium text-gray-700 mb-2">Format CSV yang didukung:</p>
                            <code class="block bg-gray-800 text-green-400 p-2 rounded mb-2 overflow-x-auto">
                                nim,nama,prodi,tanggal_lahir<br>
                                120510001,"Ahmad Fauzi","Teknik Informatika","2003-05-15"
                            </code>
                            <p class="text-gray-500 text-xs">Pastikan format tanggal lahir adalah YYYY-MM-DD. Baris pertama harus berupa header.</p>
                        </div>
                    </div>
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
<?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/admin/fase/panel.blade.php ENDPATH**/ ?>