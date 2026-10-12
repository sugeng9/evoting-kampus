@extends('layouts.app')

@section('title', 'Aktivasi 2FA Google Authenticator - KPR & PanWas')

@section('content')
<div class="max-w-2xl mx-auto my-6 sm:my-10 px-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-br from-navy-950 via-slate-900 to-navy-900 p-8 text-white relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-48 h-48 bg-teal-500/15 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex items-center space-x-3 mb-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full bg-teal-500/20 text-teal-300 text-[11px] font-bold uppercase tracking-widest border border-teal-500/30">
                    🛡️ Standar Keamanan Tahap 10
                </span>
                <span class="text-xs text-slate-300">Wajib Diaktifkan</span>
            </div>
            
            <h2 class="text-2xl font-black text-white tracking-tight">Aktifkan Google Authenticator (2FA)</h2>
            <p class="text-xs text-slate-300 mt-1">
                Halo, <strong>{{ $user->name }}</strong> ({{ $user->role_label }}). Demi menjamin netralitas dan mencegah peretasan akun kepanitiaan, setiap login panitia wajib diverifikasi menggunakan kode TOTP 6-digit.
            </p>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
            @if(session('warning'))
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl text-amber-900 text-xs font-medium">
                    {{ session('warning') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-900 text-xs font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Langkah 1: Scan QR Code atau Masukkan Manual -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-3">
                    <span class="w-6 h-6 rounded-full bg-navy-950 text-white flex items-center justify-center text-xs font-black">1</span>
                    Pindai QR Code di Google Authenticator
                </h3>
                
                <div class="flex flex-col sm:flex-row items-center gap-6">
                    <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex-shrink-0 text-center">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($qrUri) }}" 
                             alt="QR Code TOTP" 
                             class="w-44 h-44 object-contain mx-auto rounded-lg">
                        <span class="block text-[10px] text-slate-400 mt-1.5 font-mono">Scan via kamera aplikasi</span>
                    </div>

                    <div class="space-y-3 text-xs text-slate-600 flex-1">
                        <p>
                            Buka aplikasi <strong>Google Authenticator</strong> (atau Microsoft Authenticator) pada smartphone Anda, pilih <strong>+</strong> lalu <strong>Scan QR code</strong>.
                        </p>
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block mb-1">
                                Atau masukkan Kunci Pengaturan Manual:
                            </span>
                            <div class="flex items-center gap-2">
                                <code id="secretKeyText" class="font-mono text-sm font-black bg-white border border-slate-300 text-teal-700 px-3 py-1.5 rounded-lg select-all flex-1 tracking-wider">
                                    {{ $secret }}
                                </code>
                                <button type="button" 
                                        onclick="copySecretKey()" 
                                        class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">
                                    Salin
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Langkah 2: Simpan Kode Pemulihan Darurat -->
            <div class="bg-amber-50/60 border border-amber-200 rounded-2xl p-5">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <h3 class="text-sm font-bold text-amber-950 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-black">2</span>
                        Simpan 8 Kode Pemulihan Darurat (Emergency Recovery Codes)
                    </h3>
                    <button type="button" 
                            onclick="copyRecoveryCodes()" 
                            class="px-2.5 py-1 bg-amber-200/80 hover:bg-amber-300 text-amber-900 rounded-md text-[11px] font-bold transition">
                        📋 Salin Semua
                    </button>
                </div>
                <p class="text-xs text-amber-800 mb-3">
                    Simpan kode-kode di bawah ini di tempat aman (password manager / catatan fisik). Setiap kode <strong>hanya dapat digunakan 1 kali</strong> jika ponsel Anda hilang atau rusak.
                </p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 font-mono text-xs font-bold text-slate-800 bg-white p-3 rounded-xl border border-amber-200" id="recoveryCodesContainer">
                    @foreach($recoveryCodes as $code)
                        <div class="p-1.5 text-center bg-slate-50 border border-slate-200 rounded select-all">{{ $code }}</div>
                    @endforeach
                </div>
            </div>

            <!-- Langkah 3: Konfirmasi Kode Awal untuk Aktivasi -->
            <div class="border-t border-slate-200 pt-5">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2 mb-3">
                    <span class="w-6 h-6 rounded-full bg-teal-600 text-white flex items-center justify-center text-xs font-black">3</span>
                    Masukkan Kode 6-Digit dari Google Authenticator untuk Konfirmasi
                </h3>

                <form action="{{ route('admin.2fa.confirm') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Kode TOTP 6-Digit
                        </label>
                        <input type="text" 
                               name="code" 
                               id="code" 
                               maxlength="6" 
                               pattern="[0-9]{6}" 
                               inputmode="numeric" 
                               required 
                               autocomplete="one-time-code" 
                               autofocus
                               placeholder="123456" 
                               class="w-full text-center tracking-[0.5em] text-2xl font-mono font-black py-3 bg-slate-50 border @error('code') border-rose-500 @else border-slate-300 @enderror rounded-xl focus:ring-2 focus:ring-teal-500 focus:bg-white text-slate-900">
                        @error('code')
                            <p class="mt-1.5 text-xs text-rose-600 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <form action="{{ route('admin.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs text-slate-500 hover:text-rose-600 font-bold underline transition">
                                Batalkan &amp; Logout
                            </button>
                        </form>

                        <button type="submit" class="px-6 py-3 bg-gradient-to-r from-teal-600 to-teal-700 hover:from-teal-700 hover:to-teal-800 text-white font-bold rounded-xl text-sm shadow-lg shadow-teal-700/20 transition transform active:scale-95">
                            Verifikasi &amp; Aktifkan 2FA &rarr;
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
function copySecretKey() {
    const text = document.getElementById('secretKeyText').innerText.trim();
    navigator.clipboard.writeText(text).then(() => {
        alert('Kunci rahasia Base32 berhasil disalin ke clipboard!');
    });
}

function copyRecoveryCodes() {
    const codes = Array.from(document.querySelectorAll('#recoveryCodesContainer div'))
                       .map(el => el.innerText.trim())
                       .join('\n');
    navigator.clipboard.writeText(codes).then(() => {
        alert('Seluruh 8 kode pemulihan darurat berhasil disalin ke clipboard! Simpan di tempat yang sangat aman.');
    });
}
</script>
@endsection
