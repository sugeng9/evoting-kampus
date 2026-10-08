<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status NIM Pemilih - PEMIRA POLBENG</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Cek Status NIM Pemilih</h1>
            <p class="text-gray-600 text-sm mt-2">Masukkan data untuk mengecek status pendaftaran Anda</p>
        </div>

        <?php if($errors->any()): ?>
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-md border border-red-200">
                <ul class="list-disc pl-5 text-sm">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('nim.status.check')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            
            <div>
                <label for="nim" class="block text-sm font-medium text-gray-700">NIM</label>
                <input type="text" name="nim" id="nim" value="<?php echo e(old('nim')); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono border p-2">
            </div>

            <div>
                <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="<?php echo e(old('tanggal_lahir')); ?>" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>

            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cek Status
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="<?php echo e(route('daftar.form')); ?>" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium">Kembali ke Pendaftaran</a>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/auth/nim_status/form.blade.php ENDPATH**/ ?>