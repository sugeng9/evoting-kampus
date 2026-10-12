<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Sesi Halaman Berakhir | PEMIRA POLBENG</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-heading { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-200 overflow-hidden text-center p-8 sm:p-10 relative">
        <div class="absolute -right-10 -top-10 w-36 h-36 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-10 -bottom-10 w-36 h-36 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Icon Timer / Sesi Expired -->
        <div class="w-20 h-20 bg-amber-50 border-2 border-amber-200 text-amber-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-mono font-bold mb-3 border border-slate-200">
            <span>HTTP 419</span>
            <span>&bull;</span>
            <span>PAGE EXPIRED</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-heading font-black text-slate-900 tracking-tight">Sesi Halaman Berakhir</h1>
        
        <p class="text-slate-600 text-xs sm:text-sm mt-3 leading-relaxed">
            Demi keamanan dan integritas bilik suara, formulir ini memiliki batas waktu aktif. Hal ini terjadi karena halaman dibiarkan terbuka terlalu lama atau cookie sesi browser telah kedaluwarsa.
        </p>

        <div class="mt-8 space-y-3">
            <button onclick="window.location.reload();" 
                    class="w-full py-3.5 px-5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-2xl shadow-lg shadow-emerald-600/25 transition transform hover:-translate-y-0.5 text-sm flex items-center justify-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Muat Ulang Halaman (Refresh)</span>
            </button>

            <a href="{{ route('voter.login') }}" 
               class="w-full py-3 px-5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-2xl text-xs transition flex items-center justify-center space-x-1.5">
                <span>&larr; Kembali ke Portal Login Pemilih</span>
            </a>
        </div>

        <div class="mt-8 pt-5 border-t border-slate-100 text-[11px] text-slate-400">
            Komisi Pemilihan Raya (KPR) &bull; Politeknik Negeri Bengkalis
        </div>
    </div>
</body>
</html>
