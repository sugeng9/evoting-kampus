<?php $__env->startSection('title', 'Edit Paslon - KPU Kampus'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Form -->
    <div class="flex items-center justify-between bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">Edit Pasangan Calon No. 0<?php echo e($candidate->candidate_number); ?></h1>
            <p class="text-xs text-slate-500 mt-1"><?php echo e($candidate->leader_name); ?> &bull; <?php echo e($candidate->category->name); ?></p>
        </div>
        <a href="<?php echo e(route('admin.candidates.index')); ?>" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
            &larr; Kembali
        </a>
    </div>

    <!-- Form Body -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm">
        <form action="<?php echo e(route('admin.candidates.update', $candidate->id)); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <!-- Kategori & Nomor Urut -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label for="election_category_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Kategori Pemilihan *
                    </label>
                    <select name="election_category_id" id="election_category_id" required class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>" <?php echo e((old('election_category_id', $candidate->election_category_id) == $cat->id) ? 'selected' : ''); ?>>
                                [<?php echo e($cat->code); ?>] <?php echo e($cat->name); ?> 
                                <?php if($cat->study_program_filter): ?>
                                    (HMJ: <?php echo e($cat->study_program_filter); ?>)
                                <?php elseif($cat->faculty_filter): ?>
                                    (Fakultas: <?php echo e($cat->faculty_filter); ?>)
                                <?php else: ?>
                                    (Seluruh Kampus)
                                <?php endif; ?>
                                <?php if(!$cat->is_active): ?>
                                    — [STATUS: NON-AKTIF]
                                <?php endif; ?>
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label for="candidate_number" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Nomor Urut *
                    </label>
                    <input type="number" name="candidate_number" id="candidate_number" min="1" value="<?php echo e(old('candidate_number', $candidate->candidate_number)); ?>" required class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <?php $__errorArgs = ['candidate_number'];
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
            </div>

            <!-- Calon Ketua (Leader) -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800 block">
                    Data Calon Ketua
                </span>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="leader_name" class="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap Calon Ketua *</label>
                        <input type="text" name="leader_name" id="leader_name" value="<?php echo e(old('leader_name', $candidate->leader_name)); ?>" required class="w-full py-2.5 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                        <?php $__errorArgs = ['leader_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-xs text-rose-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="leader_nim" class="block text-xs font-bold text-slate-600 mb-1">NIM Calon Ketua</label>
                        <input type="text" name="leader_nim" id="leader_nim" value="<?php echo e(old('leader_nim', $candidate->leader_nim)); ?>" class="w-full py-2.5 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- Calon Wakil (Vice Leader) -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
                <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800 block">
                    Data Calon Wakil (Opsional)
                </span>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="vice_leader_name" class="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap Calon Wakil</label>
                        <input type="text" name="vice_leader_name" id="vice_leader_name" value="<?php echo e(old('vice_leader_name', $candidate->vice_leader_name)); ?>" class="w-full py-2.5 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <label for="vice_leader_nim" class="block text-xs font-bold text-slate-600 mb-1">NIM Calon Wakil</label>
                        <input type="text" name="vice_leader_nim" id="vice_leader_nim" value="<?php echo e(old('vice_leader_nim', $candidate->vice_leader_nim)); ?>" class="w-full py-2.5 px-3 bg-white border border-slate-300 rounded-xl text-xs text-slate-900 font-mono focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- Foto Paslon -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Foto Paslon
                </label>
                <?php if($candidate->photo_path): ?>
                    <div class="flex items-center space-x-4 mb-3">
                        <div class="w-16 h-16 rounded-xl border border-slate-200 overflow-hidden bg-slate-50">
                            <img src="<?php echo e(asset($candidate->photo_path)); ?>" alt="<?php echo e($candidate->leader_name); ?>" class="w-full h-full object-cover">
                        </div>
                        <span class="text-xs text-slate-400">Pilih file baru jika ingin mengganti foto saat ini.</span>
                    </div>
                <?php endif; ?>
                <input type="file" name="photo" id="photo" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                <?php $__errorArgs = ['photo'];
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

            <!-- Visi -->
            <div>
                <label for="vision" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Visi Paslon *
                </label>
                <textarea name="vision" id="vision" rows="3" required class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none"><?php echo e(old('vision', $candidate->vision)); ?></textarea>
            </div>

            <!-- Misi -->
            <div>
                <label for="mission" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Misi &amp; Program Kerja Utama *
                </label>
                <textarea name="mission" id="mission" rows="5" required class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none"><?php echo e(old('mission', $candidate->mission)); ?></textarea>
            </div>

            <!-- Status Aktif -->
            <div>
                <label for="is_active" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Status Partisipasi Paslon *
                </label>
                <select name="is_active" id="is_active" class="py-2.5 px-4 bg-slate-50 border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500">
                    <option value="1" <?php echo e($candidate->is_active ? 'selected' : ''); ?>>AKTIF (Muncul di Surat Suara)</option>
                    <option value="0" <?php echo e(!$candidate->is_active ? 'selected' : ''); ?>>NON-AKTIF (Didiskualifikasi/Disembunyikan)</option>
                </select>
            </div>

            <!-- Tombol Submit -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <a href="<?php echo e(route('admin.candidates.index')); ?>" class="py-3 px-5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="py-3 px-6 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 transition">
                    Perbarui Data Paslon
                </button>
            </div>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/admin/candidates/edit.blade.php ENDPATH**/ ?>