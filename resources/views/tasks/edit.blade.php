@extends('layouts.app')

@section('title', 'Edit Tugas — JARA')

@section('subnav_title')
    <div style="display:flex;align-items:center;gap:12px;overflow:hidden;">
        <a href="{{ route('workspaces.show', $workspace) }}"
           style="font-size:14px;color:#7a7a7a;text-decoration:none;white-space:nowrap;flex-shrink:0;transition:color 0.12s;">
            &larr; {{ $workspace->name }}
        </a>
        <span style="color:#e0e0e0;flex-shrink:0;">/</span>
        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600;color:#1d1d1f;">Edit Tugas</span>
    </div>
@endsection

@section('content')
<div class="apple-modal-page-backdrop" style="display:flex;align-items:center;justify-content:center;padding:24px 0;min-height:calc(100vh - 180px);">
    <div class="apple-modal-stage" id="task-edit-page-stage">
        <div class="apple-modal-window-lg" style="transform:none;opacity:1;box-shadow:0 20px 48px rgba(0,0,0,0.08);">

            {{-- Header --}}
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #f0f0f0;">
                <div>
                    <span class="apple-chip" style="font-size:11px;padding:3px 10px;font-weight:600;text-transform:uppercase;letter-spacing:0.04em;margin-bottom:6px;display:inline-flex;">
                        Edit Tugas
                    </span>
                    <h1 id="task-modal-title" class="typography-tagline" style="color:#1d1d1f;margin:0 0 4px;font-size:20px;">
                        Edit Tugas
                    </h1>
                    <p class="typography-caption" style="color:#7a7a7a;margin:0;">
                        Perbarui informasi tugas pada ruang kerja <strong>{{ $workspace->name }}</strong>.
                    </p>
                </div>
                <a href="{{ route('workspaces.show', $workspace) }}"
                   style="background:none;border:none;color:#7a7a7a;font-size:20px;text-decoration:none;padding:4px 8px;line-height:1;border-radius:50%;transition:background-color 0.12s;"
                   title="Kembali">
                    ✕
                </a>
            </div>

            <form action="{{ route('workspaces.tasks.update', [$workspace, $task]) }}" method="POST"
                  style="display:flex;flex-direction:column;gap:16px;">
                @csrf
                @method('PUT')

                {{-- Judul Tugas --}}
                <div>
                    <label for="title" class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                        Judul Tugas <span style="color:#ff3b30;">*</span>
                    </label>
                    <input
                        type="text"
                        name="title"
                        id="title"
                        required
                        maxlength="255"
                        value="{{ old('title', $task->title) }}"
                        placeholder="Tulis apa yang perlu dikerjakan…"
                        class="apple-input"
                        style="{{ $errors->has('title') ? 'border-color:#ff3b30;' : '' }}"
                    >
                    @error('title')
                        <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label for="description" class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                        Catatan atau Detail Tugas <span style="color:#7a7a7a;font-weight:400;">(Opsional)</span>
                    </label>
                    <textarea
                        name="description"
                        id="description"
                        rows="3"
                        maxlength="2000"
                        placeholder="Tambahkan catatan, instruksi, atau rincian tugas…"
                        class="apple-input-box"
                        style="{{ $errors->has('description') ? 'border-color:#ff3b30;' : '' }}"
                    >{{ old('description', $task->description) }}</textarea>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:4px;">
                        @error('description')
                            <p class="typography-caption" style="color:#ff3b30;margin:0;">{{ $message }}</p>
                        @else
                            <span></span>
                        @enderror
                        <span class="typography-fine-print" style="color:#7a7a7a;">Maksimal 2000 karakter</span>
                    </div>
                </div>

                {{-- Prioritas + Tenggat Waktu (2-col grid) --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    {{-- Prioritas --}}
                    <div>
                        <label for="priority" class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                            Prioritas
                        </label>
                        <select
                            name="priority"
                            id="priority"
                            required
                            class="apple-custom-select apple-input"
                            style="height:44px;{{ $errors->has('priority') ? 'border-color:#ff3b30;' : '' }}"
                        >
                            <option value="menyusul" {{ old('priority', $task->priority) === 'menyusul' ? 'selected' : '' }}>Menyusul</option>
                            <option value="penting"  {{ old('priority', $task->priority) === 'penting'  ? 'selected' : '' }}>Penting</option>
                        </select>
                        @error('priority')
                            <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tenggat Waktu: custom Apple datetime picker --}}
                    <div>
                        <label class="typography-caption-strong" style="color:#1d1d1f;display:block;margin-bottom:6px;">
                            Tenggat Waktu
                        </label>
                        <input
                            type="datetime-local"
                            name="due_date"
                            id="due_date"
                            value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d\TH:i') : '') }}"
                            class="apple-datetime-input"
                            style="{{ $errors->has('due_date') ? 'border-color:#ff3b30;' : '' }}"
                        >
                        @error('due_date')
                            <p class="typography-caption" style="color:#ff3b30;margin:4px 0 0;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Modal Footer Actions --}}
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px;margin-top:8px;padding-top:16px;border-top:1px solid #f0f0f0;">
                    <a href="{{ route('workspaces.show', $workspace) }}" class="apple-btn-secondary-compact">
                        Batal
                    </a>
                    <button type="submit" class="apple-btn-primary">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Side Calendar Companion Panel --}}
        <div class="apple-modal-calendar-panel" id="task-edit-page-calendar-panel"></div>
    </div>
</div>
@endsection
