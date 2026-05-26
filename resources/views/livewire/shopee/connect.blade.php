<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hubungkan dengan Shopee</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
            <div class="mb-6">
                {{-- <img src="https://deo.shopeemobile.com/shopee/shopee-logo.png" alt="Shopee" class="h-12 mx-auto"> --}}
            </div>

            <h2 class="text-2xl font-bold mb-4">Hubungkan dengan Shopee</h2>

            <p class="text-gray-600 mb-6">
                Halo <strong>{{ Auth::user()->name }}</strong>!<br>
                Hubungkan akun Shopee Anda untuk memulai.
            </p>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <a href="{{ route('shopee.redirect') }}"
               class="inline-flex items-center justify-center w-full px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg transition duration-200">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm0 13c-2.33 0-4.31-1.46-5.11-3.5h10.22c-.8 2.04-2.78 3.5-5.11 3.5z"/>
                </svg>
                Hubungkan dengan Shopee
            </a>

            <p class="text-xs text-gray-500 mt-6">
                Dengan menghubungkan, Anda menyetujui<br>
                <a href="#" class="text-blue-500">Ketentuan Layanan</a> kami.
            </p>
        </div>
    </div>
</body>
</html>
