@extends('layouts.app')

@section('title', 'Workspace Saya — JARA')

@section('subnav_title')
    Workspaces
@endsection

@section('content')
<div style="display:flex;flex-direction:column;gap:32px;">

    {{-- ================================================================
         1. HERO HEADER
         ================================================================ --}}
    <div class="apple-card" style="padding:28px;background:linear-gradient(180deg, #ffffff 0%, #fafafc 100%);">
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
            <div style="max-width:640px;">
                <span class="apple-chip"
                      style="font-size:11px;padding:3px 10px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;display:inline-flex;">
                    Ruang Kerja Kolaboratif
                </span>
                <h1 class="typography-display-md" style="color:#1d1d1f;margin:8px 0 8px;">
                    Workspace & Daftar Tugas Saya
                </h1>
                <p class="typography-body" style="color:#7a7a7a;margin:0;font-size:15px;">
                    Kelola ruang kerja proyek, tugas personal, serta delegasi tugas tim di satu platform terpadu.
                </p>
            </div>
            <div>
                <a href="{{ route('workspaces.create') }}" class="apple-btn-primary">
                    Buat Workspace Baru
                </a>
            </div>
        </div>
    </div>

    {{-- ================================================================
         2. SECTION: MY WORKSPACES
         ================================================================ --}}
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:10px;">
                <h2 class="typography-tagline" style="color:#1d1d1f;margin:0;font-size:18px;">
                    Workspace Saya
                </h2>
                <span class="apple-chip" style="font-size:11px;padding:2px 8px;font-weight:600;">
                    {{ $myWorkspaces->count() }}
                </span>
            </div>
        </div>

        @if ($myWorkspaces->isEmpty())
            {{-- Empty state --}}
            <div class="apple-card" style="max-width:480px;margin:0 auto;text-align:center;padding:56px 32px;">
                <div style="width:56px;height:56px;border-radius:50%;background-color:#f5f5f7;border:1px solid #e0e0e0;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;color:#0066cc;">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="typography-tagline" style="color:#1d1d1f;margin:0 0 8px;">
                    Belum Ada Workspace Dibuat
                </h3>
                <p class="typography-body" style="color:#7a7a7a;margin:0 0 20px;max-width:320px;margin-left:auto;margin-right:auto;font-size:14px;">
                    Kelompokkan tugas kuliah, kantor, atau proyek Anda dalam satu wadah terstruktur.
                </p>
                <a href="{{ route('workspaces.create') }}" class="apple-btn-primary">
                    Buat Workspace Baru
                </a>
            </div>

        @else
            {{-- Grid of Workspace Enterprise Cards --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
                @foreach ($myWorkspaces as $workspace)
                    <a href="{{ route('workspaces.show', $workspace) }}"
                       class="enterprise-kpi-card"
                       style="text-decoration:none;color:inherit;cursor:pointer;padding:20px;min-height:140px;"
                       onmouseenter="this.style.borderColor='#0071e3'"
                       onmouseleave="this.style.borderColor='#e0e0e0'">
                        <div>
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
                                <h3 class="typography-body-strong" style="color:#1d1d1f;word-break:break-word;margin:0;font-size:16px;">
                                    {{ $workspace->name }}
                                </h3>
                                <svg width="16" height="16" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24" style="flex-shrink:0;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                            <p class="typography-body" style="color:#7a7a7a;margin:0 0 16px;word-break:break-word;font-size:14px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.4;">
                                {{ $workspace->description ?: 'Tidak ada keterangan tambahan.' }}
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid #f5f5f7;">
                            <span class="apple-chip" style="font-size:11px;padding:3px 8px;font-weight:600;color:#0066cc;border-color:rgba(0,102,204,0.2);">
                                Pemilik: Anda
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ================================================================
         4. SECTION: SHARED WORKSPACES
         ================================================================ --}}
    @if ($sharedWorkspaces->isNotEmpty())
        <div style="padding-top:24px;border-top:1px solid #e0e0e0;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                <h2 class="typography-tagline" style="color:#1d1d1f;margin:0;font-size:18px;">
                    Dibagikan dengan Saya
                </h2>
                <span class="apple-chip" style="font-size:11px;padding:2px 8px;font-weight:600;">
                    {{ $sharedWorkspaces->count() }}
                </span>
            </div>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
                @foreach ($sharedWorkspaces as $shared)
                    <a href="{{ route('workspaces.show', $shared) }}"
                       class="enterprise-kpi-card"
                       style="text-decoration:none;color:inherit;cursor:pointer;padding:20px;min-height:140px;"
                       onmouseenter="this.style.borderColor='#0071e3'"
                       onmouseleave="this.style.borderColor='#e0e0e0'">
                        <div>
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
                                <h3 class="typography-body-strong" style="color:#1d1d1f;word-break:break-word;margin:0;font-size:16px;">
                                    {{ $shared->name }}
                                </h3>
                                <svg width="16" height="16" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24" style="flex-shrink:0;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                            <p class="typography-body" style="color:#7a7a7a;margin:0 0 16px;word-break:break-word;font-size:14px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;line-height:1.4;">
                                {{ $shared->description ?: 'Tidak ada keterangan tambahan.' }}
                            </p>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;padding-top:12px;border-top:1px solid #f5f5f7;">
                            <span class="apple-chip" style="font-size:11px;padding:3px 8px;font-weight:500;">
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
