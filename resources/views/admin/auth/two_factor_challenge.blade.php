@extends('layouts.app')

@section('title', 'Verifikasi 2FA Google Authenticator - KPR & PanWas')

@section('content')
<div class="max-w-md mx-auto my-6 sm:my-12 px-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-br from-navy-950 via-slate-900 to-navy-900 p-8 text-white text-center relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-teal-500/10 border border-teal-500/20 flex items-center justify-center text-teal-300">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>

            <div class="inline-block px-3 py-0.5 rounded-full bg-teal-500/20 text-teal-300 text-[10px] font-extrabold uppercase tracking-widest border border-teal-500/30 mb-1">
                Verifikasi Tingkat 2 &bull; TOTP
            </div>
            <h2 class="text-xl font-heading font-black tracking-tight text-white">Autentikasi Dua Faktor</h2>
            <p class="text-xs text-slate-300 mt-1 font-sans">
                Akun <strong>{{ $user->username }}</strong> ({{ $user->role_label }})
            </p>
        </div>

        <div class="p-8">
            @if(session('info'))
                <div class="mb-5 p-3.5 bg-blue-50 border-l-4 border-blue-500 rounded-r-xl text-blue-900 text-xs font-medium">
                    {{ session('info') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-5 p-3.5 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl text-amber-900 text-xs font-medium">
                    {{ session('warning') }}
                </div>
            @endif

            <form action="{{ route('admin.2fa.verify') }}" method="POST" id="twoFactorForm" class="space-y-5">
                @csrf

                <!-- Mode 1: TOTP 6-digit Code (Default) -->
                <div id="totpSection">
                    <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2 text-center">
                        Kode Google Authenticator (6-Digit)
                    </label>
                    <input type="text" 
                           name="code" 
                           id="code" 
                           maxlength="6" 
                           pattern="[0-9]{6}" 
                           inputmode="numeric" 
                           autocomplete="one-time-code" 
                           autofocus
                           placeholder="000000" 
                           class="w-full text-center tracking-[0.5em] text-3xl font-mono font-black py-3.5 bg-slate-50 border @error('code') border-rose-500 @else border-slate-300 @enderror rounded-2xl focus:ring-2 focus:ring-teal-500 focus:bg-white text-slate-900 shadow-inner">
                    @error('code')
                        <p class="mt-2 text-xs text-rose-600 font-semibold text-center">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mode 2: Emergency Recovery Code (Hidden by default) -->
                <div id="recoverySection" class="hidden">
                    <label for="recovery_code" class="block text-xs font-bold uppercase tracking-wider text-amber-900 mb-2 text-center">
                        Kode Pemulihan Darurat (10 Karakter)
                    </label>
                    <input type="text" 
                           name="recovery_code" 
                           id="recovery_code" 
                           maxlength="20" 
                           placeholder="Contoh: a1b2c3d4e5" 
                           class="w-full text-center font-mono font-bold tracking-widest text-lg py-3.5 bg-amber-50/50 border @error('recovery_code') border-rose-500 @else border-amber-300 @enderror rounded-2xl focus:ring-2 focus:ring-amber-500 focus:bg-white text-slate-900 shadow-inner">
                    @error('recovery_code')
                        <p class="mt-2 text-xs text-rose-600 font-semibold text-center">{{ $message }}</p>
                    @enderror
                    <p class="mt-1.5 text-[11px] text-slate-500 text-center">
                        Kode ini akan hangus setelah sekali digunakan.
                    </p>
                </div>

                <!-- Toggle Button TOTP / Recovery -->
                <div class="text-center pt-1">
                    <button type="button" 
                            id="toggleRecoveryBtn" 
                            onclick="toggleRecoveryMode()" 
                            class="text-xs text-teal-600 hover:text-teal-800 font-bold transition">
                        🔑 Gunakan Kode Pemulihan Darurat
                    </button>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" 
                            class="w-full py-3.5 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white font-bold rounded-2xl text-sm shadow-lg shadow-teal-700/20 transition transform active:scale-95">
                        Verifikasi Masuk &rarr;
                    </button>
                </div>
            </form>

            <!-- Logout Link -->
            <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Bukan Anda?</span>
                <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold underline">
                        Keluar dari Sesi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let isRecovery = false;

function toggleRecoveryMode() {
    isRecovery = !isRecovery;
    const totpSec = document.getElementById('totpSection');
    const recSec = document.getElementById('recoverySection');
    const toggleBtn = document.getElementById('toggleRecoveryBtn');
    const codeInput = document.getElementById('code');
    const recoveryInput = document.getElementById('recovery_code');

    if (isRecovery) {
        totpSec.classList.add('hidden');
        recSec.classList.remove('hidden');
        toggleBtn.innerText = '📱 Gunakan Kode Google Authenticator (TOTP)';
        codeInput.value = '';
        recoveryInput.focus();
    } else {
        recSec.classList.add('hidden');
        totpSec.classList.remove('hidden');
        toggleBtn.innerText = '🔑 Gunakan Kode Pemulihan Darurat';
        recoveryInput.value = '';
        codeInput.focus();
    }
}

// Auto toggle if recovery code error exists
@if($errors->has('recovery_code'))
    toggleRecoveryMode();
@endif
</script>
@endsection
