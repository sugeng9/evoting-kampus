<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pemilih - PEMIRA POLBENG</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Login Pemilih</h1>
            <p class="text-gray-600 mt-1">PEMIRA POLBENG</p>
        </div>

        @if(isset($fase) && !in_array($fase, ['aktif', 'voting']))
            <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-3 rounded-r-md">
                <p class="text-xs text-emerald-800">Fase <strong>{{ ucfirst($fase) }}</strong>: Silakan login untuk mengecek profil DPT dan kandidat Anda sebelum bilik suara dibuka.</p>
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

        @if(session('lockedUntil'))
            <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-md border border-red-200 text-sm">
                Terlalu banyak percobaan gagal. Silakan coba lagi pada: <strong>{{ \Carbon\Carbon::parse(session('lockedUntil'))->format('H:i') }}</strong>
            </div>
        @endif

        <form method="POST" action="{{ route('pemilih.login') }}" class="space-y-4">
            @csrf
            
            <div>
                <label for="nim" class="block text-sm font-medium text-gray-700">NIM</label>
                <input type="text" name="nim" id="nim" value="{{ old('nim') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-mono border p-2">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2">
            </div>

            @if($showCaptcha ?? false)
                <div class="mt-4 p-4 border rounded-md bg-gray-50 flex items-center justify-center">
                    <div id="captcha-placeholder" class="text-gray-500 text-sm">[CAPTCHA Placeholder]</div>
                </div>
            @endif

            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Masuk
            </button>
        </form>

        <div class="mt-6 text-center space-y-3">
            <a href="{{ route('daftar.form') }}" class="text-sm text-indigo-600 hover:text-indigo-500 block font-medium">Belum punya akun? Daftar</a>
            <a href="{{ route('nim.status.form') }}" class="text-sm text-gray-600 hover:text-gray-900 block">Cek Status NIM</a>
        </div>
    </div>
</body>
</html>
