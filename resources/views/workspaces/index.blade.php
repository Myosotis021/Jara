@extends('layouts.app')

@section('title', 'Workspace Saya — JARA')

@section('subnav_title')
    Workspaces
@endsection

@section('subnav_actions')
    <a href="{{ route('workspaces.create') }}" class="apple-btn-primary-compact">
        + Buat Workspace
    </a>
@endsection

@section('content')
<div style="display:flex;flex-direction:column;gap:40px;">

    {{-- ================================================================
         HERO HEADER
         ================================================================ --}}
    <div style="padding-bottom:24px;border-bottom:1px solid #e0e0e0;">
        <span class="apple-chip"
              style="font-size:12px;padding:4px 12px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;display:inline-flex;">
            Ruang Kerja Kolaboratif
        </span>
        <h1 class="typography-display-md" style="color:#1d1d1f;margin:8px 0 8px;">
            Workspace & Daftar Tugas Saya
        </h1>
        <p class="typography-body" style="color:#7a7a7a;margin:0;max-width:560px;">
            Kelola ruang kerja proyek, tugas personal, serta delegasi tugas tim di satu platform terpadu.
        </p>
    </div>

    {{-- ================================================================
         SECTION: MY WORKSPACES
         ================================================================ --}}
    <div>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
            <h2 class="typography-tagline" style="color:#1d1d1f;margin:0;">
                Workspace Saya
            </h2>
            <span class="apple-chip" style="font-size:12px;padding:3px 10px;font-weight:600;">
                {{ $myWorkspaces->count() }}
            </span>
        </div>

        @if ($myWorkspaces->isEmpty())
            {{-- Empty state --}}
            <div class="apple-card" style="max-width:480px;margin:0 auto;text-align:center;padding:64px 32px;">
                <div style="width:64px;height:64px;border-radius:50%;background-color:#f5f5f7;border:1px solid #e0e0e0;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:#0066cc;">
                    <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="typography-tagline" style="color:#1d1d1f;margin:0 0 8px;">
                    Belum Ada Workspace Dibuat
                </h3>
                <p class="typography-body" style="color:#7a7a7a;margin:0 0 24px;max-width:320px;margin-left:auto;margin-right:auto;">
                    Kelompokkan tugas kuliah, kantor, atau proyek Anda dalam satu wadah terstruktur.
                </p>
                <a href="{{ route('workspaces.create') }}" class="apple-btn-primary">
                    + Buat Workspace Baru
                </a>
            </div>

        @else
            {{-- 3-col grid of store-utility-cards --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
                @foreach ($myWorkspaces as $workspace)
                    <a href="{{ route('workspaces.show', $workspace) }}"
                       class="apple-card"
                       style="display:flex;flex-direction:column;justify-content:space-between;transition:border-color 0.12s;text-decoration:none;color:inherit;cursor:pointer;"
                       onmouseenter="this.style.borderColor='#0071e3'"
                       onmouseleave="this.style.borderColor='#e0e0e0'">
                        <div>
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
                                <h3 class="typography-body-strong" style="color:#1d1d1f;word-break:break-word;margin:0;">
                                    {{ $workspace->name }}
                                </h3>
                            </div>
                            <p class="typography-body" style="color:#7a7a7a;margin:0 0 20px;word-break:break-word;font-size:15px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                                {{ $workspace->description ?: 'Tidak ada keterangan tambahan.' }}
                            </p>
                        </div>
                        <div>
                            <span class="apple-chip" style="font-size:12px;padding:4px 10px;font-weight:500;">
                                Pemilik: Anda
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ================================================================
         SECTION: SHARED WORKSPACES
         ================================================================ --}}
    @if ($sharedWorkspaces->isNotEmpty())
        <div style="padding-top:32px;border-top:1px solid #e0e0e0;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
                <h2 class="typography-tagline" style="color:#1d1d1f;margin:0;">
                    Dibagikan dengan Saya
                </h2>
                <span class="apple-chip" style="font-size:12px;padding:3px 10px;font-weight:600;">
                    {{ $sharedWorkspaces->count() }}
                </span>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
                @foreach ($sharedWorkspaces as $shared)
                    <a href="{{ route('workspaces.show', $shared) }}"
                       class="apple-card"
                       style="display:flex;flex-direction:column;justify-content:space-between;transition:border-color 0.12s;text-decoration:none;color:inherit;cursor:pointer;"
                       onmouseenter="this.style.borderColor='#0071e3'"
                       onmouseleave="this.style.borderColor='#e0e0e0'">
                        <div>
                            <h3 class="typography-body-strong" style="color:#1d1d1f;word-break:break-word;margin:0 0 8px;">
                                {{ $shared->name }}
                            </h3>
                            <p class="typography-body" style="color:#7a7a7a;margin:0 0 20px;word-break:break-word;font-size:15px;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                                {{ $shared->description ?: 'Tidak ada keterangan tambahan.' }}
                            </p>
                        </div>
                        <div>
                            <span class="apple-chip" style="font-size:12px;padding:4px 10px;font-weight:500;">
                                Anggota Kolaborasi
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
