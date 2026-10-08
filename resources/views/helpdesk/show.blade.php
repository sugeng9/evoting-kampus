<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Sengketa NIM: ') }} <span class="font-mono">{{ $sengketa->nim ?? '' }}</span>
            </h2>
            <a href="{{ route('helpdesk.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Kembali ke daftar</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('kode_plain'))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-r-md shadow-sm">
                <h3 class="text-lg font-medium text-yellow-800 mb-2">Kode Daftar Ulang Berhasil Dibuat!</h3>
                <p class="text-sm text-yellow-700 mb-4">Berikan kode ini kepada mahasiswa yang bersangkutan untuk mendaftar kembali.</p>
                <div class="bg-white border border-yellow-300 rounded p-4 text-center">
                    <span class="text-3xl font-mono font-bold tracking-widest text-gray-900">{{ session('kode_plain') }}</span>
                </div>
                <p class="text-xs text-red-600 mt-2 font-bold">PENTING: Kode ini HANYA DITAMPILKAN SEKALI INI SAJA. Segera salin/catat!</p>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data Laporan -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Laporan</h3>
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    @if(($sengketa->status ?? '') == 'baru')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Baru</span>
                                    @elseif(($sengketa->status ?? '') == 'diproses')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Diproses</span>
                                    @elseif(($sengketa->status ?? '') == 'selesai')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                    @elseif(($sengketa->status ?? '') == 'ditolak')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-sm font-medium text-gray-500">Dilaporkan Pada</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ isset($sengketa->created_at) ? $sengketa->created_at->format('d M Y H:i') : '' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Kontak Pelapor</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $sengketa->pelapor_kontak ?? '' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Alasan</dt>
                                <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded border border-gray-100">{{ $sengketa->alasan ?? '' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Info Akun Saat Ini -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Akun Pemilih Saat Ini</h3>
                        @if(isset($voter))
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Status Akun</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        @if($voter->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending / Nonaktif</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Mendaftar Pada</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $voter->created_at->format('d M Y H:i') }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Email Terdaftar (Masked)</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $maskedEmail ?? '' }}</dd>
                                </div>
                            </dl>
                        @else
                            <div class="bg-gray-50 p-4 rounded text-center text-sm text-gray-500">
                                Akun pemilih tidak ditemukan atau sudah dihapus sepenuhnya.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Panel Aksi -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Aksi Penanganan</h3>

                    @if(isset($faseFrozen) && $faseFrozen)
                        <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <p class="text-sm text-yellow-700">Fase saat ini tidak mengizinkan perubahan data akun. Anda hanya dapat melihat data sengketa.</p>
                        </div>
                    @else
                        
                        <div class="space-y-6">
                            @if(($sengketa->status ?? '') == 'baru')
                                <form action="{{ route('helpdesk.sengketa.proses', $sengketa->id ?? 0) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">Tandai Sedang Diproses</button>
                                </form>
                            @endif

                            @if(in_array(($sengketa->status ?? ''), ['baru', 'diproses']))
                                <div class="border-t pt-4">
                                    <h4 class="font-medium text-sm text-gray-700 mb-2">Tindakan Cabut Akun (2 Petugas)</h4>
                                    
                                    @if(isset($voter) && ($voter->pendaftaran_status ?? '') !== 'dicabut')
                                        <form action="{{ route('helpdesk.sengketa.cabut', $sengketa->id ?? 0) }}" method="POST" class="space-y-4 max-w-md">
                                            @csrf
                                            <div class="flex items-start">
                                                <div class="flex items-center h-5">
                                                    <input id="ktm_diverifikasi" name="ktm_diverifikasi" value="1" type="checkbox" required class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                </div>
                                                <div class="ml-3 text-sm">
                                                    <label for="ktm_diverifikasi" class="font-medium text-gray-700">KTM Asli Sudah Diverifikasi</label>
                                                    <p class="text-gray-500">Saya menyatakan bahwa pelapor telah menunjukkan bukti KTM asli secara fisik atau video call resmi.</p>
                                                </div>
                                            </div>
                                            
                                            <div class="space-y-2">
                                                @if(!$sengketa->disetujui_oleh)
                                                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                                                        <strong>Tahap 1:</strong> Ditangani oleh: <em>{{ $sengketa->petugasA->name ?? 'Belum ada' }}</em>.<br>
                                                        Diperlukan persetujuan dari petugas kedua yang berbeda.
                                                    </div>
                                                    @if($sengketa->ditangani_oleh && $sengketa->ditangani_oleh !== auth()->guard('web')->id())
                                                        <button type="submit" formaction="{{ route('helpdesk.sengketa.setujui', $sengketa->id) }}" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-amber-700">
                                                            ✓ Setujui Pencabutan (Sebagai Petugas 2)
                                                        </button>
                                                    @elseif(!$sengketa->ditangani_oleh)
                                                        <p class="text-xs text-slate-500">Klik "Tandai Sedang Diproses" terlebih dahulu sebelum mengajukan pencabutan.</p>
                                                    @else
                                                        <p class="text-xs text-amber-700 font-semibold">Anda adalah Petugas 1 (yang menangani). Silakan minta petugas helpdesk lain untuk menyetujui laporan ini.</p>
                                                    @endif
                                                @else
                                                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-800 mb-2">
                                                        ✓ Telah disetujui oleh Petugas 2: <em>{{ $sengketa->petugasB->name ?? 'Petugas B' }}</em>. Siap dieksekusi.
                                                    </div>
                                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-rose-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-700">
                                                        Cabut Akun Palsu Sekarang
                                                    </button>
                                                @endif
                                            </div>
                                        </form>
                                    @else
                                        <p class="text-sm text-gray-500 italic">Akun pemilih sudah dalam status dicabut.</p>
                                    @endif
                                </div>

                                <div class="border-t pt-4">
                                    <h4 class="font-medium text-sm text-gray-700 mb-2">Tolak Laporan</h4>
                                    <form action="{{ route('helpdesk.sengketa.tolak', $sengketa->id ?? 0) }}" method="POST" class="max-w-md space-y-3">
                                        @csrf
                                        <textarea name="alasan_tolak" rows="2" required placeholder="Alasan penolakan..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Tolak Sengketa</button>
                                    </form>
                                </div>
                            @endif

                            @if(($sengketa->status ?? '') == 'selesai')
                                <div class="border-t pt-4">
                                    <h4 class="font-medium text-sm text-gray-700 mb-2">Terbitkan Kode Daftar Ulang</h4>
                                    <p class="text-xs text-gray-500 mb-3">Jika akun lama telah berhasil dicabut, Anda dapat menerbitkan kode khusus agar mahasiswa ini bisa mendaftar ulang.</p>
                                    <form action="{{ route('helpdesk.sengketa.kode', $sengketa->id ?? 0) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">Terbitkan Kode Daftar Ulang</button>
                                    </form>
                                </div>
                            @endif
                            
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
