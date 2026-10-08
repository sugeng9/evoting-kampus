<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil - PEMIRA POLBENG</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8 text-center">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
            <svg class="h-10 w-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Pendaftaran Berhasil!</h1>
        <p class="text-lg font-bold text-emerald-600 mb-4">Akun Anda Telah Aktif &amp; Terverifikasi 🎉</p>
        
        <div class="bg-emerald-50 p-4 rounded-xl text-sm text-emerald-900 text-left mb-6 border border-emerald-200">
            <p class="font-semibold mb-1">Otentikasi Berhasil</p>
            <p class="text-xs text-emerald-800 leading-relaxed mb-2">
                NIM Anda telah berhasil divalidasi dan kepemilikannya terkonfirmasi melalui OTP email. Akun Anda langsung aktif tanpa perlu menunggu persetujuan manual panitia.
            </p>
            <p class="text-xs text-emerald-700">
                Silakan login sekarang menggunakan NIM dan Password pribadi yang baru saja Anda buat.
            </p>
        </div>
        
        <div class="space-y-3">
            <a href="<?php echo e(route('voter.login')); ?>" class="w-full flex justify-center py-3 px-4 rounded-xl shadow-md text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition">
                🔑 Masuk ke Akun Pemilih Sekarang &rarr;
            </a>
            <a href="<?php echo e(route('nim.status.form')); ?>" class="w-full flex justify-center py-2.5 px-4 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                🔍 Cek Status Kepemilikan NIM
            </a>
            <a href="<?php echo e(url('/')); ?>" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold block pt-1">
                Kembali ke Beranda
            </a>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\Users\syaha\.gemini\antigravity\scratch\evoting-kampus\resources\views/auth/pendaftaran/sukses.blade.php ENDPATH**/ ?>