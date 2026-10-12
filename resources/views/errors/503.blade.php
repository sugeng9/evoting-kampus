<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Sedang Dipelihara - PEMIRA POLITEKNIK NEGERI BENGKALIS</title>
    <!-- Tailwind CSS CDN (Standalone, tidak membutuhkan database / backend assets) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-4 selection:bg-emerald-500 selection:text-white">

    <div class="max-w-xl w-full bg-slate-800/90 border border-slate-700/80 rounded-3xl p-6 sm:p-10 shadow-2xl backdrop-blur-xl relative overflow-hidden text-center">
        <!-- Glow Orbs Background -->
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Badge Instansi -->
        <div class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold mb-6">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
            <span>PEMELIHARAAN SISTEM &bull; MODE AMAN (503)</span>
        </div>

        <!-- Hero Icon -->
        <div class="w-20 h-20 mx-auto rounded-3xl bg-slate-900/80 border border-slate-700 flex items-center justify-center mb-6 shadow-inner">
            <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <!-- Heading -->
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-3">
            Sistem Sedang Dipelihara
        </h1>
        <p class="text-slate-300 text-sm leading-relaxed mb-6">
            Komisi Pemilihan Raya (KPR) dan Panitia Pengawas (PanWas) sedang melakukan sinkronisasi data, rekonsiliasi kotak suara, atau pemeliharaan server berkala demi menjaga integritas dan netralitas pemilihan.
        </p>

        <!-- Informasi Kotak Suara & Keamanan -->
        <div class="bg-slate-900/60 rounded-2xl p-4 border border-slate-700/60 text-left mb-6 space-y-2">
            <div class="flex items-center space-x-2 text-xs font-bold text-emerald-400">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Integritas Suara Dijamin Aman 100%</span>
            </div>
            <p class="text-[12px] text-slate-400 leading-normal">
                Seluruh suara yang telah masuk ke kotak suara digital tersimpan terenkripsi dengan rantai audit kriptografis. Tidak ada data surat suara yang hilang selama proses ini.
            </p>
        </div>

        <!-- Tombol Refresh & Countdown Otomatis -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-6">
            <button onclick="window.location.reload()" class="w-full sm:w-auto px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider transition shadow-lg shadow-emerald-600/30 flex items-center justify-center space-x-2">
                <svg class="w-4 h-4 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Coba Muat Ulang Halaman</span>
            </button>
            <span class="text-xs text-slate-400 font-mono">
                Auto refresh dalam: <strong id="countdown" class="text-amber-400">30</strong>s
            </span>
        </div>

        <!-- Footer Helpdesk -->
        <div class="pt-6 border-t border-slate-700/60 text-xs text-slate-400">
            <p>Butuh bantuan mendesak? Hubungi Petugas Helpdesk Pemira:</p>
            <p class="mt-1 font-mono text-emerald-400 font-semibold">
                WhatsApp: <a href="https://wa.me/6285187953580" target="_blank" class="underline hover:text-emerald-300">+62 851-8795-3580</a>
            </p>
        </div>
    </div>

    <script>
        let seconds = 30;
        const countdownEl = document.getElementById('countdown');
        setInterval(() => {
            seconds--;
            if (countdownEl) countdownEl.innerText = seconds;
            if (seconds <= 0) {
                window.location.reload();
            }
        }, 1000);
    </script>
</body>
</html>
