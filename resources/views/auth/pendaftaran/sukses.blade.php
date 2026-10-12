<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil - PEMIRA POLITEKNIK NEGERI BENGKALIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4 antialiased text-slate-800">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200/90 p-6 sm:p-8 text-center">
        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-2xl bg-emerald-50 text-emerald-700 mb-5 border border-emerald-200">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
        </div>
        
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Pendaftaran Akun Berhasil</h1>
        <p class="text-xs font-semibold text-emerald-700 mt-1">Akun Pemilih Anda Telah Aktif &amp; Terverifikasi</p>
        
        <div class="bg-slate-50 p-4 rounded-xl text-xs text-slate-600 text-left my-5 border border-slate-200 space-y-1.5">
            <p class="font-bold text-slate-900 text-xs uppercase tracking-wider">Otentikasi Identitas Berhasil</p>
            <p class="leading-relaxed">
                NIM Anda telah berhasil divalidasi silang terhadap data resmi BAAK dan kepemilikannya terkonfirmasi melalui OTP.
            </p>
            <p class="text-slate-500 text-[11px]">
                Silakan masuk menggunakan NIM dan Kata Sandi yang baru saja Anda buat.
            </p>
        </div>
        
        <div class="space-y-2.5">
            <a href="{{ route('voter.login') }}" class="w-full flex justify-center py-3 px-4 rounded-xl shadow-sm text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 active:bg-black focus:outline-none transition">
                Masuk ke Bilik Suara Sekarang &rarr;
            </a>
            <a href="{{ route('nim.status.form') }}" class="w-full flex justify-center py-2.5 px-4 border border-slate-300 rounded-xl text-xs font-medium text-slate-700 bg-white hover:bg-slate-50 transition">
                Cek Status Kepemilikan NIM
            </a>
        </div>
    </div>
</body>
</html>
