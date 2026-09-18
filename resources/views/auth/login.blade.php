<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk — JARA</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-[#f5f5f7] text-[#1d1d1f] antialiased min-h-screen flex flex-col font-sans">
    <!-- Apple global-nav (44px, surface-black) -->
    <header class="apple-global-nav w-full">
        <div class="max-w-[1440px] mx-auto h-[44px] px-4 sm:px-8 flex items-center justify-between">
            <a href="/" class="font-semibold text-white tracking-tight text-[13px] hover:text-[#cccccc] transition">
                JARA
            </a>
            <span class="text-[#cccccc] text-[12px]">
                Sistem Manajemen Workspace &amp; Tugas
            </span>
        </div>
    </header>

    <!-- Center Card Container -->
    <main class="flex-grow flex items-center justify-center px-4 py-12 sm:py-16">
        <div class="w-full max-w-[440px]">
            <!-- Store Utility Card: White, 18px radius, hairline border, no shadow -->
            <div class="apple-card p-8 sm:p-10">
                <!-- Branding & Headlines -->
                <div class="text-center mb-8">
                    <span class="typography-caption text-[#7a7a7a] uppercase tracking-wider block mb-1">
                        Autentikasi Akun
                    </span>
                    <h1 class="typography-display-md text-[#1d1d1f] tracking-tight">
                        Masuk ke JARA
                    </h1>
                    <p class="typography-caption text-[#7a7a7a] mt-2">
                        Kelola tugas, pantau progres tim, dan simpan berkas lampiran di satu tempat.
                    </p>
                </div>

                <!-- Flash Alerts -->
                @if(session('success'))
                    <div class="mb-5 p-3.5 rounded-[11px] bg-white border border-[#e0e0e0] flex items-center space-x-2.5">
                        <span class="w-2 h-2 rounded-full bg-[#0066cc] shrink-0"></span>
                        <span class="typography-caption text-[#1d1d1f]">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-3.5 rounded-[11px] bg-white border border-[#e0e0e0] flex items-center space-x-2.5">
                        <span class="w-2 h-2 rounded-full bg-[#ff3b30] shrink-0"></span>
                        <span class="typography-caption text-[#1d1d1f]">{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Input -->
                    <div>
                        <label for="email" class="block typography-caption-strong text-[#1d1d1f] mb-1.5">
                            Alamat Email
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
                            placeholder="nama@email.com"
                            class="apple-input {{ $errors->has('email') ? '!border-[#ff3b30]' : '' }}"
                        >
                        @error('email')
                            <p class="typography-caption text-[#ff3b30] mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div>
                        <label for="password" class="block typography-caption-strong text-[#1d1d1f] mb-1.5">
                            Kata Sandi
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="apple-input {{ $errors->has('password') ? '!border-[#ff3b30]' : '' }}"
                        >
                        @error('password')
                            <p class="typography-caption text-[#ff3b30] mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me Option -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                value="1"
                                class="w-4 h-4 rounded-[4px] border-[#e0e0e0] text-[#0066cc] focus:ring-[#0071e3]"
                            >
                            <span class="typography-caption text-[#333333]">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <!-- Submit Button: button-primary (Action Blue Pill, active scale 0.95) -->
                    <div class="pt-2">
                        <button type="submit" class="apple-btn-primary w-full text-center">
                            Masuk
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer Notice -->
            <div class="text-center mt-6 text-[#7a7a7a] typography-fine-print">
                Registrasi akun tertutup dan dikelola langsung oleh Administrator.
            </div>
        </div>
    </main>

    <!-- Bottom Mini-Footer -->
    <footer class="py-6 text-center text-[#7a7a7a] typography-fine-print border-t border-[#e0e0e0]">
        &copy; {{ date('Y') }} JARA. Hak Cipta Dilindungi Undang-Undang.
    </footer>
</body>
</html>
