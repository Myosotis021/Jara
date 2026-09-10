<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $workspace->name }} - Jara Workspace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen">
    <!-- Navigation Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="#" class="font-bold text-xl text-blue-600 tracking-tight">JARA</a>
                <span class="text-gray-300">/</span>
                <span class="text-sm font-semibold text-gray-700">{{ $workspace->name }}</span>
            </div>
            <div class="flex items-center gap-4 text-xs text-gray-600">
                <span>Halo, <strong>{{ auth()->user()->name ?? 'Pengguna' }}</strong></span>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-6">
        <!-- Flash Alert -->
        @if (session('success'))
            <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4 fill-current text-emerald-600" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4 fill-current text-red-600" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Workspace Info Header -->
        <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm mb-6">
            <h1 class="text-xl font-bold text-gray-800">{{ $workspace->name }}</h1>
            @if ($workspace->description)
                <p class="text-xs text-gray-500 mt-1">{{ $workspace->description }}</p>
            @endif
        </div>

        <!-- Task List Section -->
        <div class="space-y-6">
            <h2 class="text-base font-semibold text-gray-800">Daftar Tugas & Lampiran</h2>

            @forelse ($workspace->tasks as $task)
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition">
                    <!-- Task Header -->
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-gray-900 {{ $task->is_completed ? 'line-through text-gray-400' : '' }}">
                                    {{ $task->title }}
                                </h3>
                                @if ($task->priority === 'penting')
                                    <span class="bg-red-50 text-red-600 text-[10px] font-bold px-2 py-0.5 rounded border border-red-200 uppercase tracking-wide">PENTING</span>
                                @else
                                    <span class="bg-gray-100 text-gray-600 text-[10px] font-medium px-2 py-0.5 rounded border border-gray-200 uppercase tracking-wide">MENYUSUL</span>
                                @endif

                                @if ($task->is_completed)
                                    <span class="bg-emerald-50 text-emerald-600 text-[10px] font-medium px-2 py-0.5 rounded border border-emerald-200">SELESAI</span>
                                @else
                                    <span class="bg-amber-50 text-amber-600 text-[10px] font-medium px-2 py-0.5 rounded border border-amber-200">BELUM SELESAI</span>
                                @endif
                            </div>
                            @if ($task->description)
                                <p class="text-xs text-gray-500 mt-1">{{ $task->description }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Attachment Section (PRD 4) -->
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-xs font-semibold text-gray-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                Berkas Lampiran Tugas ({{ $task->attachments->count() }})
                            </h4>
                        </div>

                        <!-- Attachment List -->
                        @if ($task->attachments->count() > 0)
                            <div class="space-y-2">
                                @foreach ($task->attachments as $attachment)
                                    <div class="flex flex-wrap items-center justify-between p-2.5 bg-gray-50 rounded-lg border border-gray-200 text-xs">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <!-- File Extension Badge -->
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
                                            
                                            <!-- File Name & Meta -->
                                            <div class="truncate">
                                                <a href="{{ route('workspaces.tasks.attachments.download', [$workspace->id, $task->id, $attachment->id]) }}" 
                                                   class="font-medium text-gray-800 hover:text-blue-600 truncate block">
                                                    {{ $attachment->original_name }}
                                                </a>
                                                <div class="text-[11px] text-gray-400">
                                                    {{ $attachment->formatted_size }} • Diunggah oleh: {{ $attachment->uploader->name ?? 'Pengguna' }} • {{ $attachment->created_at->format('d M Y H:i') }}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex items-center gap-3 mt-1 sm:mt-0">
                                            <!-- Download Link -->
                                            <a href="{{ route('workspaces.tasks.attachments.download', [$workspace->id, $task->id, $attachment->id]) }}" 
                                               class="text-blue-600 hover:text-blue-800 font-semibold flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                </svg>
                                                Unduh
                                            </a>

                                            <!-- Delete Button (Only uploader or workspace owner) -->
                                            @if ($attachment->user_id === auth()->id() || $workspace->user_id === auth()->id())
                                                <form action="{{ route('workspaces.tasks.attachments.destroy', [$workspace->id, $task->id, $attachment->id]) }}" 
                                                      method="POST" 
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus berkas lampiran ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 font-medium flex items-center gap-1">
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
                        <div class="mt-3 p-3 bg-blue-50/50 rounded-lg border border-dashed border-blue-200">
                            <form action="{{ route('workspaces.tasks.attachments.store', [$workspace->id, $task->id]) }}" 
                                  method="POST" 
                                  enctype="multipart/form-data">
                                @csrf
                                <label for="attachment-{{ $task->id }}" class="block text-xs font-semibold text-gray-700 mb-1">
                                    Pilih Berkas Lampiran (Maksimal 10 MB - PDF, DOC, DOCX, ZIP, PNG, JPG)
                                </label>
                                <input type="file" 
                                       id="attachment-{{ $task->id }}" 
                                       name="attachment" 
                                       required 
                                       accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png"
                                       class="block w-full text-xs text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                                
                                @error('attachment')
                                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                                @enderror

                                <button type="submit" class="mt-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-1.5 px-3 rounded transition flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Unggah Berkas
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-gray-200 text-center text-gray-500">
                    <p class="text-sm">Belum ada tugas di workspace ini.</p>
                </div>
            @endforelse
        </div>
    </main>
</body>
</html>
