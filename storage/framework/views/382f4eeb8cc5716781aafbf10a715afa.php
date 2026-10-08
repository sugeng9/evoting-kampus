<?php $__env->startSection('title', 'Aktivasi Akun Pemilih & Pembuatan PIN'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-md mx-auto my-6 sm:my-10">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-br from-emerald-800 via-teal-900 to-navy-900 p-8 text-white text-center relative overflow-hidden">
            <div class="inline-flex p-3 bg-white/10 rounded-2xl mb-3 backdrop-blur-sm border border-white/10">
                <svg class="w-8 h-8 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-black tracking-tight">Aktivasi Akun Pemilih</h2>
            <p class="text-xs text-emerald-200 mt-1">Verifikasi Identitas DPT &amp; Buat PIN Rahasia Anda Sendiri</p>
        </div>

        <!-- Form Body -->
        <div class="p-8">
            <form action="<?php echo e(route('voter.activate.submit')); ?>" method="POST" id="activationForm" class="space-y-4">
                <?php echo csrf_field(); ?>

                <!-- Input NIM -->
                <div>
                    <label for="nim" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nomor Induk Mahasiswa (NIM) *
                    </label>
                    <input type="text" 
                           name="nim" 
                           id="nim" 
                           value="<?php echo e(old('nim')); ?>" 
                           required 
                           autocomplete="off"
                           placeholder="Contoh: 120140001" 
                           class="w-full py-3 px-4 bg-slate-50 border <?php $__errorArgs = ['nim'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-500 <?php else: ?> border-slate-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-xl text-slate-900 font-mono text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <?php $__errorArgs = ['nim'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-rose-600 font-semibold"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Input Nama Lengkap untuk Validasi -->
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Nama Lengkap Mahasiswa *
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="<?php echo e(old('name')); ?>" 
                           required 
                           placeholder="Sesuai nama terdaftar di kampus" 
                           class="w-full py-3 px-4 bg-slate-50 border <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-500 <?php else: ?> border-slate-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-xl text-slate-900 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <span class="text-[11px] text-slate-400 mt-1 block">Digunakan sistem untuk mencocokkan identitas NIM di DPT.</span>
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-rose-600 font-semibold"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Buat PIN Baru -->
                <div>
                    <label for="pin" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Buat PIN Rahasia Baru (Min. 6 Digit) *
                    </label>
                    <input type="password" 
                           name="pin" 
                           id="pin" 
                           required 
                           placeholder="Buat 6 digit angka (misal: tanggal lahir / angka rahasia)" 
                           class="w-full py-3 px-4 bg-slate-50 border <?php $__errorArgs = ['pin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-rose-500 <?php else: ?> border-slate-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> rounded-xl text-slate-900 text-xs font-mono font-bold tracking-widest focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <?php $__errorArgs = ['pin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-xs text-rose-600 font-semibold"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Konfirmasi PIN -->
                <div>
                    <label for="pin_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Ulangi PIN Rahasia Baru *
                    </label>
                    <input type="password" 
                           name="pin_confirmation" 
                           id="pin_confirmation" 
                           required 
                           placeholder="Ketik ulang PIN di atas" 
                           class="w-full py-3 px-4 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-mono font-bold tracking-widest focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <!-- Security Note -->
                <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-[11px] text-emerald-800 leading-relaxed">
                    🔒 PIN ini akan disimpan dalam format terenkripsi (Hash) dan hanya diketahui oleh Anda untuk mencoblos di bilik suara.
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        id="submitActivateBtn" 
                        class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition text-xs uppercase tracking-wider flex items-center justify-center space-x-2">
                    <span id="btnTextActivate">Aktivasi &amp; Masuk ke Bilik Suara</span>
                    <svg id="spinnerActivate" class="hidden animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                <a href="<?php echo e(route('voter.login')); ?>" class="text-xs text-slate-500 hover:text-emerald-700 font-bold">
                    Sudah memiliki PIN? Masuk di sini &rarr;
                </a>
            </div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    document.getElementById('activationForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitActivateBtn');
        const text = document.getElementById('btnTextActivate');
        const spinner = document.getElementById('spinnerActivate');

        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        text.innerText = 'Mengaktivasi Akun...';
        spinner.classList.remove('hidden');
    });
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/voter/activate.blade.php ENDPATH**/ ?>