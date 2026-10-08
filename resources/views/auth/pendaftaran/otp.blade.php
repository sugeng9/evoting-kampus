<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - PEMIRA POLBENG</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Verifikasi OTP</h1>
            <p class="text-gray-600 text-sm mt-2">NIM: <span class="font-bold text-slate-800 font-mono">{{ $nim ?? session('pendaftaran_nim') }}</span></p>
            <p class="text-gray-600 text-sm">Kode dikirim ke: <span class="font-medium text-slate-800">{{ $maskedEmail ?? session('pendaftaran_email') }}</span></p>
        </div>


        @if (session('success'))
            <div class="mb-4 p-3.5 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 text-xs flex items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-4 p-3.5 bg-amber-50 text-amber-800 rounded-xl border border-amber-200 text-xs flex items-center space-x-2">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3.5 bg-rose-50 text-rose-800 rounded-xl border border-rose-200 text-xs flex items-center space-x-2">
                <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3.5 bg-red-50 text-red-700 rounded-xl border border-red-200">
                <ul class="list-disc pl-5 text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('daftar.otp.verify') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="nim" value="{{ $nim ?? session('pendaftaran_nim') }}">
            
            <div>
                <label for="otp" class="sr-only">Kode OTP</label>
                <input type="text" name="otp" id="otp" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autofocus required class="mt-1 block w-full text-center text-3xl tracking-widest font-mono rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border p-3" placeholder="------">
            </div>
            
            <div class="text-center text-sm text-gray-500">
                <p>OTP berlaku 10 menit, maksimal 5 percobaan.</p>
                <p class="mt-1 font-medium text-red-600 text-lg" id="countdown">10:00</p>
            </div>

            <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                Verifikasi & Aktifkan Akun
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <form method="POST" action="{{ route('daftar.otp.resend') }}">
                @csrf
                <button type="submit" id="btn-resend" class="text-indigo-600 hover:text-indigo-800 font-semibold flex items-center space-x-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Kirim Ulang Kode OTP</span>
                </button>
            </form>
            
            <a href="{{ route('voter.login', ['tab' => 'register']) }}" class="text-slate-500 hover:text-slate-800 font-medium">
                &larr; Ganti NIM / Email
            </a>
        </div>
    </div>

    <script>
        let timeLeft = 600;
        const countdownEl = document.getElementById('countdown');
        
        const timer = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(timer);
                countdownEl.textContent = "Waktu Habis";
                return;
            }
            
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            countdownEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }, 1000);

        // Timer cooldown tombol Kirim Ulang OTP
        let resendTime = 60;
        const resendBtn = document.getElementById('btn-resend');
        if (resendBtn) {
            resendBtn.disabled = true;
            resendBtn.classList.add('opacity-50', 'cursor-not-allowed');
            resendBtn.innerHTML = `<span>Kirim Ulang (${resendTime}s)</span>`;

            const resendInterval = setInterval(() => {
                resendTime--;
                if (resendTime <= 0) {
                    clearInterval(resendInterval);
                    resendBtn.disabled = false;
                    resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    resendBtn.innerHTML = `
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Kirim Ulang Kode OTP</span>
                    `;
                } else {
                    resendBtn.innerHTML = `<span>Kirim Ulang (${resendTime}s)</span>`;
                }
            }, 1000);
        }
    </script>
</body>
</html>
