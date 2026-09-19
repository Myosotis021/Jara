@props(['task', 'workspace'])

<div class="enterprise-task-card {{ $task->is_completed ? 'is-completed' : '' }}" id="task-card-{{ $task->id }}" data-task-id="{{ $task->id }}">
    {{-- Main Task Row --}}
    <div style="display:flex;align-items:flex-start;gap:16px;">
        {{-- Status Toggle Button --}}
        <form action="{{ route('workspaces.tasks.toggle-status', [$workspace, $task]) }}"
              method="POST" class="task-toggle-form" style="margin:0;flex-shrink:0;padding-top:2px;">
            @csrf
            @method('PATCH')
            <button type="submit"
                    class="task-toggle-btn"
                    title="{{ $task->is_completed ? 'Tandai belum selesai' : 'Tandai selesai' }}"
                    style="width:24px;height:24px;border-radius:50%;border:{{ $task->is_completed ? '2px solid #0066cc' : '1.5px solid #d2d2d7' }};background-color:{{ $task->is_completed ? '#0066cc' : '#ffffff' }};color:{{ $task->is_completed ? '#ffffff' : 'transparent' }};display:flex;align-items:center;justify-content:center;cursor:pointer;transition:all 0.15s ease;flex-shrink:0;">
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

                <span class="task-title-text typography-body-strong"
                      style="{{ $task->is_completed ? 'text-decoration:line-through;color:#7a7a7a;' : 'color:#1d1d1f;' }}word-break:break-word;transition:color 0.15s ease;">
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
               style="display:inline-flex;align-items:center;gap:4px;font-size:13px;font-weight:500;"
               data-task-id="{{ $task->id }}"
               data-task-title="{{ $task->title }}"
               data-task-description="{{ $task->description ?? '' }}"
               data-task-priority="{{ $task->priority }}"
               data-task-due-date="{{ $task->due_date ? $task->due_date->format('Y-m-d\TH:i') : '' }}"
               data-task-action="{{ route('workspaces.tasks.update', [$workspace, $task]) }}">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                <span>Edit</span>
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
                        class="apple-text-link"
                        style="display:inline-flex;align-items:center;gap:4px;color:#ff3b30;background:none;border:none;padding:0;cursor:pointer;font-family:inherit;font-size:13px;font-weight:500;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        <line x1="10" y1="11" x2="10" y2="17"></line>
                        <line x1="14" y1="11" x2="14" y2="17"></line>
                    </svg>
                    <span>Hapus</span>
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
                                            class="apple-text-link"
                                            style="display:inline-flex;align-items:center;gap:3px;color:#ff3b30;background:none;border:none;padding:0;cursor:pointer;font-family:inherit;font-size:12px;font-weight:500;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        <span>Hapus</span>
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
