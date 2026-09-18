@extends('layouts.app')

@section('title', $workspace->name . ' — JARA')

@section('subnav_title')
    <div style="display:flex;align-items:center;gap:12px;overflow:hidden;">
        <a href="{{ route('workspaces.index') }}"
           style="font-size:14px;color:#7a7a7a;text-decoration:none;white-space:nowrap;flex-shrink:0;transition:color 0.12s;">
            &larr; Workspaces
        </a>
        <span style="color:#e0e0e0;flex-shrink:0;">/</span>
        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $workspace->name }}</span>
    </div>
@endsection

@section('subnav_actions')
    @if(auth()->id() === $workspace->user_id)
        <a href="{{ route('workspaces.edit', $workspace) }}" class="apple-btn-secondary-compact">
            Pengaturan
        </a>
    @endif
@endsection

@section('content')
<div style="display:flex;flex-direction:column;gap:24px;">

    {{-- ================================================================
         1. WORKSPACE HERO CARD
         ================================================================ --}}
    <div class="apple-card" style="padding:24px;">
        <div style="display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:16px;">
            <div style="flex:1;min-width:0;">
                <span class="apple-chip" style="font-size:12px;padding:4px 12px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:8px;display:inline-flex;">
                    Ruang Kerja
                </span>
                <h1 class="typography-display-lg" style="color:#1d1d1f;margin:8px 0 8px;word-break:break-word;">
                    {{ $workspace->name }}
                </h1>
                <p class="typography-body" style="color:#7a7a7a;margin:0;word-break:break-word;max-width:640px;">
                    {{ $workspace->description ?: 'Tidak ada keterangan deskripsi tambahan untuk workspace ini.' }}
                </p>
            </div>
            <div style="flex-shrink:0;">
                <span class="apple-chip" style="font-size:13px;padding:6px 14px;font-weight:500;">
                    Pemilik: {{ $workspace->user_id === auth()->id() ? 'Anda' : ($workspace->owner->name ?? 'Pengguna Lain') }}
                </span>
            </div>
        </div>
    </div>

    {{-- ================================================================
         2. PROGRES TUGAS
         ================================================================ --}}
    <div class="apple-card" style="padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
            <h2 class="typography-caption-strong" style="color:#7a7a7a;text-transform:uppercase;letter-spacing:0.06em;margin:0;">
                PROGRES TUGAS WORKSPACE
            </h2>
            <div class="typography-body-strong" style="color:#1d1d1f;">
                {{ $progressPercentage }}%
                <span class="typography-caption" style="color:#7a7a7a;font-weight:400;">
                    ({{ $completedTasks }} dari {{ $totalTasks }} selesai)
                </span>
            </div>
        </div>

        <!-- Progress bar: 8px height (multiple of 4) -->
        <div style="width:100%;background-color:#f0f0f0;border-radius:9999px;height:8px;overflow:hidden;">
            <div style="background-color:#0066cc;height:8px;border-radius:9999px;width:{{ $progressPercentage }}%;transition:width 0.3s ease;"></div>
        </div>

        <!-- Counters -->
        <div class="typography-caption" style="color:#7a7a7a;margin-top:12px;display:flex;flex-wrap:wrap;align-items:center;gap:12px;">
            <span style="color:#0066cc;font-weight:600;">{{ $completedTasks }} Selesai</span>
            <span>&bull;</span>
            <span style="color:#1d1d1f;">{{ $totalTasks - $completedTasks }} Belum Selesai</span>
            <span>&bull;</span>
            <span style="color:#ff3b30;font-weight:600;">{{ $pentingTasks }} Penting</span>
        </div>
    </div>

    {{-- ================================================================
         3. ANGGOTA TIM KOLABORASI
         ================================================================ --}}
    <div class="apple-card" style="padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <h2 class="typography-tagline" style="color:#1d1d1f;margin:0;">
                Anggota Tim ({{ 1 + $workspace->members->count() }} Orang)
            </h2>
        </div>

        {{-- Invite Form (owner only) --}}
        @if (auth()->id() === $workspace->user_id)
            <form action="{{ route('workspaces.members.store', $workspace->id) }}" method="POST"
                  style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:12px;margin-bottom:20px;padding:16px;background-color:#fafafc;border:1px solid #e0e0e0;border-radius:12px;">
                @csrf
                <div style="flex:1;min-width:200px;">
                    <label for="user_id" class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                        Undang Pengguna Terdaftar
                    </label>
                    <select name="user_id" id="user_id" required class="apple-custom-select apple-input" style="height:44px;">
                        <option value="">— Pilih Pengguna —</option>
                        @foreach ($availableUsers as $candidate)
                            <option value="{{ $candidate->id }}">{{ $candidate->name }} ({{ $candidate->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="apple-btn-primary-compact" style="height:44px;white-space:nowrap;">
                    + Undang ke Workspace
                </button>
            </form>
        @endif

        {{-- Member table --}}
        <div style="overflow-x:auto;">
            <table style="width:100%;text-align:left;border-collapse:collapse;" class="typography-caption">
                <thead>
                    <tr style="color:#7a7a7a;text-transform:uppercase;letter-spacing:0.04em;border-bottom:1px solid #e0e0e0;">
                        <th style="padding:12px 16px;font-weight:600;">Nama</th>
                        <th style="padding:12px 16px;font-weight:600;">Email</th>
                        <th style="padding:12px 16px;font-weight:600;">Peran</th>
                        <th style="padding:12px 16px;font-weight:600;text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Owner row --}}
                    <tr style="border-bottom:1px solid #f0f0f0;">
                        <td style="padding:16px;font-weight:600;color:#1d1d1f;">
                            {{ $workspace->owner->name ?? 'Pemilik' }}
                        </td>
                        <td style="padding:16px;color:#7a7a7a;">
                            {{ $workspace->owner->email ?? '—' }}
                        </td>
                        <td style="padding:16px;">
                            <span class="apple-chip" style="font-size:11px;padding:3px 10px;font-weight:600;color:#0066cc;border-color:#0066cc;">
                                PEMILIK
                            </span>
                        </td>
                        <td style="padding:16px;text-align:right;color:#7a7a7a;">—</td>
                    </tr>

                    {{-- Member rows --}}
                    @foreach ($workspace->members as $member)
                        <tr style="border-bottom:1px solid #f0f0f0;">
                            <td style="padding:16px;font-weight:500;color:#1d1d1f;">{{ $member->name }}</td>
                            <td style="padding:16px;color:#7a7a7a;">{{ $member->email }}</td>
                            <td style="padding:16px;">
                                <span class="apple-chip" style="font-size:11px;padding:3px 10px;font-weight:500;color:#7a7a7a;">
                                    ANGGOTA
                                </span>
                            </td>
                            <td style="padding:16px;text-align:right;">
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
                                                style="color:#ff3b30;background:none;border:none;padding:0;cursor:pointer;font-family:inherit;font-size:14px;font-weight:400;">
                                            Keluarkan
                                        </button>
                                    </form>
                                @else
                                    <span style="color:#7a7a7a;">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================================================================
         4. FORM TAMBAH TUGAS BARU
         ================================================================ --}}
    <div class="apple-card" style="padding:24px;">
        <h3 class="typography-tagline" style="color:#1d1d1f;margin:0 0 20px;">
            Tambah Tugas Baru
        </h3>

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
                    value="{{ old('title') }}"
                    required
                    placeholder="Tulis judul tugas apa yang perlu dikerjakan…"
                    class="apple-input {{ $errors->has('title') ? '' : '' }}"
                    style="{{ $errors->has('title') ? 'border-color:#ff3b30;' : '' }}"
                >
                @error('title')
                    <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                @enderror
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
                    placeholder="Tulis catatan atau detail tambahan tugas di sini…"
                    class="apple-input-box"
                    style="{{ $errors->has('description') ? 'border-color:#ff3b30;' : '' }}"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                @enderror
            </div>

            {{-- Prioritas, Tenggat, Submit (3-col grid) --}}
            <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:12px;align-items:flex-end;">
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
                        style="height:44px;{{ $errors->has('priority') ? 'border-color:#ff3b30;' : '' }}"
                    >
                        <option value="menyusul" {{ old('priority', 'menyusul') === 'menyusul' ? 'selected' : '' }}>Menyusul</option>
                        <option value="penting"  {{ old('priority') === 'penting'  ? 'selected' : '' }}>Penting</option>
                    </select>
                </div>

                {{-- Tenggat Waktu: custom Apple datetime picker --}}
                <div>
                    <label class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                        Tenggat Waktu
                    </label>
                    <input
                        type="datetime-local"
                        id="due_date"
                        name="due_date"
                        value="{{ old('due_date') }}"
                        class="apple-datetime-input"
                        style="{{ $errors->has('due_date') ? 'border-color:#ff3b30;' : '' }}"
                    >
                    @error('due_date')
                        <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div>
                    <button type="submit" class="apple-btn-primary" style="height:44px;width:100%;">
                        + Tambah Tugas
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ================================================================
         5. DAFTAR TUGAS
         ================================================================ --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        {{-- Filter header --}}
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;padding-bottom:16px;border-bottom:1px solid #e0e0e0;">
            <h3 class="typography-tagline" style="color:#1d1d1f;margin:0;">
                Daftar Tugas
            </h3>

            {{-- Filter chips --}}
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;">
                <a href="{{ route('workspaces.show', $workspace) }}"
                   class="apple-chip {{ empty($status) ? 'apple-chip-selected' : '' }}"
                   style="font-size:13px;padding:6px 14px;">
                    Semua ({{ $totalTasks }})
                </a>
                <a href="{{ route('workspaces.show', [$workspace, 'status' => 'active']) }}"
                   class="apple-chip {{ $status === 'active' ? 'apple-chip-selected' : '' }}"
                   style="font-size:13px;padding:6px 14px;">
                    Belum Selesai ({{ $totalTasks - $completedTasks }})
                </a>
                <a href="{{ route('workspaces.show', [$workspace, 'status' => 'completed']) }}"
                   class="apple-chip {{ $status === 'completed' ? 'apple-chip-selected' : '' }}"
                   style="font-size:13px;padding:6px 14px;">
                    Selesai ({{ $completedTasks }})
                </a>
                <a href="{{ route('workspaces.show', [$workspace, 'status' => 'penting']) }}"
                   class="apple-chip {{ $status === 'penting' ? 'apple-chip-selected' : '' }}"
                   style="font-size:13px;padding:6px 14px;">
                    Penting ({{ $pentingTasks }})
                </a>
            </div>
        </div>

        {{-- Empty state --}}
        @if ($tasks->isEmpty())
            <div class="apple-card" style="text-align:center;padding:64px 24px;">
                <div style="width:48px;height:48px;border-radius:50%;background-color:#f5f5f7;border:1px solid #e0e0e0;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#7a7a7a;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <h4 class="typography-body-strong" style="color:#1d1d1f;margin:0 0 4px;">
                    Belum Ada Tugas di Workspace Ini
                </h4>
                <p class="typography-caption" style="color:#7a7a7a;margin:0;">
                    Gunakan form di atas untuk menambahkan tugas baru.
                </p>
            </div>

        @else
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach ($tasks as $task)
                    {{-- Task card --}}
                    <div class="apple-card"
                         style="padding:20px;{{ $task->is_completed ? 'background-color:#fafafc;opacity:0.85;' : 'background-color:#ffffff;' }}">

                        {{-- Main row: toggle + info + actions --}}
                        <div style="display:flex;align-items:flex-start;gap:16px;">

                            {{-- Status toggle circle --}}
                            <form action="{{ route('workspaces.tasks.toggle-status', [$workspace, $task]) }}"
                                  method="POST" style="margin:0;flex-shrink:0;padding-top:2px;">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        title="{{ $task->is_completed ? 'Tandai belum selesai' : 'Tandai selesai' }}"
                                        style="width:24px;height:24px;border-radius:50%;border:{{ $task->is_completed ? '2px solid #0066cc' : '1.5px solid #e0e0e0' }};background-color:{{ $task->is_completed ? '#0066cc' : '#ffffff' }};color:{{ $task->is_completed ? '#ffffff' : 'transparent' }};display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.12s;flex-shrink:0;">
                                    @if ($task->is_completed)
                                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </button>
                            </form>

                            {{-- Task content --}}
                            <div style="flex:1;min-width:0;">
                                {{-- Priority + title --}}
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

                                {{-- Metadata --}}
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

                            {{-- Actions: Edit + Hapus --}}
                            <div style="display:flex;align-items:center;gap:12px;flex-shrink:0;align-self:center;" class="typography-caption">
                                <a href="{{ route('workspaces.tasks.edit', [$workspace, $task]) }}"
                                   class="apple-text-link">
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

                        {{-- ============================================
                             ATTACHMENTS SECTION
                             ============================================ --}}
                        <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f0f0f0;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                                <span class="typography-caption-strong" style="color:#1d1d1f;display:flex;align-items:center;gap:6px;">
                                    <svg width="14" height="14" fill="none" stroke="#7a7a7a" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                    </svg>
                                    Berkas Lampiran
                                    <span style="color:#7a7a7a;font-weight:400;">({{ $task->attachments->count() }})</span>
                                </span>
                            </div>

                            @if ($task->attachments->isNotEmpty())
                                <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:12px;">
                                    @foreach ($task->attachments as $attachment)
                                        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px;border-radius:12px;background-color:#fafafc;border:1px solid #e0e0e0;gap:12px;flex-wrap:wrap;"
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

                                            <div style="display:flex;align-items:center;gap:12px;flex-shrink:0;">
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
                                                                style="color:#ff3b30;background:none;border:none;padding:0;cursor:pointer;font-family:inherit;font-size:14px;font-weight:400;">
                                                            Hapus
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="typography-caption" style="color:#7a7a7a;font-style:italic;margin:0 0 12px;">
                                    Belum ada berkas lampiran untuk tugas ini.
                                </p>
                            @endif

                            {{-- Upload form --}}
                            <div style="padding:16px;background-color:#fafafc;border-radius:12px;border:1px solid #e0e0e0;">
                                <form action="{{ route('workspaces.tasks.attachments.store', [$workspace->id, $task->id]) }}"
                                      method="POST"
                                      enctype="multipart/form-data">
                                    @csrf
                                    <label for="attachment-{{ $task->id }}"
                                           class="typography-caption-strong"
                                           style="display:block;color:#1d1d1f;margin-bottom:8px;">
                                        Pilih Berkas Lampiran
                                        <span style="color:#7a7a7a;font-weight:400;">(Maks. 10 MB — PDF, DOC, DOCX, ZIP, PNG, JPG)</span>
                                    </label>
                                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;">
                                        <label for="attachment-{{ $task->id }}" class="apple-btn-secondary-compact" style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;margin:0;white-space:nowrap;">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Pilih Berkas
                                        </label>
                                        <span id="file-name-{{ $task->id }}" class="typography-caption" style="color:#7a7a7a;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            Belum ada berkas dipilih
                                        </span>
                                        <input type="file"
                                               id="attachment-{{ $task->id }}"
                                               name="attachment"
                                               required
                                               accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png"
                                               style="display:none;"
                                               onchange="document.getElementById('file-name-{{ $task->id }}').textContent = this.files[0] ? this.files[0].name : 'Belum ada berkas dipilih'">
                                        <button type="submit" class="apple-btn-primary-compact" style="flex-shrink:0;">
                                            Unggah
                                        </button>
                                    </div>
                                    @error('attachment')
                                        <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                                    @enderror
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
