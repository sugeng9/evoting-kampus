<?php $__env->startSection('title', 'Quick Count Ditutup'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-md mx-auto my-12 text-center">
    <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-xl">
        <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <h2 class="text-xl font-black text-slate-900">Quick Count Masih Dirahasiakan</h2>
        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
            Demi menjaga independensi dan integritas suara, hasil rekapitulasi hanya akan dipublikasikan oleh Panitia KPU Kampus setelah masa pemungutan suara resmi berakhir.
        </p>

        <div class="my-6 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600">
            <span class="font-bold text-slate-800 block">Jadwal Penutupan Pemira:</span>
            <span class="font-mono text-emerald-700 font-bold block mt-1">
                <?php echo e($setting->voting_ends_at ? $setting->voting_ends_at->translatedFormat('d F Y, H:i T') : 'Menunggu Pengumuman Panitia'); ?>

            </span>
        </div>

        <a href="<?php echo e(route('voter.login')); ?>" class="w-full py-3 px-4 bg-navy-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs block transition">
            Kembali ke Halaman Login
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/public/quick_count_locked.blade.php ENDPATH**/ ?>