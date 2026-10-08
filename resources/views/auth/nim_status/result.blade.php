<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Cek Status - PEMIRA POLBENG</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Status Pendaftaran</h1>
            <p class="text-lg font-mono font-bold mt-2 text-indigo-600">{{ $nim }}</p>
        </div>

        <div class="flex justify-center mb-6">
            @if($status === 'belum_terdaftar')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-gray-100 text-gray-800 border border-gray-200">Belum Terdaftar</span>
            @elseif($status === 'pending')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">Menunggu Verifikasi (PENDING)</span>
            @elseif($status === 'aktif')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-green-100 text-green-800 border border-green-200">Aktif - Boleh Memilih</span>
            @elseif($status === 'dicabut')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-red-100 text-red-800 border border-red-200">Akun Dicabut</span>
            @endif
        </div>

        @if($voter)
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 text-sm text-gray-700 space-y-2 mb-4">
                <p><strong>Mendaftar Pada:</strong> {{ $voter->created_at->format('d M Y H:i') }} WIB</p>
                <p><strong>Email Terdaftar:</strong> {{ $maskedEmail }}</p>
            </div>
        @endif

        {{-- STATUS PENANGANAN SENGKETA --}}
        @if($sengketa)
            @if($sengketa->status === 'selesai' && $status === 'dicabut')
                <div class="mb-5 p-4 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-900 space-y-2">
                    <div class="flex items-center space-x-2 font-bold text-emerald-800 text-sm">
                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>Sengketa Diterima & Disetujui KPR</span>
                    </div>
                    <p class="text-emerald-800 leading-relaxed">
                        Laporan kepemilikan Anda telah <strong>diverifikasi dan disetujui</strong> oleh panitia. Akun lama yang disalahgunakan telah berhasil <strong>dicabut</strong>.
                    </p>
                    @if($sengketa->catatan_petugas)
                        <div class="p-2.5 bg-white/80 rounded-lg border border-emerald-200 font-mono text-[11px] text-slate-700">
                            <strong>Catatan Panitia:</strong> {{ $sengketa->catatan_petugas }}
                        </div>
                    @endif
                    <div class="pt-2">
                        <a href="{{ route('daftar.form', ['kode' => 1, 'nim' => $nim]) }}" class="w-full inline-flex justify-center items-center py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm transition-colors text-xs">
                            Daftar Ulang Akun Resmi Anda (Gunakan Kode) &rarr;
                        </a>
                        <p class="text-[11px] text-emerald-700 text-center mt-1">*Masukkan Kode Pemulihan 1x pakai yang diberikan petugas Helpdesk.</p>
                    </div>
                </div>
            @elseif($sengketa->status === 'selesai' && str_contains($sengketa->catatan_petugas ?? '', 'DITOLAK'))
                <div class="mb-5 p-4 bg-rose-50 rounded-xl border border-rose-200 text-xs text-rose-900 space-y-2">
                    <div class="flex items-center space-x-2 font-bold text-rose-800 text-sm">
                        <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <span>Laporan Sengketa Ditolak</span>
                    </div>
                    <p class="text-rose-800 leading-relaxed">
                        Laporan sengketa kepemilikan untuk NIM ini <strong>tidak dapat diterima</strong> oleh panitia Helpdesk.
                    </p>
                    @if($sengketa->catatan_petugas)
                        <div class="p-2.5 bg-white/80 rounded-lg border border-rose-200 font-mono text-[11px] text-slate-700">
                            <strong>Alasan Penolakan:</strong> {{ $sengketa->catatan_petugas }}
                        </div>
                    @endif
                    <p class="text-[11px] text-rose-700 mt-1">
                        *Jika Anda adalah pemilik akun yang sah dan hanya lupa kata sandi, silakan minta <strong>Reset Kata Sandi</strong> kepada panitia di lokasi bilik suara.
                    </p>
                </div>
            @elseif(in_array($sengketa->status, ['baru', 'diproses']))
                <div class="mb-5 p-4 bg-blue-50 rounded-xl border border-blue-200 text-xs text-blue-900 space-y-2">
                    <div class="flex items-center space-x-2 font-bold text-blue-800 text-sm">
                        <svg class="w-4 h-4 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Laporan Sedang Ditinjau Helpdesk</span>
                    </div>
                    <p class="text-blue-800 leading-relaxed">
                        Laporan sengketa untuk NIM ini berstatus <strong>{{ strtoupper($sengketa->status) }}</strong>.
                    </p>
                    <div class="p-2.5 bg-white/80 rounded-lg border border-blue-200 text-[11px] text-slate-700">
                        <strong>Instruksi:</strong> Silakan datangi meja Helpdesk KPR di kampus dengan membawa <strong>KTM Fisik Asli</strong> dan akses SIAKAD untuk menyelesaikan proses verifikasi.
                    </div>
                </div>
            @endif
        @endif

        {{-- OPSI LAPORAN JIKA BELUM PERNAH SENGKETA DAN MASIH DIBUKA --}}
        @if($status !== 'belum_terdaftar' && (! $sengketa || $sengketa->status === 'selesai' && $status !== 'dicabut'))
            @if($faseAllowsSengketa ?? false)
                <div class="text-center mt-5 p-4 bg-slate-50 rounded-xl border border-slate-200">
                    <p class="text-xs text-gray-600 mb-2">Merasa tidak pernah mendaftarkan email di atas?</p>
                    <a href="{{ route('nim.sengketa.form', ['nim' => $nim]) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-xs font-semibold text-white bg-red-600 hover:bg-red-700 focus:outline-none transition-colors">
                        Ini Bukan Saya! (Ajukan Sengketa)
                    </a>
                </div>
            @elseif(($fase ?? '') === 'voting')
                <div class="mt-5 bg-amber-50 border-l-4 border-amber-400 p-3 rounded-r-lg text-xs text-amber-800">
                    <p class="font-medium">Fase Pemungutan Suara Sedang Berjalan</p>
                    <p class="text-[11px] mt-0.5 text-amber-700">Perubahan kepemilikan akun dikunci demi keamanan hasil suara. Laporan diterima sebagai catatan insiden pengawasan.</p>
                </div>
            @endif
        @endif

        <div class="mt-8 text-center space-y-3">
            @if($status === 'belum_terdaftar' && in_array($fase ?? '', ['pendaftaran', 'sengketa']))
                <a href="{{ route('daftar.form') }}" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Daftar Sekarang
                </a>
            @endif
            <a href="{{ route('nim.status.form') }}" class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Cek NIM Lain
            </a>
            <a href="{{ url('/') }}" class="text-sm text-indigo-600 hover:text-indigo-500 block pt-2 font-medium">Kembali ke Beranda</a>
        </div>
    </div>
</body>
</html>
