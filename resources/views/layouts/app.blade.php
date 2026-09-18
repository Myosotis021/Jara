<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'JARA — Sistem Manajemen Workspace & Tugas')</title>
    <meta name="description" content="JARA — Platform manajemen workspace dan tugas tim yang kolaboratif, efisien, dan terpusat.">

    <!-- Google Fonts: Inter (Apple SF Pro substitute) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <!-- Vite: CSS + JS -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="background-color:#f5f5f7;color:#1d1d1f;min-height:100vh;display:flex;flex-direction:column;-webkit-font-smoothing:antialiased;">

    <!-- ===================================================================
         TOP NAV ROW 1: global-nav (44px, surface-black, nav-link 12px)
         =================================================================== -->
    <header class="apple-global-nav w-full">
        <div style="max-width:1440px;margin:0 auto;height:44px;padding:0 32px;display:flex;align-items:center;justify-content:space-between;">
            <!-- Brand + Primary Nav links -->
            <div style="display:flex;align-items:center;gap:32px;">
                <a href="{{ route('workspaces.index') }}"
                   style="font-size:13px;font-weight:600;color:#ffffff;text-decoration:none;letter-spacing:-0.1px;line-height:1;">
                    JARA
                </a>
                @auth
                    <nav style="display:flex;align-items:center;gap:24px;" class="hidden sm:flex">
                        <a href="{{ route('workspaces.index') }}"
                           style="font-size:12px;font-weight:400;letter-spacing:-0.12px;text-decoration:none;color:{{ request()->is('workspaces*') ? '#ffffff' : '#cccccc' }};transition:color 0.12s;">
                            Workspaces
                        </a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.users.index') }}"
                               style="font-size:12px;font-weight:400;letter-spacing:-0.12px;text-decoration:none;color:{{ request()->routeIs('admin.users.*') ? '#ffffff' : '#cccccc' }};transition:color 0.12s;">
                                Kelola Pengguna
                            </a>
                        @endif
                    </nav>
                @endauth
            </div>

            <!-- Right: User info + utility actions -->
            <div style="display:flex;align-items:center;gap:16px;">
                @auth
                    <span style="font-size:12px;color:#cccccc;letter-spacing:-0.12px;" class="hidden sm:inline">
                        {{ auth()->user()->name }}
                    </span>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;margin:0;">
                        @csrf
                        <button type="submit" class="apple-btn-dark">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="apple-text-link-on-dark" style="font-size:12px;">
                        Masuk
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- ===================================================================
         TOP NAV ROW 2: sub-nav-frosted (52px, parchment frosted, tagline)
         =================================================================== -->
    <nav class="apple-sub-nav-frosted w-full">
        <div style="max-width:1440px;margin:0 auto;height:52px;padding:0 32px;display:flex;align-items:center;justify-content:space-between;">
            <!-- Left: Page title / category / breadcrumb -->
            <div class="typography-tagline" style="color:#1d1d1f;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;flex:1;min-width:0;">
                @yield('subnav_title', 'Workspaces')
            </div>

            <!-- Right: Contextual utility actions -->
            <div style="display:flex;align-items:center;gap:12px;flex-shrink:0;padding-left:16px;">
                @yield('subnav_actions')
            </div>
        </div>
    </nav>

    <!-- ===================================================================
         MAIN CONTENT: low-density museum-gallery on canvas-parchment
         =================================================================== -->
    <main style="flex:1;max-width:1440px;width:100%;margin:0 auto;padding:32px 32px 48px;" class="w-full">

        {{-- Flash: success --}}
        @if (session('success'))
            <div class="apple-alert" style="margin-bottom:24px;">
                <span class="apple-alert-dot" style="background-color:#0066cc;"></span>
                <span class="typography-caption" style="color:#1d1d1f;flex:1;">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Flash: error --}}
        @if (session('error'))
            <div class="apple-alert" style="margin-bottom:24px;">
                <span class="apple-alert-dot" style="background-color:#ff3b30;"></span>
                <span class="typography-caption" style="color:#1d1d1f;flex:1;">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- ===================================================================
         FOOTER: canvas-parchment, 64px padding, dense link columns
         =================================================================== -->
    <footer class="apple-footer">
        <div style="max-width:1440px;margin:0 auto;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:32px;padding-bottom:40px;border-bottom:1px solid #e0e0e0;">

                <!-- Col 1: Navigation -->
                <div>
                    <h3 class="typography-caption-strong" style="color:#1d1d1f;margin:0 0 12px;">Navigasi JARA</h3>
                    <ul class="typography-dense-link" style="color:#333333;list-style:none;padding:0;margin:0;font-size:14px;">
                        <li><a href="{{ route('workspaces.index') }}" class="apple-text-link">Workspace Saya</a></li>
                        <li><a href="{{ route('workspaces.create') }}" class="apple-text-link">Buat Ruang Kerja Baru</a></li>
                        @auth
                            @if(auth()->user()->isAdmin())
                                <li><a href="{{ route('admin.users.index') }}" class="apple-text-link">Manajemen Pengguna</a></li>
                            @endif
                        @endauth
                    </ul>
                </div>

                <!-- Col 2: Features -->
                <div>
                    <h3 class="typography-caption-strong" style="color:#1d1d1f;margin:0 0 12px;">Kolaborasi & Tugas</h3>
                    <ul class="typography-dense-link" style="color:#333333;list-style:none;padding:0;margin:0;font-size:14px;">
                        <li>Manajemen Prioritas Tugas</li>
                        <li>Monitoring Progres Tim</li>
                        <li>Lampiran Dokumen & Berkas</li>
                    </ul>
                </div>

                <!-- Col 3: Design info -->
                <div>
                    <h3 class="typography-caption-strong" style="color:#1d1d1f;margin:0 0 12px;">Spesifikasi UI/UX</h3>
                    <ul class="typography-dense-link" style="color:#333333;list-style:none;padding:0;margin:0;font-size:14px;">
                        <li>Apple Design System</li>
                        <li>SF Pro / Inter Typography</li>
                        <li>Action Blue (#0066cc)</li>
                    </ul>
                </div>

                <!-- Col 4: Account -->
                <div>
                    <h3 class="typography-caption-strong" style="color:#1d1d1f;margin:0 0 12px;">Akun & Sesi</h3>
                    <div class="typography-fine-print" style="color:#7a7a7a;line-height:1.6;">
                        @auth
                            Masuk sebagai <strong style="color:#333333;">{{ auth()->user()->name }}</strong><br>
                            {{ auth()->user()->email }}
                        @else
                            Silakan masuk menggunakan akun terdaftar untuk mengelola tugas Anda.
                        @endauth
                    </div>
                </div>
            </div>

            <!-- Legal row -->
            <div style="padding-top:24px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;">
                <span class="typography-fine-print" style="color:#7a7a7a;">
                    Hak Cipta &copy; {{ date('Y') }} JARA. Seluruh hak cipta dilindungi undang-undang.
                </span>
                <div class="typography-fine-print" style="color:#7a7a7a;display:flex;gap:16px;">
                    <span>Privasi</span>
                    <span>&bull;</span>
                    <span>Ketentuan Penggunaan</span>
                    <span>&bull;</span>
                    <span>Peta Situs</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
