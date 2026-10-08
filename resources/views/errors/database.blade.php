<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database MySQL Belum Dinyalakan - E-Voting Kampus</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 flex items-center justify-center min-h-screen p-4 font-sans">
    <div class="max-w-xl w-full bg-slate-800 border border-slate-700 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        
        <!-- Header Alert -->
        <div class="flex items-center space-x-4">
            <div class="w-14 h-14 rounded-2xl bg-amber-500/20 border border-amber-500/30 text-amber-400 flex items-center justify-center flex-shrink-0">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Peringatan Koneksi Server</span>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Database MySQL Belum Aktif</h1>
            </div>
        </div>

        <!-- Penjelasan Masalah -->
        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-700/80 text-xs sm:text-sm text-slate-300 space-y-2 leading-relaxed">
            <p>
                Aplikasi Laravel tidak dapat menghubungi server database di <strong class="text-white font-mono">127.0.0.1:3306</strong>.
            </p>
            <p class="text-slate-400 text-xs">
                Hal ini biasanya terjadi karena aplikasi <strong>XAMPP Control Panel</strong> atau <strong>Laragon</strong> belum dinyalakan, atau tombol <strong>Start MySQL</strong> belum diklik.
            </p>
        </div>

        <!-- Panduan Solusi Langkah demi Langkah -->
        <div class="space-y-3">
            <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Cara Mengaktifkan:</h2>
            <ol class="space-y-2.5 text-xs text-slate-300">
                <li class="flex items-start space-x-3 p-3 rounded-xl bg-slate-700/40 border border-slate-700">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center flex-shrink-0 text-xs">1</span>
                    <span>Buka aplikasi <strong>XAMPP Control Panel</strong> (atau <strong>Laragon</strong>) di laptop Anda.</span>
                </li>
                <li class="flex items-start space-x-3 p-3 rounded-xl bg-slate-700/40 border border-slate-700">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center flex-shrink-0 text-xs">2</span>
                    <span>Cari baris <strong>MySQL</strong>, lalu klik tombol <strong class="text-emerald-400">"Start"</strong> sampai indikatornya berwarna hijau.</span>
                </li>
                <li class="flex items-start space-x-3 p-3 rounded-xl bg-slate-700/40 border border-slate-700">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center flex-shrink-0 text-xs">3</span>
                    <span>Setelah MySQL aktif (hijau), klik tombol <strong>"Muat Ulang Halaman"</strong> di bawah atau tekan tombol <strong>F5</strong> di keyboard.</span>
                </li>
            </ol>
        </div>

        <!-- Tombol Aksi -->
        <div class="pt-2 flex flex-col sm:flex-row items-center gap-3">
            <button onclick="window.location.reload()" class="w-full sm:w-auto flex-1 py-3 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition shadow-lg shadow-emerald-600/30 flex items-center justify-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Muat Ulang Halaman (Refresh)</span>
            </button>
            <a href="http://127.0.0.1:8000/voter/login" class="w-full sm:w-auto py-3 px-5 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-200 font-bold text-xs transition text-center">
                Bilik Suara Langsung &rarr;
            </a>
        </div>

    </div>
</body>
</html>
