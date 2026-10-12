@extends('layouts.app')

@section('title', 'Login Panitia KPR & PanWas - Politeknik Negeri Bengkalis')

@section('content')
<div class="max-w-md mx-auto my-6 sm:my-12">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
        
        <!-- Header Banner Admin -->
        <div class="bg-gradient-to-br from-navy-950 via-slate-900 to-navy-900 p-8 text-white text-center relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-12 -bottom-12 w-40 h-40 bg-teal-500/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="flex justify-center mb-1">
                <x-app-logo size="lg" variant="mark" theme="dark" />
            </div>

            <div class="inline-block px-3 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-extrabold uppercase tracking-widest border border-amber-500/30 mt-3 mb-1">
                Otoritas Panitia &bull; KPR &amp; PanWas
            </div>
            <h2 class="text-2xl font-heading font-black tracking-tight text-white">Portal Panel PanWas &amp; KPR</h2>
            <p class="text-xs text-slate-300 font-sans mt-1">Pusat Kendali Pemira &bull; Politeknik Negeri Bengkalis</p>
        </div>

        <!-- Form Body -->
        <div class="p-8">
            <form action="{{ route('admin.login.submit') }}" method="POST" id="adminLoginForm" class="space-y-5">
                @csrf

                <!-- Input Username -->
                <div>
                    <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Username Administrator / Panitia
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input type="text" 
                               name="username" 
                               id="username" 
                               value="{{ old('username') }}" 
                               required 
                               autocomplete="username"
                               autofocus
                               placeholder="Contoh: admin_kpr" 
                               class="w-full pl-11 pr-4 py-3 bg-slate-50 border @error('username') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition text-sm">
                    </div>
                    @error('username')
                        <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Input Password -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Kata Sandi (Password)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               required 
                               autocomplete="current-password"
                               placeholder="Masukkan kata sandi panitia" 
                               class="w-full pl-11 pr-11 py-3 bg-slate-50 border @error('password') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white transition text-sm">
                        <button type="button" 
                                onclick="togglePasswordVisibility()" 
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Security Notice -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center space-x-2 text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                        <span>Ingat sesi ini</span>
                    </label>
                    <span class="text-slate-400 text-[11px]">Audit IP Otomatis</span>
                </div>

                @if(config('services.turnstile.enabled'))
                    <!-- Cloudflare Turnstile Anti-Bot Protection -->
                    <div class="flex flex-col items-center justify-center my-2">
                        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="light"></div>
                        @error('cf-turnstile-response')
                            <p class="mt-1.5 text-xs text-rose-600 font-semibold text-center">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Submit Button -->
                <button type="submit" 
                        id="adminSubmitBtn" 
                        class="w-full py-3.5 px-4 bg-navy-900 hover:bg-slate-800 active:bg-black text-white font-bold rounded-xl shadow-lg transition flex items-center justify-center space-x-2 text-xs uppercase tracking-wider">
                    <span id="adminBtnText">Masuk ke Panel Kontrol</span>
                    <svg id="adminBtnSpinner" class="hidden animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                </button>
            </form>

            <!-- Quick Fill Demo Admin Account -->
            <div class="mt-6 p-3.5 bg-slate-50 rounded-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-slate-700 flex items-center space-x-1">
                        <span>🔑 Akun Default Administrator</span>
                    </span>
                    <button type="button" 
                            onclick="fillAdminCredentials('admin_kpu', 'AdminKpu2026!Secure')"
                            class="text-[10px] font-bold text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 px-2 py-0.5 rounded transition">
                        Klik Isi Otomatis &rarr;
                    </button>
                </div>
                <div class="text-[11px] font-mono text-slate-500 space-y-0.5">
                    <div>Username: <span class="text-slate-800 font-bold">admin_kpu</span></div>
                    <div>Password: <span class="text-slate-800 font-bold">AdminKpu2026!Secure</span></div>
                </div>
            </div>

            <!-- Switch to Voter Login -->
            <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                <a href="{{ route('voter.login') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-bold">
                    &larr; Masuk sebagai Mahasiswa Pemilih (NIM)
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility() {
        const pass = document.getElementById('password');
        pass.type = (pass.type === 'password') ? 'text' : 'password';
    }

    function fillAdminCredentials(u, p) {
        const userInput = document.getElementById('username');
        const passInput = document.getElementById('password');
        userInput.value = u;
        passInput.value = p;
        userInput.classList.add('ring-2', 'ring-teal-500');
        passInput.classList.add('ring-2', 'ring-teal-500');
        setTimeout(() => {
            userInput.classList.remove('ring-2', 'ring-teal-500');
            passInput.classList.remove('ring-2', 'ring-teal-500');
        }, 1000);
    }

    document.getElementById('adminLoginForm').addEventListener('submit', function() {
        const btn = document.getElementById('adminSubmitBtn');
        const text = document.getElementById('adminBtnText');
        const spinner = document.getElementById('adminBtnSpinner');

        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        text.innerText = 'Memverifikasi Akses...';
        spinner.classList.remove('hidden');
    });
</script>
@endpush
