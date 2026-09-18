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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vite: CSS + JS -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body style="background-color:#f5f5f7;color:#1d1d1f;min-height:100vh;display:flex;flex-direction:column;-webkit-font-smoothing:antialiased;">

    <!-- ===================================================================
         APPLE RESIZABLE NAVBAR (Native Vanilla JS + CSS Architecture)
         Transforms from full-width header to floating glass pill on scroll
         =================================================================== -->
    <header id="apple-resizable-navbar" class="apple-resizable-nav-wrapper">
        <div class="apple-resizable-nav-container">
            <!-- Brand Logo -->
            <div class="apple-nav-brand">
                <a href="{{ route('workspaces.index') }}" class="apple-nav-brand-link">
                    <span class="apple-nav-brand-badge">J</span>
                    <span class="apple-nav-brand-text">JARA</span>
                </a>
            </div>

            <!-- Desktop Navigation Links with Animated Hover Pill -->
            <nav class="apple-nav-links-desktop">
                <a href="{{ route('workspaces.index') }}"
                   class="apple-nav-link-item {{ request()->is('workspaces*') ? 'is-active' : '' }}">
                    <span>Workspaces</span>
                </a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}"
                           class="apple-nav-link-item {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
                            <span>Kelola Pengguna</span>
                        </a>
                    @endif
                @endauth
            </nav>

            <!-- Right Actions: User Profile / Auth CTA -->
            <div class="apple-nav-actions-desktop">
                @auth
                    <div class="apple-nav-user-pill">
                        <span class="apple-nav-user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="apple-nav-user-name">
                            {{ auth()->user()->name }}
                        </span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;margin:0;">
                        @csrf
                        <button type="submit" class="apple-nav-btn-secondary">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="apple-nav-btn-primary">
                        Masuk
                    </a>
                @endauth
            </div>

            <!-- Mobile Hamburger Toggle -->
            <button type="button" id="apple-mobile-menu-toggle" class="apple-mobile-toggle-btn" aria-label="Menu" aria-expanded="false">
                <svg class="icon-menu-hamburger" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg class="icon-menu-close" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Nav Menu Sheet -->
        <div id="apple-mobile-nav-sheet" class="apple-mobile-nav-sheet">
            <div class="apple-mobile-nav-links">
                <a href="{{ route('workspaces.index') }}"
                   class="apple-mobile-link {{ request()->is('workspaces*') ? 'is-active' : '' }}">
                    Workspaces
                </a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.users.index') }}"
                           class="apple-mobile-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
                            Kelola Pengguna
                        </a>
                    @endif
                @endauth
            </div>

            <div class="apple-mobile-nav-footer">
                @auth
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;padding:8px 12px;background:rgba(0,0,0,0.03);border-radius:10px;">
                        <span class="apple-nav-user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <div style="font-size:13px;font-weight:500;color:#1d1d1f;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ auth()->user()->name }}
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="apple-nav-btn-secondary" style="width:100%;justify-content:center;">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="apple-nav-btn-primary" style="width:100%;display:flex;justify-content:center;">
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
