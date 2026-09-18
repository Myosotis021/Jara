@extends('layouts.app')

@section('title', $workspace->name . ' — JARA')

@section('subnav_title')
    <div style="display:flex;align-items:center;gap:12px;overflow:hidden;">
        <a href="{{ route('workspaces.index') }}"
           style="font-size:14px;color:#7a7a7a;text-decoration:none;white-space:nowrap;flex-shrink:0;transition:color 0.12s;">
            &larr; Workspaces
        </a>
        <span style="color:#e0e0e0;flex-shrink:0;">/</span>
        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600;color:#1d1d1f;">{{ $workspace->name }}</span>
    </div>
@endsection

@section('content')
<div style="display:flex;flex-direction:column;gap:24px;">

    {{-- ================================================================
         1. WORKSPACE ENTERPRISE HERO HEADER
         ================================================================ --}}
    <div class="apple-card" style="padding:24px 28px;background:linear-gradient(180deg, #ffffff 0%, #fafafc 100%);">
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
            <div style="flex:1;min-width:280px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <span class="apple-chip" style="font-size:11px;padding:3px 10px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;">
                        Ruang Kerja
                    </span>
                    <span style="font-size:12px;color:#7a7a7a;">
                        &bull; Dibuat oleh <strong>{{ $workspace->user_id === auth()->id() ? 'Anda' : ($workspace->owner->name ?? 'Pengguna Lain') }}</strong>
                    </span>
                </div>
                <h1 class="typography-display-md" style="color:#1d1d1f;margin:0 0 6px;word-break:break-word;">
                    {{ $workspace->name }}
                </h1>
                <p class="typography-body" style="color:#7a7a7a;margin:0;word-break:break-word;font-size:15px;max-width:720px;">
                    {{ $workspace->description ?: 'Tidak ada keterangan deskripsi tambahan untuk workspace ini.' }}
                </p>
            </div>
            <div style="display:flex;align-items:center;gap:12px;flex-shrink:0;">
                <div style="display:flex;align-items:center;padding:8px 14px;background-color:#ffffff;border:1px solid #e0e0e0;border-radius:10px;">
                    <span class="avatar-initials" style="width:26px;height:26px;font-size:11px;margin-right:8px;">
                        {{ strtoupper(substr($workspace->owner->name ?? 'U', 0, 2)) }}
                    </span>
                    <div style="font-size:12px;">
                        <span style="color:#7a7a7a;display:block;line-height:1.2;">Pemilik</span>
                        <strong style="color:#1d1d1f;">{{ $workspace->user_id === auth()->id() ? 'Anda' : ($workspace->owner->name ?? 'Pengguna') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================================================================
         2. EXECUTIVE TELEMETRY KPI METRICS ROW
         ================================================================ --}}
    <div class="enterprise-kpi-grid">
        {{-- KPI 1: Total Tasks --}}
        <div class="enterprise-kpi-card">
            <span style="font-size:11px;font-weight:600;color:#7a7a7a;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;display:block;">
                Total Tugas
            </span>
            <div style="display:flex;align-items:baseline;justify-content:space-between;">
                <span style="font-size:28px;font-weight:700;color:#1d1d1f;line-height:1;">
                    {{ $totalTasks }}
                </span>
                <span style="font-size:12px;color:#7a7a7a;">Item</span>
            </div>
        </div>

        {{-- KPI 2: Completed Tasks --}}
        <div class="enterprise-kpi-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <span style="font-size:11px;font-weight:600;color:#0066cc;text-transform:uppercase;letter-spacing:0.05em;">
                    Selesai
                </span>
                <span style="font-size:12px;font-weight:600;color:#0066cc;">
                    {{ $progressPercentage }}%
                </span>
            </div>
            <div style="display:flex;align-items:baseline;justify-content:space-between;">
                <span style="font-size:28px;font-weight:700;color:#0066cc;line-height:1;">
                    {{ $completedTasks }}
                </span>
                <span style="font-size:12px;color:#7a7a7a;">dari {{ $totalTasks }}</span>
            </div>
        </div>

        {{-- KPI 3: In Progress / Belum Selesai --}}
        <div class="enterprise-kpi-card">
            <span style="font-size:11px;font-weight:600;color:#7a7a7a;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;display:block;">
                Belum Selesai
            </span>
            <div style="display:flex;align-items:baseline;justify-content:space-between;">
                <span style="font-size:28px;font-weight:700;color:#1d1d1f;line-height:1;">
                    {{ $totalTasks - $completedTasks }}
                </span>
                <span style="font-size:12px;color:#7a7a7a;">Aktif</span>
            </div>
        </div>

        {{-- KPI 4: Penting --}}
        <div class="enterprise-kpi-card">
            <span style="font-size:11px;font-weight:600;color:#ff3b30;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;display:block;">
                Prioritas Penting
            </span>
            <div style="display:flex;align-items:baseline;justify-content:space-between;">
                <span style="font-size:28px;font-weight:700;color:#ff3b30;line-height:1;">
                    {{ $pentingTasks }}
                </span>
                <span style="font-size:12px;color:#ff3b30;font-weight:500;">Perhatian</span>
            </div>
        </div>
    </div>

    {{-- ================================================================
         3. ASYMMETRIC 2-COLUMN ENTERPRISE WORKSTATION LAYOUT
         ================================================================ --}}
    <div class="enterprise-layout-grid">

        {{-- ============================================================
             LEFT COLUMN: PRIMARY TASK LIST (~70%)
             ============================================================ --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- 3.1 DAFTAR TUGAS HEADER & ACTIONS --}}
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;padding-bottom:12px;border-bottom:1px solid #e0e0e0;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <h2 class="typography-tagline" style="color:#1d1d1f;margin:0;font-size:18px;">
                        Daftar Tugas
                    </h2>
                    <span class="apple-chip" style="font-size:11px;padding:2px 8px;font-weight:600;">
                        {{ $tasks->count() }}
                    </span>
                </div>

                {{-- Filter & Action buttons --}}
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                    {{-- Filter segmented chips --}}
                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;">
                        <a href="{{ route('workspaces.show', $workspace) }}"
                           class="apple-chip {{ empty($status) ? 'apple-chip-selected' : '' }}"
                           style="font-size:12px;padding:5px 12px;">
                            Semua ({{ $totalTasks }})
                        </a>
                        <a href="{{ route('workspaces.show', [$workspace, 'status' => 'active']) }}"
                           class="apple-chip {{ $status === 'active' ? 'apple-chip-selected' : '' }}"
                           style="font-size:12px;padding:5px 12px;">
                            Belum Selesai ({{ $totalTasks - $completedTasks }})
                        </a>
                        <a href="{{ route('workspaces.show', [$workspace, 'status' => 'completed']) }}"
                           class="apple-chip {{ $status === 'completed' ? 'apple-chip-selected' : '' }}"
                           style="font-size:12px;padding:5px 12px;">
                            Selesai ({{ $completedTasks }})
                        </a>
                        <a href="{{ route('workspaces.show', [$workspace, 'status' => 'penting']) }}"
                           class="apple-chip {{ $status === 'penting' ? 'apple-chip-selected' : '' }}"
                           style="font-size:12px;padding:5px 12px;">
                            Penting ({{ $pentingTasks }})
                        </a>
                    </div>

                    <button type="button" class="apple-btn-primary-compact open-task-modal-btn" style="height:32px;padding:0 14px;font-size:12px;">
                        + Tambah Tugas
                    </button>
                </div>
            </div>

            {{-- Empty State --}}
            @if ($tasks->isEmpty())
                <div class="apple-card" style="text-align:center;padding:56px 24px;">
                    <div style="width:52px;height:52px;border-radius:50%;background-color:#f5f5f7;border:1px solid #e0e0e0;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;color:#0066cc;">
                        <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <h4 class="typography-body-strong" style="color:#1d1d1f;margin:0 0 4px;">
                        Belum Ada Tugas di Workspace Ini
                    </h4>
                    <p class="typography-caption" style="color:#7a7a7a;margin:0 0 16px;">
                        Kelola tugas proyek Anda dengan menekan tombol di bawah.
                    </p>
                    <button type="button" class="apple-btn-primary open-task-modal-btn">
                        + Tambah Tugas Baru
                    </button>
                </div>

            @else
                {{-- Task Cards List --}}
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach ($tasks as $task)
                        <div class="enterprise-task-card {{ $task->is_completed ? 'is-completed' : '' }}">
                            {{-- Main Task Row --}}
                            <div style="display:flex;align-items:flex-start;gap:16px;">
                                {{-- Status Toggle Button --}}
                                <form action="{{ route('workspaces.tasks.toggle-status', [$workspace, $task]) }}"
                                      method="POST" style="margin:0;flex-shrink:0;padding-top:2px;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            title="{{ $task->is_completed ? 'Tandai belum selesai' : 'Tandai selesai' }}"
                                            style="width:24px;height:24px;border-radius:50%;border:{{ $task->is_completed ? '2px solid #0066cc' : '1.5px solid #d2d2d7' }};background-color:{{ $task->is_completed ? '#0066cc' : '#ffffff' }};color:{{ $task->is_completed ? '#ffffff' : 'transparent' }};display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.12s;flex-shrink:0;">
                                        @if ($task->is_completed)
                                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </button>
                                </form>

                                {{-- Task Info --}}
                                <div style="flex:1;min-width:0;">
                                    <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:4px;">
                                        @if ($task->priority === 'penting')
                                            <span class="apple-chip"
                                                  style="font-size:11px;padding:2px 8px;font-weight:600;color:#ff3b30;border-color:rgba(255,59,48,0.3);">
                                                PENTING
                                            </span>
                                        @else
                                            <span class="apple-chip"
                                                  style="font-size:11px;padding:2px 8px;font-weight:500;color:#7a7a7a;">
                                                MENYUSUL
                                            </span>
                                        @endif

                                        <span class="typography-body-strong"
                                              style="{{ $task->is_completed ? 'text-decoration:line-through;color:#7a7a7a;' : 'color:#1d1d1f;' }}word-break:break-word;">
                                            {{ $task->title }}
                                        </span>
                                    </div>

                                    {{-- Description --}}
                                    @if ($task->description)
                                        <p class="typography-caption" style="color:#7a7a7a;margin:0 0 8px;word-break:break-word;">
                                            {!! nl2br(e($task->description)) !!}
                                        </p>
                                    @endif

                                    {{-- Meta row --}}
                                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;font-size:12px;color:#7a7a7a;">
                                        @if ($task->due_date)
                                            <span style="{{ !$task->is_completed && $task->due_date->isPast() ? 'color:#ff3b30;font-weight:600;' : '' }}">
                                                Tenggat: {{ $task->due_date->format('d M Y H:i') }}
                                                @if (!$task->is_completed && $task->due_date->isPast())
                                                    <span>(Terlewat)</span>
                                                @endif
                                            </span>
                                            <span>&bull;</span>
                                        @endif

                                        @if ($task->is_completed && $task->completed_at)
                                            <span style="color:#0066cc;">
                                                Selesai: {{ $task->completed_at->format('d M Y H:i') }}
                                            </span>
                                            <span>&bull;</span>
                                        @endif

                                        <span>Oleh: {{ $task->creator->name ?? 'Pengguna' }}</span>
                                    </div>
                                </div>

                                {{-- Actions: Edit & Hapus --}}
                                <div style="display:flex;align-items:center;gap:12px;flex-shrink:0;align-self:flex-start;" class="typography-caption">
                                    <a href="{{ route('workspaces.tasks.edit', [$workspace, $task]) }}"
                                       class="apple-text-link open-task-edit-modal-btn"
                                       data-task-id="{{ $task->id }}"
                                       data-task-title="{{ $task->title }}"
                                       data-task-description="{{ $task->description ?? '' }}"
                                       data-task-priority="{{ $task->priority }}"
                                       data-task-due-date="{{ $task->due_date ? $task->due_date->format('Y-m-d\TH:i') : '' }}"
                                       data-task-action="{{ route('workspaces.tasks.update', [$workspace, $task]) }}">
                                        Edit
                                    </a>
                                    <form action="{{ route('workspaces.tasks.destroy', [$workspace, $task]) }}"
                                          method="POST"
                                          style="display:inline;margin:0;"
                                          data-confirm="Apakah Anda yakin ingin menghapus tugas ini? Seluruh berkas lampirannya juga akan ikut terhapus permanen."
                                          data-confirm-title="Hapus Tugas"
                                          data-confirm-btn="Hapus Tugas">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                style="color:#ff3b30;background:none;border:none;padding:0;cursor:pointer;font-family:inherit;font-size:14px;font-weight:400;">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Attachments Container --}}
                            <div style="margin-top:14px;padding-top:14px;border-top:1px solid #f0f0f0;">
                                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                                    <span class="typography-caption-strong" style="color:#1d1d1f;display:flex;align-items:center;gap:6px;font-size:13px;">
                                        <svg width="14" height="14" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        Berkas Lampiran
                                        <span style="color:#7a7a7a;font-weight:400;">({{ $task->attachments->count() }})</span>
                                    </span>
                                </div>

                                @if ($task->attachments->isNotEmpty())
                                    <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:10px;">
                                        @foreach ($task->attachments as $attachment)
                                            <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:10px;background-color:#fafafc;border:1px solid #e0e0e0;gap:12px;flex-wrap:wrap;"
                                                 class="typography-caption">
                                                <div style="display:flex;align-items:center;gap:8px;overflow:hidden;flex:1;min-width:0;">
                                                    @php $ext = strtoupper(pathinfo($attachment->original_name, PATHINFO_EXTENSION)); @endphp
                                                    <span class="apple-chip" style="font-size:10px;padding:2px 6px;font-weight:700;flex-shrink:0;">
                                                        {{ $ext }}
                                                    </span>
                                                    <div style="overflow:hidden;min-width:0;">
                                                        <a href="{{ route('workspaces.tasks.attachments.download', [$workspace->id, $task->id, $attachment->id]) }}"
                                                           class="apple-text-link"
                                                           style="font-weight:500;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                            {{ $attachment->original_name }}
                                                        </a>
                                                        <div style="font-size:11px;color:#7a7a7a;white-space:nowrap;">
                                                            {{ $attachment->formatted_size }} &bull; {{ $attachment->uploader->name ?? 'Pengguna' }} &bull; {{ $attachment->created_at->format('d M Y H:i') }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
                                                    <a href="{{ route('workspaces.tasks.attachments.download', [$workspace->id, $task->id, $attachment->id]) }}"
                                                       class="apple-text-link" style="font-weight:500;">
                                                        Unduh
                                                    </a>
                                                    @if ($attachment->user_id === auth()->id() || $workspace->user_id === auth()->id())
                                                        <form action="{{ route('workspaces.tasks.attachments.destroy', [$workspace->id, $task->id, $attachment->id]) }}"
                                                              method="POST"
                                                              style="display:inline;margin:0;"
                                                              data-confirm="Apakah Anda yakin ingin menghapus berkas lampiran ini?"
                                                              data-confirm-title="Hapus Berkas Lampiran"
                                                              data-confirm-btn="Hapus Berkas">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    style="color:#ff3b30;background:none;border:none;padding:0;cursor:pointer;font-family:inherit;font-size:13px;font-weight:400;">
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="typography-caption" style="color:#7a7a7a;font-style:italic;margin:0 0 10px;font-size:13px;">
                                        Belum ada berkas lampiran untuk tugas ini.
                                    </p>
                                @endif

                                {{-- Custom Upload Form --}}
                                <form action="{{ route('workspaces.tasks.attachments.store', [$workspace->id, $task->id]) }}"
                                      method="POST"
                                      enctype="multipart/form-data"
                                      style="padding:12px;background-color:#fafafc;border-radius:10px;border:1px solid #e0e0e0;">
                                    @csrf
                                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;">
                                        <label for="attachment-{{ $task->id }}" class="apple-btn-secondary-compact" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;margin:0;white-space:nowrap;font-size:12px;padding:5px 12px;">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Pilih Berkas
                                        </label>
                                        <span id="file-name-{{ $task->id }}" class="typography-caption" style="color:#7a7a7a;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;">
                                            Belum ada berkas dipilih
                                        </span>
                                        <input type="file"
                                               id="attachment-{{ $task->id }}"
                                               name="attachment"
                                               required
                                               accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png"
                                               style="display:none;"
                                               onchange="document.getElementById('file-name-{{ $task->id }}').textContent = this.files[0] ? this.files[0].name : 'Belum ada berkas dipilih'">
                                        <button type="submit" class="apple-btn-primary-compact" style="flex-shrink:0;font-size:12px;padding:5px 14px;">
                                            Unggah
                                        </button>
                                    </div>
                                    @error('attachment')
                                        <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                                    @enderror
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        {{-- ============================================================
             RIGHT COLUMN: CONTEXT & COLLABORATION SIDEBAR (~30%)
             ============================================================ --}}
        <div style="display:flex;flex-direction:column;gap:20px;">

            {{-- 3.2 PROGRES TUGAS CARD --}}
            <div class="enterprise-sidebar-card">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <h2 class="typography-caption-strong" style="color:#7a7a7a;text-transform:uppercase;letter-spacing:0.06em;margin:0;font-size:12px;">
                        PROGRES TUGAS WORKSPACE
                    </h2>
                    <span class="typography-body-strong" style="color:#0066cc;font-size:15px;">
                        {{ $progressPercentage }}%
                    </span>
                </div>

                {{-- Progress Bar --}}
                <div style="width:100%;background-color:#f0f0f0;border-radius:9999px;height:8px;overflow:hidden;margin-bottom:10px;">
                    <div style="background-color:#0066cc;height:8px;border-radius:9999px;width:{{ $progressPercentage }}%;transition:width 0.3s ease;"></div>
                </div>

                <div class="typography-caption" style="color:#7a7a7a;font-size:13px;display:flex;align-items:center;justify-content:space-between;">
                    <span>Status Penyelesaian:</span>
                    <strong style="color:#1d1d1f;">({{ $completedTasks }} dari {{ $totalTasks }} selesai)</strong>
                </div>
            </div>

            {{-- 3.3 ANGGOTA TIM KOLABORASI CARD --}}
            <div class="enterprise-sidebar-card">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <h3 class="typography-tagline" style="color:#1d1d1f;margin:0;font-size:16px;">
                        Anggota Tim ({{ 1 + $workspace->members->count() }})
                    </h3>
                    <span class="apple-chip" style="font-size:11px;padding:2px 8px;font-weight:600;">
                        Kolaborator
                    </span>
                </div>

                {{-- Invite Form (Owner Only) --}}
                @if (auth()->id() === $workspace->user_id)
                    <form action="{{ route('workspaces.members.store', $workspace->id) }}" method="POST"
                          id="invite-member-form"
                          style="display:flex;flex-direction:column;gap:12px;margin-bottom:16px;padding:14px;background-color:#fafafc;border:1px solid #e0e0e0;border-radius:12px;">
                        @csrf
                        <div>
                            <label class="typography-caption-strong" style="color:#1d1d1f;display:block;font-size:12px;margin-bottom:6px;">
                                Tambah Anggota Tim
                            </label>

                            {{-- Apple Searchable Member Combobox with Live Database Search --}}
                            <div class="apple-member-search-container"
                                 id="apple-member-combobox"
                                 data-search-url="{{ route('workspaces.members.search', $workspace->id) }}">
                                <input type="hidden" name="user_id" id="invite_user_id" value="{{ old('user_id') }}">
                                <input type="hidden" name="email" id="invite_user_email" value="{{ old('email') }}">

                                {{-- Trigger Button --}}
                                <div class="apple-member-trigger" id="apple-member-trigger" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false">
                                    <div style="display:flex;align-items:center;gap:8px;overflow:hidden;flex:1;min-width:0;">
                                        <svg width="15" height="15" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24" style="flex-shrink:0;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m16-10a4 4 0 11-8 0 4 4 0 018 0zM20 8v6M23 11h-6"/>
                                        </svg>
                                        <span class="apple-member-selected-label" id="apple-member-label" style="font-size:13px;color:#7a7a7a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            Cari nama atau email pengguna…
                                        </span>
                                    </div>
                                    <svg class="apple-member-arrow" width="14" height="14" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24" style="flex-shrink:0;transition:transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>

                                {{-- Smooth Animated Dropdown Menu with Live Database Search --}}
                                <div class="apple-member-menu" id="apple-member-menu" role="listbox">
                                    {{-- Search Box inside Dropdown --}}
                                    <div class="apple-member-search-box">
                                        <svg width="14" height="14" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24" style="flex-shrink:0;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                        <input type="text"
                                               class="apple-member-search-input"
                                               id="apple-member-search-input"
                                               placeholder="Ketik email atau nama di database…"
                                               autocomplete="off"
                                               spellcheck="false">
                                        <button type="button" class="apple-member-clear-btn" id="apple-member-clear-btn" style="display:none;" title="Bersihkan">×</button>
                                    </div>

                                    {{-- Options List --}}
                                    <div class="apple-member-options-list" id="apple-member-options-list">
                                        @forelse ($availableUsers as $candidate)
                                            <div class="apple-member-option"
                                                 data-id="{{ $candidate->id }}"
                                                 data-name="{{ $candidate->name }}"
                                                 data-email="{{ $candidate->email }}">
                                                <span class="avatar-initials" style="width:24px;height:24px;font-size:10px;flex-shrink:0;">
                                                    {{ strtoupper(substr($candidate->name, 0, 2)) }}
                                                </span>
                                                <div style="overflow:hidden;flex:1;min-width:0;">
                                                    <div class="apple-member-opt-name" style="font-size:13px;font-weight:600;color:#1d1d1f;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                        {{ $candidate->name }}
                                                    </div>
                                                    <div class="apple-member-opt-email" style="font-size:11px;color:#7a7a7a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                                        {{ $candidate->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="apple-member-empty">
                                                Belum ada calon anggota lain yang tersedia.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        @error('user_id')
                            <p class="typography-caption" style="color:#ff3b30;margin:0;font-size:12px;">{{ $message }}</p>
                        @enderror
                        @error('email')
                            <p class="typography-caption" style="color:#ff3b30;margin:0;font-size:12px;">{{ $message }}</p>
                        @enderror

                        <button type="submit" id="invite-submit-btn" class="apple-btn-primary-compact" style="height:36px;width:100%;font-size:12px;">
                            Undang ke Workspace
                        </button>
                    </form>
                @endif

                {{-- Member Compact List --}}
                <div style="display:flex;flex-direction:column;gap:10px;">
                    {{-- Owner Item --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f0f0;">
                        <div style="display:flex;align-items:center;gap:10px;overflow:hidden;flex:1;min-width:0;">
                            <span class="avatar-initials">
                                {{ strtoupper(substr($workspace->owner->name ?? 'P', 0, 2)) }}
                            </span>
                            <div style="overflow:hidden;min-width:0;">
                                <span style="font-size:13px;font-weight:600;color:#1d1d1f;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $workspace->owner->name ?? 'Pemilik' }}
                                </span>
                                <span style="font-size:11px;color:#7a7a7a;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    {{ $workspace->owner->email ?? '—' }}
                                </span>
                            </div>
                        </div>
                        <span class="apple-chip" style="font-size:10px;padding:2px 6px;font-weight:600;color:#0066cc;border-color:#0066cc;flex-shrink:0;">
                            PEMILIK
                        </span>
                    </div>

                    {{-- Members Items --}}
                    @foreach ($workspace->members as $member)
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f0f0;">
                            <div style="display:flex;align-items:center;gap:10px;overflow:hidden;flex:1;min-width:0;">
                                <span class="avatar-initials" style="background-color:#f5f5f7;color:#7a7a7a;border-color:#e0e0e0;">
                                    {{ strtoupper(substr($member->name ?? 'A', 0, 2)) }}
                                </span>
                                <div style="overflow:hidden;min-width:0;">
                                    <span style="font-size:13px;font-weight:500;color:#1d1d1f;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        {{ $member->name }}
                                    </span>
                                    <span style="font-size:11px;color:#7a7a7a;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                        {{ $member->email }}
                                    </span>
                                </div>
                            </div>

                            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                                <span class="apple-chip" style="font-size:10px;padding:2px 6px;font-weight:500;color:#7a7a7a;">
                                    ANGGOTA
                                </span>
                                @if (auth()->id() === $workspace->user_id)
                                    <form action="{{ route('workspaces.members.destroy', [$workspace->id, $member->id]) }}"
                                          method="POST"
                                          style="display:inline;margin:0;"
                                          data-confirm="Apakah Anda yakin ingin mengeluarkan anggota ini dari workspace?"
                                          data-confirm-title="Keluarkan Anggota"
                                          data-confirm-btn="Keluarkan">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                title="Keluarkan anggota"
                                                style="color:#ff3b30;background:none;border:none;padding:2px 4px;cursor:pointer;font-family:inherit;font-size:12px;line-height:1;">
                                            ✕
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 3.4 WORKSPACE INFO & ACTIONS CARD --}}
            <div class="enterprise-sidebar-card">
                <h3 class="typography-caption-strong" style="color:#7a7a7a;text-transform:uppercase;letter-spacing:0.06em;margin:0 0 12px;font-size:12px;">
                    Informasi Workspace
                </h3>
                <div style="display:flex;flex-direction:column;gap:8px;font-size:13px;color:#7a7a7a;">
                    <div style="display:flex;justify-content:space-between;">
                        <span>Dibuat pada:</span>
                        <strong style="color:#1d1d1f;">{{ $workspace->created_at->format('d M Y') }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span>Peran Anda:</span>
                        <strong style="color:#0066cc;">{{ $workspace->user_id === auth()->id() ? 'Pemilik' : 'Anggota Tim' }}</strong>
                    </div>
                    @if(auth()->id() === $workspace->user_id)
                        <div style="padding-top:8px;border-top:1px solid #f0f0f0;margin-top:4px;">
                            <a href="{{ route('workspaces.edit', $workspace) }}"
                               class="apple-btn-secondary-compact"
                               style="display:block;text-align:center;text-decoration:none;font-size:12px;padding:6px 0;">
                                Edit Pengaturan Workspace
                            </a>
                        </div>
                    @endif
                </div>
            </div>

        </div>

    </div>

</div>

{{-- ================================================================
     4. MODAL POPUP WINDOW: TAMBAH TUGAS BARU
     ================================================================ --}}
<div id="task-create-modal"
     class="apple-modal-backdrop {{ (old('_method') !== 'PUT' && ($errors->has('title') || $errors->has('description') || $errors->has('priority') || $errors->has('due_date'))) ? 'is-open' : '' }}"
     role="dialog"
     aria-modal="true"
     aria-labelledby="task-modal-title">

    <div class="apple-modal-stage" id="task-modal-stage">
        <div class="apple-modal-window-lg" id="task-modal-card">
        {{-- Modal Header --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #f0f0f0;">
            <div>
                <span class="apple-chip" style="font-size:11px;padding:3px 10px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;display:inline-flex;">
                    Tugas Baru
                </span>
                <h2 id="task-modal-title" class="typography-tagline" style="color:#1d1d1f;margin:0 0 4px;font-size:20px;">
                    Tambah Tugas Baru
                </h2>
                <p class="typography-caption" style="color:#7a7a7a;margin:0;">
                    Tuliskan tugas baru untuk ruang kerja <strong>{{ $workspace->name }}</strong>.
                </p>
            </div>
            <button type="button"
                    class="close-task-modal-btn"
                    style="background:none;border:none;color:#7a7a7a;font-size:20px;cursor:pointer;padding:4px 8px;line-height:1;border-radius:50%;transition:background-color 0.12s;"
                    title="Tutup">
                ✕
            </button>
        </div>

        {{-- Modal Form --}}
        <form action="{{ route('workspaces.tasks.store', $workspace) }}" method="POST"
              style="display:flex;flex-direction:column;gap:16px;">
            @csrf

            {{-- Judul --}}
            <div>
                <label for="title" class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                    Judul Tugas <span style="color:#ff3b30;">*</span>
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('_method') !== 'PUT' ? old('title') : '' }}"
                    required
                    placeholder="Tulis apa yang perlu dikerjakan…"
                    class="apple-input"
                    style="{{ (old('_method') !== 'PUT' && $errors->has('title')) ? 'border-color:#ff3b30;' : '' }}"
                >
                @if (old('_method') !== 'PUT')
                    @error('title')
                        <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            {{-- Catatan --}}
            <div>
                <label for="description" class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                    Catatan atau Detail Tugas <span style="color:#7a7a7a;font-weight:400;">(Opsional)</span>
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    placeholder="Tambahkan catatan, instruksi, atau rincian tugas…"
                    class="apple-input-box"
                    style="{{ (old('_method') !== 'PUT' && $errors->has('description')) ? 'border-color:#ff3b30;' : '' }}"
                >{{ old('_method') !== 'PUT' ? old('description') : '' }}</textarea>
                @if (old('_method') !== 'PUT')
                    @error('description')
                        <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            {{-- Prioritas & Tenggat Waktu (2-Col Grid) --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                {{-- Prioritas --}}
                <div>
                    <label for="priority" class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                        Prioritas
                    </label>
                    <select
                        id="priority"
                        name="priority"
                        required
                        class="apple-custom-select apple-input"
                        style="height:44px;{{ (old('_method') !== 'PUT' && $errors->has('priority')) ? 'border-color:#ff3b30;' : '' }}"
                    >
                        <option value="menyusul" {{ (old('_method') !== 'PUT' && old('priority', 'menyusul') === 'menyusul') ? 'selected' : '' }}>Menyusul</option>
                        <option value="penting"  {{ (old('_method') !== 'PUT' && old('priority') === 'penting')  ? 'selected' : '' }}>Penting</option>
                    </select>
                </div>

                {{-- Tenggat Waktu --}}
                <div>
                    <label class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                        Tenggat Waktu
                    </label>
                    <input
                        type="datetime-local"
                        id="due_date"
                        name="due_date"
                        value="{{ old('_method') !== 'PUT' ? old('due_date') : '' }}"
                        class="apple-datetime-input"
                        style="{{ (old('_method') !== 'PUT' && $errors->has('due_date')) ? 'border-color:#ff3b30;' : '' }}"
                    >
                    @if (old('_method') !== 'PUT')
                        @error('due_date')
                            <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            </div>

            {{-- Modal Footer Actions --}}
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px;margin-top:8px;padding-top:16px;border-top:1px solid #f0f0f0;">
                <button type="button" class="apple-btn-secondary-compact close-task-modal-btn">
                    Batal
                </button>
                <button type="submit" class="apple-btn-primary">
                    + Tambah Tugas
                </button>
            </div>
        </form>
    </div>

    {{-- Side Calendar Companion Panel --}}
    <div class="apple-modal-calendar-panel" id="task-modal-calendar-panel"></div>
</div>
</div>

{{-- ================================================================
     5. MODAL POPUP WINDOW: EDIT TUGAS
     ================================================================ --}}
<div id="task-edit-modal"
     class="apple-modal-backdrop {{ (old('_method') === 'PUT' && ($errors->has('title') || $errors->has('description') || $errors->has('priority') || $errors->has('due_date'))) ? 'is-open' : '' }}"
     role="dialog"
     aria-modal="true"
     aria-labelledby="task-edit-modal-title">

    <div class="apple-modal-stage" id="task-edit-modal-stage">
        <div class="apple-modal-window-lg" id="task-edit-modal-card">
        {{-- Modal Header --}}
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #f0f0f0;">
            <div>
                <span class="apple-chip" style="font-size:11px;padding:3px 10px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;display:inline-flex;">
                    Edit Tugas
                </span>
                <h2 id="task-edit-modal-title" class="typography-tagline" style="color:#1d1d1f;margin:0 0 4px;font-size:20px;">
                    Edit Tugas
                </h2>
                <p class="typography-caption" style="color:#7a7a7a;margin:0;">
                    Perbarui informasi tugas pada ruang kerja <strong>{{ $workspace->name }}</strong>.
                </p>
            </div>
            <button type="button"
                    class="close-task-edit-modal-btn"
                    style="background:none;border:none;color:#7a7a7a;font-size:20px;cursor:pointer;padding:4px 8px;line-height:1;border-radius:50%;transition:background-color 0.12s;"
                    title="Tutup">
                ✕
            </button>
        </div>

        {{-- Modal Form --}}
        <form id="task-edit-modal-form"
              action="{{ (old('_method') === 'PUT' && old('task_id')) ? route('workspaces.tasks.update', [$workspace, old('task_id')]) : '' }}"
              method="POST"
              style="display:flex;flex-direction:column;gap:16px;">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_task_id" name="task_id" value="{{ old('_method') === 'PUT' ? old('task_id') : '' }}">

            {{-- Judul --}}
            <div>
                <label for="edit_title" class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                    Judul Tugas <span style="color:#ff3b30;">*</span>
                </label>
                <input
                    type="text"
                    id="edit_title"
                    name="title"
                    value="{{ old('_method') === 'PUT' ? old('title') : '' }}"
                    required
                    placeholder="Tulis apa yang perlu dikerjakan…"
                    class="apple-input"
                    style="{{ (old('_method') === 'PUT' && $errors->has('title')) ? 'border-color:#ff3b30;' : '' }}"
                >
                @if (old('_method') === 'PUT')
                    @error('title')
                        <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            {{-- Catatan --}}
            <div>
                <label for="edit_description" class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                    Catatan atau Detail Tugas <span style="color:#7a7a7a;font-weight:400;">(Opsional)</span>
                </label>
                <textarea
                    id="edit_description"
                    name="description"
                    rows="3"
                    placeholder="Tambahkan catatan, instruksi, atau rincian tugas…"
                    class="apple-input-box"
                    style="{{ (old('_method') === 'PUT' && $errors->has('description')) ? 'border-color:#ff3b30;' : '' }}"
                >{{ old('_method') === 'PUT' ? old('description') : '' }}</textarea>
                @if (old('_method') === 'PUT')
                    @error('description')
                        <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                    @enderror
                @endif
            </div>

            {{-- Prioritas & Tenggat Waktu (2-Col Grid) --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                {{-- Prioritas --}}
                <div>
                    <label for="edit_priority" class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                        Prioritas
                    </label>
                    <select
                        id="edit_priority"
                        name="priority"
                        required
                        class="apple-custom-select apple-input"
                        style="height:44px;{{ (old('_method') === 'PUT' && $errors->has('priority')) ? 'border-color:#ff3b30;' : '' }}"
                    >
                        <option value="menyusul" {{ (old('_method') === 'PUT' && old('priority') === 'menyusul') ? 'selected' : '' }}>Menyusul</option>
                        <option value="penting"  {{ (old('_method') === 'PUT' && old('priority') === 'penting')  ? 'selected' : '' }}>Penting</option>
                    </select>
                </div>

                {{-- Tenggat Waktu --}}
                <div>
                    <label class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                        Tenggat Waktu
                    </label>
                    <input
                        type="datetime-local"
                        id="edit_due_date"
                        name="due_date"
                        value="{{ old('_method') === 'PUT' ? old('due_date') : '' }}"
                        class="apple-datetime-input"
                        style="{{ (old('_method') === 'PUT' && $errors->has('due_date')) ? 'border-color:#ff3b30;' : '' }}"
                    >
                    @if (old('_method') === 'PUT')
                        @error('due_date')
                            <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            </div>

            {{-- Modal Footer Actions --}}
            <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px;margin-top:8px;padding-top:16px;border-top:1px solid #f0f0f0;">
                <button type="button" class="apple-btn-secondary-compact close-task-edit-modal-btn">
                    Batal
                </button>
                <button type="submit" class="apple-btn-primary">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- Side Calendar Companion Panel --}}
    <div class="apple-modal-calendar-panel" id="task-edit-modal-calendar-panel"></div>
</div>
</div>
@endsection
