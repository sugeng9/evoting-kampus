<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Sengketa - PEMIRA POLITEKNIK NEGERI BENGKALIS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-red-600">Laporan Sengketa Kepemilikan NIM</h1>
            <p class="text-gray-600 text-sm mt-2">NIM: <span class="font-mono font-bold">{{ request('nim', $nim ?? '') }}</span></p>
        </div>

        <div class="bg-red-50 p-4 rounded-md border border-red-200 mb-6">
            <p class="text-xs text-red-700 text-center font-bold">PERINGATAN: Laporan palsu dapat diproses secara hukum dan sanksi akademik.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-md border border-red-200">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('nim.sengketa.submit') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="nim" value="{{ request('nim', $nim ?? '') }}">
            
            <div>
                <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700">Verifikasi Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2">
            </div>

            <div>
                <label for="pelapor_kontak" class="block text-sm font-medium text-gray-700">Kontak Anda (No HP/Email)</label>
                <input type="text" name="pelapor_kontak" id="pelapor_kontak" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2" placeholder="0812xxxx / email@contoh.com">
            </div>

            <div>
                <label for="alasan" class="block text-sm font-medium text-gray-700">Detail Laporan (min 20 karakter)</label>
                <textarea name="alasan" id="alasan" rows="3" required minlength="20" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm border p-2" placeholder="Jelaskan mengapa Anda melaporkan akun ini (misal: saya belum mendaftar tapi nim saya dipakai orang lain)..."></textarea>
            </div>

            <div>
                <label for="bukti_ktm" class="block text-sm font-medium text-gray-700">
                    Unggah Bukti KTM Asli <span class="text-xs text-gray-500 font-normal">(Opsional / Sangat Direkomendasikan)</span>
                </label>
                <input type="file" name="bukti_ktm" id="bukti_ktm" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 border border-gray-200 rounded-md p-1">
                <p class="text-[11px] text-gray-500 mt-1">Format: JPG, PNG, atau PDF. Ukuran maks 3MB. Berkas disimpan terenkripsi di server terisolasi.</p>
            </div>

            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Kirim Laporan
            </button>
        </form>

        <div class="mt-6 text-center space-y-2">
            <div>
                <a href="{{ route('nim.status.form') }}" class="text-sm text-gray-600 hover:text-gray-900 font-medium">Batal</a>
            </div>
            <div class="pt-2 border-t border-slate-100 text-xs text-slate-500">
                <span>Perlu bantuan cepat? Hubungi WhatsApp Helpdesk: </span>
                <a href="https://wa.me/6285187953580?text=Halo%20Panitia%20Helpdesk%20KPR%20Pemira,%20saya%20butuh%20bantuan%20terkait%20sengketa%20NIM" target="_blank" class="font-bold text-emerald-600 hover:underline">
                    +62 851-8795-3580 &nearr;
                </a>
            </div>
        </div>
    </div>
</body>
</html>
