@extends('layouts.app')

@section('title', $workspace->name . ' — JARA')

@section('content')
<div class="space-y-6">
    <!-- Back to workspaces navigation -->
    <div>
        <a href="{{ route('workspaces.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Workspaces
        </a>
    </div>

    <!-- Workspace Header -->
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2.5 py-1 rounded">
                    Workspace
                </span>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight mt-2 break-words">
                    {{ $workspace->name }}
                </h1>
                <p class="text-sm text-gray-500 mt-1 break-words">
                    {{ $workspace->description ?: 'Tidak ada deskripsi.' }}
                </p>
            </div>
            <div class="flex items-center space-x-2 text-xs text-gray-500 shrink-0">
                <span class="px-2.5 py-1 rounded bg-gray-100 font-medium">
                    Pemilik: {{ $workspace->user_id === auth()->id() ? 'Anda' : ($workspace->owner->name ?? 'Lain') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Monitoring Progres Card -->
    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-xs">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-700">
                PROGRES TUGAS WORKSPACE
            </h2>
            <span class="text-sm font-bold text-gray-900">
                {{ $progressPercentage }}% <span class="text-xs font-normal text-gray-500">({{ $completedTasks }} dari {{ $totalTasks }} selesai)</span>
            </span>
        </div>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden mt-3">
            <div class="bg-green-600 h-3 rounded-full transition-all duration-300" style="width: {{ $progressPercentage }}%"></div>
        </div>

        <div class="text-xs font-medium text-gray-500 mt-3 flex flex-wrap gap-x-4 gap-y-1">
            <span class="text-green-700 font-semibold">{{ $completedTasks }} Selesai</span>
            <span>&bull;</span>
            <span class="text-gray-700">{{ $totalTasks - $completedTasks }} Belum Selesai</span>
            <span>&bull;</span>
            <span class="text-red-700 font-semibold">{{ $pentingTasks }} Penting</span>
        </div>
    </div>

    <!-- Form Tambah Tugas Baru ("Kaya Nulis Biasa") -->
    <div class="bg-white p-5 rounded-xl border border-blue-100 shadow-xs">
        <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">
            Tambah Tugas Baru
        </h3>

        <form action="{{ route('workspaces.tasks.store', $workspace) }}" method="POST" class="space-y-3">
            @csrf

            <!-- Input Judul Tugas -->
            <div>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    required
                    placeholder="Tulis judul tugas apa yang perlu dikerjakan..."
                    class="w-full px-3.5 py-2.5 border rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-blue-500 transition
                           {{ $errors->has('title') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}"
                >
                @error('title')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Input Catatan/Deskripsi Tambahan -->
            <div>
                <textarea
                    name="description"
                    rows="2"
                    placeholder="Tulis catatan atau detail tambahan tugas di sini (opsional)..."
                    class="w-full px-3.5 py-2 border rounded-lg text-xs text-gray-700 focus:outline-none focus:ring-2 focus:border-blue-500 transition
                           {{ $errors->has('description') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end pt-1">
                <!-- Dropdown Prioritas -->
                <div>
                    <label for="priority" class="block text-xs font-semibold text-gray-600 mb-1">
                        Prioritas:
                    </label>
                    <select
                        id="priority"
                        name="priority"
                        required
                        class="w-full px-3 py-2 border rounded-lg text-xs text-gray-800 focus:outline-none focus:ring-2 focus:border-blue-500 bg-white
                               {{ $errors->has('priority') ? 'border-red-500' : 'border-gray-300' }}"
                    >
                        <option value="penting" {{ old('priority') === 'penting' ? 'selected' : '' }}>[!] Penting</option>
                        <option value="menyusul" {{ old('priority', 'menyusul') === 'menyusul' ? 'selected' : '' }}>Menyusul</option>
                    </select>
                </div>

                <!-- Input Tenggat Waktu -->
                <div>
                    <label for="due_date" class="block text-xs font-semibold text-gray-600 mb-1">
                        Tenggat Waktu:
                    </label>
                    <input
                        type="datetime-local"
                        id="due_date"
                        name="due_date"
                        value="{{ old('due_date') }}"
                        class="w-full px-3 py-1.5 border rounded-lg text-xs text-gray-800 focus:outline-none focus:ring-2 focus:border-blue-500 bg-white
                               {{ $errors->has('due_date') ? 'border-red-500' : 'border-gray-300' }}"
                    >
                </div>

                <!-- Tombol Submit -->
                <div>
                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg text-xs transition duration-150 ease-in-out shadow-xs cursor-pointer"
                    >
                        + Tambah Tugas
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Section Daftar Tugas -->
    <div class="space-y-4">
        <!-- Filter Tabs & Title -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-gray-200">
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">
                Daftar Tugas
            </h3>

            <!-- Status Filters -->
            <div class="flex items-center space-x-1 text-xs">
                <a href="{{ route('workspaces.show', $workspace) }}"
                   class="px-2.5 py-1 rounded-md transition {{ empty($status) ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Semua ({{ $totalTasks }})
                </a>
                <a href="{{ route('workspaces.show', [$workspace, 'status' => 'active']) }}"
                   class="px-2.5 py-1 rounded-md transition {{ $status === 'active' ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Belum Selesai ({{ $totalTasks - $completedTasks }})
                </a>
                <a href="{{ route('workspaces.show', [$workspace, 'status' => 'completed']) }}"
                   class="px-2.5 py-1 rounded-md transition {{ $status === 'completed' ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Selesai ({{ $completedTasks }})
                </a>
                <a href="{{ route('workspaces.show', [$workspace, 'status' => 'penting']) }}"
                   class="px-2.5 py-1 rounded-md transition {{ $status === 'penting' ? 'bg-blue-600 text-white font-semibold' : 'text-gray-600 hover:bg-gray-100' }}">
                    Penting ({{ $pentingTasks }})
                </a>
            </div>
        </div>

        @if ($tasks->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                <div class="w-12 h-12 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
                <h4 class="text-sm font-semibold text-gray-800 mb-1">Belum Ada Tugas di Workspace Ini</h4>
                <p class="text-xs text-gray-500">Tulis tugas pertama Anda pada form di atas!</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach ($tasks as $task)
                    <div class="p-4 rounded-xl border transition {{ $task->is_completed ? 'bg-gray-50 border-gray-200 opacity-75' : 'bg-white border-gray-200 hover:border-blue-300 shadow-xs' }}">
                        <!-- Baris Utama Tugas: Checkbox, Judul, Info, & Aksi -->
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <!-- Sisi Kiri: Checkbox & Teks -->
                            <div class="flex items-start space-x-3 flex-grow">
                                <!-- Toggle Button Form -->
                                <form action="{{ route('workspaces.tasks.toggle-status', [$workspace, $task]) }}" method="POST" class="shrink-0 mt-0.5">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="{{ $task->is_completed ? 'Tandai belum selesai' : 'Tandai selesai' }}" class="w-6 h-6 rounded-md border flex items-center justify-center cursor-pointer transition {{ $task->is_completed ? 'border-green-600 bg-green-600 text-white' : 'border-gray-300 hover:border-blue-500 bg-white' }}">
                                        @if ($task->is_completed)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif
                                    </button>
                                </form>

                                <!-- Konten Tugas -->
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                        <!-- Priority Badge -->
                                        @if ($task->priority === 'penting')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 uppercase tracking-wider">
                                                [PENTING]
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700 uppercase tracking-wider">
                                                [MENYUSUL]
                                            </span>
                                        @endif

                                        <!-- Title -->
                                        <span class="text-sm font-semibold {{ $task->is_completed ? 'line-through text-gray-400' : 'text-gray-900' }} break-words">
                                            {{ $task->title }}
                                        </span>
                                    </div>

                                    <!-- Description -->
                                    @if ($task->description)
                                        <p class="text-xs text-gray-600 break-words mt-1">
                                            {!! nl2br(e($task->description)) !!}
                                        </p>
                                    @endif

                                    <!-- Metadata info -->
                                    <div class="flex items-center space-x-3 text-[11px] text-gray-500 flex-wrap gap-y-1 pt-1">
                                        @if ($task->due_date)
                                            <span class="{{ !$task->is_completed && $task->due_date->isPast() ? 'text-red-600 font-medium' : '' }}">
                                                Tenggat: {{ $task->due_date->format('d M Y H:i') }}
                                                @if (!$task->is_completed && $task->due_date->isPast())
                                                    <span class="text-red-600 font-bold">(Terlewat)</span>
                                                @endif
                                            </span>
                                            <span>&bull;</span>
                                        @endif

                                        @if ($task->is_completed && $task->completed_at)
                                            <span class="text-green-700">
                                                Selesai pada: {{ $task->completed_at->format('d M Y H:i') }}
                                            </span>
                                            <span>&bull;</span>
                                        @endif

                                        <span>Oleh: {{ $task->creator->name ?? 'Pengguna' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Sisi Kanan: Action Buttons -->
                            <div class="flex items-center space-x-3 shrink-0 self-end sm:self-center text-xs">
                                <a href="{{ route('workspaces.tasks.edit', [$workspace, $task]) }}" class="text-gray-600 hover:text-gray-900 font-medium transition">
                                    Edit
                                </a>
                                <form action="{{ route('workspaces.tasks.destroy', [$workspace, $task]) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium transition cursor-pointer">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Lampiran / Berkas Tugas (PRD 4) -->
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    Berkas Lampiran Tugas
                                    <span class="text-[11px] font-normal text-gray-400">({{ $task->attachments->count() }})</span>
                                </span>
                            </div>

                            <!-- List Berkas yang Sudah Diunggah -->
                            @if ($task->attachments->isNotEmpty())
                                <div class="space-y-1.5 mb-3">
                                    @foreach ($task->attachments as $attachment)
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-2 rounded-lg bg-gray-50 border border-gray-200 text-xs gap-2">
                                            <div class="flex items-center space-x-2 truncate">
                                                @php
                                                    $ext = strtoupper(pathinfo($attachment->original_name, PATHINFO_EXTENSION));
                                                    $badgeClass = match($ext) {
                                                        'PDF' => 'bg-red-100 text-red-700 border-red-200',
                                                        'ZIP' => 'bg-purple-100 text-purple-700 border-purple-200',
                                                        'DOC', 'DOCX' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                        'JPG', 'JPEG', 'PNG' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                        default => 'bg-gray-100 text-gray-700 border-gray-200',
                                                    };
                                                @endphp
                                                <span class="px-1.5 py-0.5 text-[10px] font-bold rounded border {{ $badgeClass }}">
                                                    {{ $ext }}
                                                </span>
                                                
                                                <div class="truncate">
                                                    <a href="{{ route('workspaces.tasks.attachments.download', [$workspace->id, $task->id, $attachment->id]) }}" 
                                                       class="font-medium text-gray-800 hover:text-blue-600 truncate block">
                                                        {{ $attachment->original_name }}
                                                    </a>
                                                    <div class="text-[10px] text-gray-400">
                                                        {{ $attachment->formatted_size }} &bull; Diunggah oleh: {{ $attachment->uploader->name ?? 'Pengguna' }} &bull; {{ $attachment->created_at->format('d M Y H:i') }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-3 shrink-0 self-end sm:self-center">
                                                <a href="{{ route('workspaces.tasks.attachments.download', [$workspace->id, $task->id, $attachment->id]) }}" 
                                                   class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                    </svg>
                                                    Unduh
                                                </a>

                                                @if ($attachment->user_id === auth()->id() || $workspace->user_id === auth()->id())
                                                    <form action="{{ route('workspaces.tasks.attachments.destroy', [$workspace->id, $task->id, $attachment->id]) }}" 
                                                          method="POST" 
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas lampiran ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-500 hover:text-red-700 font-medium flex items-center gap-1 cursor-pointer">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                            Hapus
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-gray-400 italic py-1">Belum ada berkas yang diunggah untuk tugas ini.</p>
                            @endif

                            <!-- Form Upload Berkas Baru -->
                            <div class="mt-2 p-3 bg-blue-50/50 rounded-lg border border-dashed border-blue-200">
                                <form action="{{ route('workspaces.tasks.attachments.store', [$workspace->id, $task->id]) }}" 
                                      method="POST" 
                                      enctype="multipart/form-data">
                                    @csrf
                                    <label for="attachment-{{ $task->id }}" class="block text-[11px] font-semibold text-gray-700 mb-1">
                                        Pilih Berkas Lampiran (Maksimal 10 MB — PDF, DOC, DOCX, ZIP, PNG, JPG)
                                    </label>
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                                        <input type="file" 
                                               id="attachment-{{ $task->id }}" 
                                               name="attachment" 
                                               required 
                                               accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png"
                                               class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                                        
                                        <button type="submit" class="shrink-0 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-1.5 px-3 rounded transition flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Unggah
                                        </button>
                                    </div>
                                    @error('attachment')
                                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
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
