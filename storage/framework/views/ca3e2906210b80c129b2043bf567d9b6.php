<?php $__env->startSection('title', 'Verifikasi Kuitansi Suara Independen'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto space-y-8 my-6">

    <div class="text-center">
        <div class="inline-flex p-3 bg-emerald-100 rounded-2xl text-emerald-700 mb-3">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Audit & Verifikasi Suara Independen</h1>
        <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
            Verifikasi apakah suara Anda telah tersimpan secara sah di dalam kotak suara digital tanpa mengungkap isi pilihan Anda (Zero-Knowledge Audit).
        </p>
    </div>

    <!-- Form Input Kode Verifikasi -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-lg">
        <form action="<?php echo e(route('verify_ballot.submit')); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>

            <div>
                <label for="verification_code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Kode Verifikasi Kuitansi (Audit Code)
                </label>
                <div class="relative">
                    <input type="text" 
                           name="verification_code" 
                           id="verification_code" 
                           value="<?php echo e(old('verification_code', request('code'))); ?>" 
                           required 
                           placeholder="Contoh: PEMIRA-9F2B-K8L1" 
                           class="w-full text-center uppercase tracking-widest text-lg font-mono py-3.5 px-4 bg-slate-50 border border-slate-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white outline-none">
                </div>
                <?php $__errorArgs = ['verification_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p class="text-xs text-rose-600 font-semibold mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <button type="submit" class="w-full py-3.5 bg-navy-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-md transition flex items-center justify-center space-x-2">
                <span>Periksa Keberadaan Suara di Kotak Suara</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
        </form>

        <!-- Hasil Audit Box -->
        <?php if(session('audit_result')): ?>
            <?php $res = session('audit_result'); ?>
            <div class="mt-8 pt-6 border-t border-slate-200">
                <?php if($res['status'] === 'verified'): ?>
                    <div class="p-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-950 space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-emerald-900 uppercase tracking-wide">STATUS: TERVERIFIKASI & SAH</h3>
                                <p class="text-xs text-emerald-700">Suara Anda telah tersimpan dan diperhitungkan dalam rekapitulasi resmi.</p>
                            </div>
                        </div>

                        <div class="text-xs space-y-2 bg-white/70 p-4 rounded-xl border border-emerald-200 font-mono">
                            <div><strong class="text-slate-600">Kategori:</strong> <?php echo e($res['category_name']); ?></div>
                            <div><strong class="text-slate-600">Waktu Masuk (WIB):</strong> <?php echo e($res['recorded_at']); ?></div>
                            <div class="break-all"><strong class="text-slate-600">Proof Hash:</strong> <?php echo e($res['ballot_hash']); ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="p-6 rounded-2xl bg-rose-50 border border-rose-200 text-rose-950 flex items-start space-x-3">
                        <svg class="w-6 h-6 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-black text-rose-900">KODE TIDAK DITEMUKAN</h3>
                            <p class="text-xs text-rose-700 mt-1"><?php echo e($res['message']); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/public/verify_ballot.blade.php ENDPATH**/ ?>