@extends('layouts.app')

@section('title', 'Login & Pendaftaran Pemilih Mahasiswa')

@section('content')
@php
    $hasRegisterErrors = $errors->has('tanggal_lahir') 
        || $errors->has('email') 
        || $errors->has('password')
        || $errors->has('password_confirmation')
        || $errors->has('no_hp')
        || old('tanggal_lahir') !== null
        || old('email') !== null;

    $initialTab = (request('tab') === 'register' || $hasRegisterErrors) ? 'register' : 'login';
@endphp

<div class="max-w-lg mx-auto my-6 sm:my-10">
    <div class="bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-br from-navy-950 via-slate-900 to-navy-900 p-7 text-white text-center relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-12 -bottom-12 w-40 h-40 bg-teal-500/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="flex justify-center mb-1">
                <x-app-logo size="lg" variant="mark" theme="dark" />
            </div>

            <div class="inline-flex items-center space-x-1.5 px-3 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-extrabold uppercase tracking-widest border border-emerald-500/30 mt-3 mb-1">
                <span>Bilik Suara Resmi</span>
                <span>&bull;</span>
                <span>Polbeng</span>
            </div>
            <h2 class="text-2xl font-heading font-black tracking-tight text-white">Portal Pemira Digital</h2>
            <p class="text-xs text-slate-300 font-sans mt-1">Otentikasi &amp; Pendaftaran Hak Suara Mahasiswa</p>
            
            @if(isset($fase))
                <div class="mt-2.5">
                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-slate-800/80 text-slate-300 text-[10px] font-mono border border-slate-700">
                        <span class="w-1.5 h-1.5 rounded-full {{ in_array($fase, ['aktif', 'voting']) ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400' }}"></span>
                        <span>Fase Sistem: <strong class="text-white uppercase">{{ $fase }}</strong></span>
                    </span>
                </div>
            @endif
        </div>

        <!-- Tab Switcher (Masuk vs Daftar) -->
        <div class="px-6 pt-6 pb-2">
            <div class="flex p-1.5 bg-slate-100/90 rounded-2xl border border-slate-200">
                <button type="button" 
                        id="tab-btn-login" 
                        onclick="switchTab('login')" 
                        class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2 {{ $initialTab === 'login' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/60' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    <span>Masuk (Login)</span>
                </button>
                <button type="button" 
                        id="tab-btn-register" 
                        onclick="switchTab('register')" 
                        class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2 {{ $initialTab === 'register' ? 'bg-white text-slate-900 shadow-sm border border-slate-200/60' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span>Daftar Akun Baru</span>
                </button>
            </div>
        </div>

        <!-- Form Body Container -->
        <div class="p-6 sm:p-8 pt-4">

            <!-- ========================================== -->
            <!-- 1. PANEL LOGIN PEMILIH                     -->
            <!-- ========================================== -->
            <div id="panel-login" class="{{ $initialTab === 'login' ? '' : 'hidden' }}">
                <form action="{{ route('voter.login.submit') }}" method="POST" id="loginForm" class="space-y-4">
                    @csrf

                    <!-- Input NIM -->
                    <div>
                        <label for="login_nim" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Nomor Induk Mahasiswa (NIM)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input type="text" 
                                   name="nim" 
                                   id="login_nim" 
                                   value="{{ old('nim') }}" 
                                   required 
                                   autocomplete="username"
                                   placeholder="Contoh: 120120045" 
                                   class="w-full pl-11 pr-4 py-3 bg-slate-50 border @error('nim') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition text-sm">
                        </div>
                        @error('nim')
                            <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Input Kata Sandi Akun Pemilih -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="login_pin" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Kata Sandi (Password Akun)
                            </label>
                            <span class="text-[11px] text-slate-400">Password saat Pendaftaran Mandiri</span>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <input type="password" 
                                   name="pin" 
                                   id="login_pin" 
                                   required 
                                   autocomplete="current-password"
                                   placeholder="Masukkan Kata Sandi akun Anda" 
                                   class="w-full pl-11 pr-11 py-3 bg-slate-50 border @error('pin') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition text-sm">
                            <button type="button" 
                                    onclick="toggleInputVisibility('login_pin', 'loginEyeIcon')" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                                    aria-label="Toggle password visibility">
                                <svg id="loginEyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                        @error('pin')
                            <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Anti-Fraud Notice -->
                    <div class="p-3 rounded-xl bg-amber-50/70 border border-amber-200 text-amber-900 text-xs leading-relaxed flex items-start space-x-2.5">
                        <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span><strong>1 NIM = 1 Suara.</strong> Sesi bilik suara terenkripsi dan terlindung dari akses ganda secara bersamaan.</span>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            id="loginSubmitBtn" 
                            class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-0.5 flex items-center justify-center space-x-2 text-sm">
                        <span id="loginBtnText">Masuk ke Bilik Suara</span>
                        <svg id="loginBtnSpinner" class="hidden animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </button>

                    <!-- Switch to Register -->
                    <div class="pt-3 border-t border-slate-100 text-center space-y-2.5">
                        <div class="text-xs text-slate-500">
                            Belum terdaftar sebagai pemilih?
                            <button type="button" onclick="switchTab('register')" class="font-bold text-indigo-600 hover:text-indigo-700 hover:underline ml-1">
                                Daftar Akun Baru Sekarang &rarr;
                            </button>
                        </div>

                        <div>
                            <a href="{{ route('nim.status.form') }}" class="inline-flex items-center space-x-1.5 py-1.5 px-3 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-200 transition">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <span>Cek Status Kepemilikan NIM</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ========================================== -->
            <!-- 2. PANEL PENDAFTARAN PEMILIH MANDIRI       -->
            <!-- ========================================== -->
            <div id="panel-register" class="{{ $initialTab === 'register' ? '' : 'hidden' }}">
                @if($allowsPendaftaran ?? true)
                    <form action="{{ route('daftar.otp.send') }}" method="POST" id="registerForm" class="space-y-4">
                        @csrf

                        @if(request('kode') || old('kode_pendaftaran'))
                            <div>
                                <label for="reg_kode_pendaftaran" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                                    Kode Pemulihan Pendaftaran
                                </label>
                                <input type="text" 
                                       name="kode_pendaftaran" 
                                       id="reg_kode_pendaftaran" 
                                       value="{{ request('kode') ?? old('kode_pendaftaran') }}" 
                                       readonly
                                       class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-300 rounded-xl text-slate-600 font-mono text-xs">
                            </div>
                        @endif

                        <!-- NIM Mahasiswa -->
                        <div>
                            <label for="reg_nim" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nomor Induk Mahasiswa (NIM)
                            </label>
                            <input type="text" 
                                   name="nim" 
                                   id="reg_nim" 
                                   value="{{ old('nim') }}" 
                                   required 
                                   placeholder="Contoh: 120510001 (sesuai KTM)" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border @error('nim') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-sm">
                            @error('nim')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Lahir -->
                        <div>
                            <label for="reg_dob" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Tanggal Lahir Mahasiswa
                            </label>
                            <input type="date" 
                                   name="tanggal_lahir" 
                                   id="reg_dob" 
                                   value="{{ old('tanggal_lahir') }}" 
                                   required 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border @error('tanggal_lahir') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-sm">
                            <p class="mt-1 text-[11px] text-slate-400">Dibutuhkan untuk validasi data akademik Polbeng.</p>
                            @error('tanggal_lahir')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email Pribadi -->
                        <div>
                            <label for="reg_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Alamat Email Pribadi Aktif
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="reg_email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   placeholder="mahasiswa@gmail.com" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border @error('email') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-sm">
                            <p class="mt-1 text-[11px] text-slate-400">Kode OTP 6-digit untuk verifikasi akun akan dikirimkan ke email ini.</p>
                            @error('email')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- No HP / WhatsApp (Opsional) -->
                        <div>
                            <label for="reg_phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Nomor HP / WhatsApp <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                            </label>
                            <input type="tel" 
                                   name="no_hp" 
                                   id="reg_phone" 
                                   value="{{ old('no_hp') }}" 
                                   placeholder="Contoh: 081234567890" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border @error('no_hp') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-sm">
                            @error('no_hp')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Baru -->
                        <div>
                            <label for="reg_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Buat Kata Sandi (Password)
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       name="password" 
                                       id="reg_password" 
                                       required 
                                       autocomplete="new-password"
                                       placeholder="Minimal 8 karakter kombinasi huruf & angka" 
                                       class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border @error('password') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-sm">
                                <button type="button" 
                                        onclick="toggleInputVisibility('reg_password', 'regEyeIcon')" 
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                                        aria-label="Toggle password visibility">
                                    <svg id="regEyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <label for="reg_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                Konfirmasi Kata Sandi
                            </label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="reg_password_confirmation" 
                                   required 
                                   autocomplete="new-password"
                                   placeholder="Ulangi kata sandi di atas" 
                                   class="w-full px-3.5 py-2.5 bg-slate-50 border @error('password_confirmation') border-rose-500 @else border-slate-300 @enderror rounded-xl text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition text-sm">
                            @error('password_confirmation')
                                <p class="mt-1 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button Pendaftaran -->
                        <button type="submit" 
                                id="regSubmitBtn" 
                                class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-bold rounded-xl shadow-lg shadow-indigo-600/30 transition transform hover:-translate-y-0.5 flex items-center justify-center space-x-2 text-sm mt-2">
                            <span id="regBtnText">Daftar &amp; Kirim Kode OTP &rarr;</span>
                            <svg id="regBtnSpinner" class="hidden animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                        </button>

                        <!-- Switch back to Login -->
                        <div class="pt-3 border-t border-slate-100 text-center">
                            <div class="text-xs text-slate-500">
                                Sudah pernah mendaftar akun?
                                <button type="button" onclick="switchTab('login')" class="font-bold text-emerald-600 hover:text-emerald-700 hover:underline ml-1">
                                    Masuk di sini &rarr;
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    <!-- Notifikasi jika fase pendaftaran ditutup -->
                    <div class="p-6 text-center space-y-4 bg-slate-50 rounded-2xl border border-slate-200">
                        <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Fase Pendaftaran Ditutup</h3>
                            <p class="text-xs text-slate-500 mt-1">
                                Sistem saat ini berada dalam fase <strong class="text-slate-800 uppercase">{{ $fase ?? 'Non-Aktif' }}</strong>.
                                Pendaftaran akun mandiri baru hanya dibuka saat fase Pendaftaran.
                            </p>
                        </div>
                        <div class="pt-2 flex flex-col space-y-2">
                            <button type="button" onclick="switchTab('login')" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition">
                                🔑 Masuk dengan Akun Terdaftar
                            </button>
                            <a href="{{ route('nim.status.form') }}" class="w-full py-2.5 px-4 bg-white hover:bg-slate-100 text-slate-700 font-bold rounded-xl text-xs border border-slate-300 transition">
                                🔍 Cek Status Kepemilikan NIM
                            </a>
                        </div>
                    </div>
                @endif
            </div>

        </div>

        <!-- Security Footer -->
        <div class="px-8 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
            <span class="flex items-center space-x-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Enkripsi TLS 1.3 Aktif</span>
            </span>
            <span>Bilik Suara Terisolasi</span>
        </div>
    </div>



    <!-- Link ke Login Admin -->
    <div class="mt-6 text-center">
        <a href="{{ route('admin.login') }}" class="inline-flex items-center space-x-2 text-xs font-bold text-slate-600 hover:text-teal-700 bg-white hover:bg-teal-50 px-4 py-2.5 rounded-2xl border border-slate-200 hover:border-teal-300 shadow-sm transition group">
            <div class="w-6 h-6 rounded-lg bg-teal-100 group-hover:bg-teal-600 flex items-center justify-center transition">
                <svg class="w-3.5 h-3.5 text-teal-700 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <span>Login sebagai Panitia / Administrator KPR &rarr;</span>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function switchTab(tab) {
        const loginBtn = document.getElementById('tab-btn-login');
        const registerBtn = document.getElementById('tab-btn-register');
        const loginPanel = document.getElementById('panel-login');
        const registerPanel = document.getElementById('panel-register');

        if (tab === 'register') {
            loginBtn.classList.remove('bg-white', 'text-slate-900', 'shadow-sm', 'border', 'border-slate-200/60');
            loginBtn.classList.add('text-slate-500', 'hover:text-slate-800');

            registerBtn.classList.add('bg-white', 'text-slate-900', 'shadow-sm', 'border', 'border-slate-200/60');
            registerBtn.classList.remove('text-slate-500', 'hover:text-slate-800');

            loginPanel.classList.add('hidden');
            registerPanel.classList.remove('hidden');

            const regNimInput = document.getElementById('reg_nim');
            if (regNimInput && !regNimInput.value) {
                regNimInput.focus();
            }

            if (history.replaceState) {
                history.replaceState(null, null, '#register');
            }
        } else {
            registerBtn.classList.remove('bg-white', 'text-slate-900', 'shadow-sm', 'border', 'border-slate-200/60');
            registerBtn.classList.add('text-slate-500', 'hover:text-slate-800');

            loginBtn.classList.add('bg-white', 'text-slate-900', 'shadow-sm', 'border', 'border-slate-200/60');
            loginBtn.classList.remove('text-slate-500', 'hover:text-slate-800');

            registerPanel.classList.add('hidden');
            loginPanel.classList.remove('hidden');

            const loginNimInput = document.getElementById('login_nim');
            if (loginNimInput && !loginNimInput.value) {
                loginNimInput.focus();
            }

            if (history.replaceState) {
                history.replaceState(null, null, '#login');
            }
        }
    }

    function toggleInputVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (!input) return;

        if (input.type === 'password') {
            input.type = 'text';
            if (icon) {
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>`;
            }
        } else {
            input.type = 'password';
            if (icon) {
                icon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>`;
            }
        }
    }

    function fillTestAccount(nim, pin) {
        switchTab('login');
        const nimInput = document.getElementById('login_nim');
        const pinInput = document.getElementById('login_pin');
        if (nimInput && pinInput) {
            nimInput.value = nim;
            pinInput.value = pin;
            nimInput.classList.add('ring-2', 'ring-emerald-500');
            pinInput.classList.add('ring-2', 'ring-emerald-500');
            setTimeout(() => {
                nimInput.classList.remove('ring-2', 'ring-emerald-500');
                pinInput.classList.remove('ring-2', 'ring-emerald-500');
            }, 1000);
        }
    }

    function fillTestRegistration(nim, dob, email) {
        switchTab('register');
        const nimInput = document.getElementById('reg_nim');
        const dobInput = document.getElementById('reg_dob');
        const emailInput = document.getElementById('reg_email');
        const passInput = document.getElementById('reg_password');
        const confirmPassInput = document.getElementById('reg_password_confirmation');

        if (nimInput) nimInput.value = nim;
        if (dobInput) dobInput.value = dob;
        if (emailInput) emailInput.value = email;
        if (passInput) passInput.value = 'Polbeng2026!';
        if (confirmPassInput) confirmPassInput.value = 'Polbeng2026!';

        [nimInput, dobInput, emailInput, passInput, confirmPassInput].forEach(el => {
            if (el) {
                el.classList.add('ring-2', 'ring-indigo-500');
                setTimeout(() => el.classList.remove('ring-2', 'ring-indigo-500'), 1000);
            }
        });
    }

    // Hash & URL query detection on load
    document.addEventListener('DOMContentLoaded', function() {
        const hash = window.location.hash;
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');

        if (hash === '#register' || tabParam === 'register' || {{ $initialTab === 'register' ? 'true' : 'false' }}) {
            switchTab('register');
        } else {
            switchTab('login');
        }
    });

    // Anti Double-Submit Guard: Login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            const btn = document.getElementById('loginSubmitBtn');
            const btnText = document.getElementById('loginBtnText');
            const spinner = document.getElementById('loginBtnSpinner');

            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
            }
            if (btnText) btnText.innerText = 'Mengautentikasi...';
            if (spinner) spinner.classList.remove('hidden');
        });
    }

    // Anti Double-Submit Guard: Register
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function() {
            const btn = document.getElementById('regSubmitBtn');
            const btnText = document.getElementById('regBtnText');
            const spinner = document.getElementById('regBtnSpinner');

            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-75', 'cursor-not-allowed');
            }
            if (btnText) btnText.innerText = 'Memverifikasi data & mengirim OTP...';
            if (spinner) spinner.classList.remove('hidden');
        });
    }
</script>
@endpush
