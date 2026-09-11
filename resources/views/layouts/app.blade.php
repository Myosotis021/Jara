<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Jara') — {{ config('app.name', 'Jara') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen font-sans text-gray-900 antialiased">
    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <div class="flex items-center gap-6">
                    <a href="/" class="text-xl font-bold text-blue-600 tracking-tight">JARA</a>
                    <div class="hidden sm:flex items-center gap-1">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.users.index') }}"
                               class="px-3 py-2 rounded-lg text-sm font-medium transition
                                      {{ request()->routeIs('admin.users.*') ? 'text-blue-700 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                                Kelola Pengguna
                            </a>
                        @endif
                        <a href="/workspaces"
                           class="px-3 py-2 rounded-lg text-sm font-medium transition
                                  {{ request()->is('workspaces*') ? 'text-blue-700 bg-blue-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                            Workspaces
                        </a>
                    </div>
                </div>

                {{-- User Menu --}}
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600 hidden sm:inline">
                        Halo, <span class="font-semibold text-gray-800">{{ auth()->user()->name }}</span>
                    </span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="text-sm text-red-600 hover:text-red-800 font-medium px-3 py-2 rounded-lg hover:bg-red-50 transition">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash Messages --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 mt-4">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif
    </div>

    {{-- Main Content --}}
    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-6">
        @yield('content')
    </main>
</body>
</html>
