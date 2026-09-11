<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Tailwind CDN fallback & Vite assets -->
    <script src="https://cdn.tailwindcss.com"></script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4 font-sans antialiased">
    <div class="w-full max-w-md">
        {{-- Card --}}
        <div class="bg-white rounded-xl shadow-md border border-gray-100 p-8">
            {{-- Branding --}}
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-blue-600 tracking-tight">JARA</h1>
                <p class="text-sm text-gray-500 mt-1">Sistem Manajemen & Upload Tugas Tim/Pribadi</p>
            </div>

            {{-- Flash Alert --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-2.5 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5 rounded-lg mb-4">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                        Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        maxlength="255"
                        autocomplete="email"
                        autofocus
                        class="w-full px-3 py-2 border rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-blue-500 transition
                               {{ $errors->has('email') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}"
                        placeholder="user@example.com"
                    >
                    @error('email')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label for="password" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                        Kata Sandi
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full px-3 py-2 border rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-blue-500 transition
                               {{ $errors->has('password') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="mb-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" value="1"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-600">Ingat Saya</span>
                    </label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-lg transition duration-150 ease-in-out shadow-sm cursor-pointer">
                    Masuk
                </button>
            </form>
        </div>
    </div>
</body>
</html>
