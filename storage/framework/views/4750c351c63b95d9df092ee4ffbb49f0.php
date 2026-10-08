<?php $__env->startSection('content'); ?>
    <div class="space-y-6">
        <?php if(isset($header)): ?>
            <div class="bg-navy-900 text-white p-6 sm:p-8 rounded-3xl shadow-xl flex items-center justify-between">
                <?php echo e($header); ?>

            </div>
        <?php endif; ?>

        <?php echo e($slot); ?>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/components/app-layout.blade.php ENDPATH**/ ?>