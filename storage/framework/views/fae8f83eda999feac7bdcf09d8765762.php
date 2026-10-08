<?php $__env->startSection('title', 'Edit Pemilih DPT - KPU Kampus'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header Form -->
    <div class="flex items-center justify-between bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Edit Data Pemilih (DPT)</h1>
            <p class="text-xs text-slate-500 mt-1">NIM: <strong class="font-mono text-slate-700"><?php echo e($voter->nim); ?></strong> &bull; <?php echo e($voter->name); ?></p>
        </div>
        <a href="<?php echo e(route('admin.voters.index')); ?>" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
            &larr; Kembali
        </a>
    </div>

    <!-- Form Body -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="<?php echo e(route('admin.voters.update', $voter->id)); ?>" method="POST" class="space-y-5">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <!-- Input NIM -->
            <div>
                <label for="nim" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Nomor Induk Mahasiswa (NIM) *
                </label>
                <input type="text" 
                       name="nim" 
                       id="nim" 
                       value="<?php echo e(old('nim', $voter->nim)); ?>" 
                       required 
                       class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 font-mono text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <?php $__errorArgs = ['nim'];
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

            <!-- Input Nama Lengkap -->
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Nama Lengkap Mahasiswa *
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="<?php echo e(old('name', $voter->name)); ?>" 
                       required 
                       class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <?php $__errorArgs = ['name'];
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

            <!-- Fakultas & Program Studi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="faculty" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Fakultas *
                    </label>
                    <input type="text" 
                           name="faculty" 
                           id="faculty" 
                           value="<?php echo e(old('faculty', $voter->faculty)); ?>" 
                           required 
                           class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="study_program" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Program Studi *
                    </label>
                    <input type="text" 
                           name="study_program" 
                           id="study_program" 
                           value="<?php echo e(old('study_program', $voter->study_program)); ?>" 
                           required 
                           class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Status Hak Suara -->
            <div>
                <label for="is_active" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Status Hak Suara Pemilih *
                </label>
                <select name="is_active" id="is_active" class="py-2.5 px-4 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500">
                    <option value="1" <?php echo e($voter->is_active ? 'selected' : ''); ?>>AKTIF (Dapat Login &amp; Menggunakan Hak Suara)</option>
                    <option value="0" <?php echo e(!$voter->is_active ? 'selected' : ''); ?>>NON-AKTIF (Hak Suara Dicabut / Diskorsing)</option>
                </select>
            </div>

            <!-- Reset PIN Baru (Opsional) -->
            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 space-y-2">
                <div class="flex items-center justify-between">
                    <label for="pin" class="block text-xs font-bold uppercase tracking-wider text-amber-900">
                        Reset PIN Baru (Opsional)
                    </label>
                    <button type="button" 
                            onclick="generateRandomPin()" 
                            class="text-xs font-bold text-emerald-700 hover:text-emerald-800 underline">
                        + Buat PIN Acak
                    </button>
                </div>
                <input type="text" 
                       name="pin" 
                       id="pin" 
                       placeholder="Kosongkan jika TIDAK ingin mengubah PIN mahasiswa" 
                       class="w-full py-2.5 px-3 bg-white border border-amber-300 rounded-xl text-slate-900 font-mono tracking-wider text-xs focus:ring-2 focus:ring-emerald-500 outline-none">
                <span class="text-[11px] text-amber-800/80 block">Isi hanya jika mahasiswa lupa PIN atau mengajukan permohonan reset token ke Helpdesk KPU.</span>
            </div>

            <!-- Tombol Submit -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="<?php echo e(route('admin.voters.index')); ?>" class="py-3 px-5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="py-3 px-6 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 transition">
                    Perbarui Data Pemilih
                </button>
            </div>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function generateRandomPin() {
        const pin = Math.floor(100000 + Math.random() * 900000);
        document.getElementById('pin').value = pin;
    }
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/admin/voters/edit.blade.php ENDPATH**/ ?>