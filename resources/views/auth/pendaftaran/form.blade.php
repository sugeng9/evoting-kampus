<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pemilih - PEMIRA POLBENG</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">PEMIRA POLBENG</h1>
            <p class="text-gray-600">Pendaftaran Pemilih</p>
        </div>

        @if(App\Models\SystemPhase::current()->fase ?? false)
            <div class="mb-4 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Fase Saat Ini: {{ ucfirst(App\Models\SystemPhase::current()->fase) }}
                </span>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-md border border-red-200">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('daftar.otp.send') }}" class="space-y-4">
            @csrf
            
            @if(request('kode'))
                <div>
                    <label for="kode_pendaftaran" class="block text-sm font-medium text-gray-700">Kode Pendaftaran</label>
                    <input type="text" name="kode_pendaftaran" id="kode_pendaftaran" value="{{ request('kode') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm border p-2 bg-gray-50 text-gray-500" readonly>
                </div>
            @endif

            <div>
                <label for="nim" class="block text-sm font-medium text-gray-700">NIM</label>
                <input type="text" name="nim" id="nim" value="{{ old('nim') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono border p-2">
            </div>

            <div>
                <label for="tanggal_lahir" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Pribadi</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>

            <div>
                <label for="no_hp" class="block text-sm font-medium text-gray-700">Nomor HP (Opsional)</label>
                <input type="tel" name="no_hp" id="no_hp" value="{{ old('no_hp') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
                <p class="text-xs text-gray-500 mt-1">Gunakan minimal 8 karakter dengan kombinasi huruf dan angka.</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>

            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Kirim OTP
            </button>
        </form>

        <div class="mt-6 text-center space-y-2">
            <a href="{{ route('nim.status.form') }}" class="text-sm text-indigo-600 hover:text-indigo-500 block">Cek Status NIM Saya</a>
            <p class="text-sm text-gray-600">Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500 font-medium">Login</a></p>
        </div>
    </div>
</body>
</html>
