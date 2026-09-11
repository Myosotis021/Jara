@extends('layouts.app')

@section('title', $workspace->name . ' - JARA')

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
        <h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center space-x-2">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <span>Tambah Tugas Baru ("Kaya Nulis Biasa")</span>
        </h3>

        <form action="{{ route('workspaces.tasks.store', $workspace) }}" method="POST" class="space-y-3">
            @csrf

            <!-- Input Judul Tugas -->
            <div>
                <label for="title" class="sr-only">Judul Tugas</label>
                <input 
                    type="text" 
                    name="title" 
                    id="title" 
                    required 
                    maxlength="255"
                    value="{{ old('title') }}" 
                    placeholder="Tulis apa yang perlu dikerjakan..." 
                    class="w-full px-3.5 py-2.5 border @error('title') border-red-500 focus:ring-red-500 @else border-gray-300 focus:ring-blue-500 @enderror rounded-lg text-sm text-gray-900 font-medium focus:outline-none focus:ring-2 transition"
                >
                @error('title')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Input Catatan/Deskripsi -->
            <div>
                <label for="description" class="sr-only">Catatan Tambahan</label>
                <textarea 
                    name="description" 
                    id="description" 
                    rows="2" 
                    maxlength="2000"
                    placeholder="Tulis catatan detail atau instruksi di sini (opsional)..." 
                    class="w-full px-3.5 py-2 border @error('description') border-red-500 focus:ring-red-500 @else border-gray-300 focus:ring-blue-500 @enderror rounded-lg text-xs text-gray-700 focus:outline-none focus:ring-2 transition"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kontrol Prioritas, Deadline, dan Tombol Tambah -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end pt-1">
                <div class="sm:col-span-4">
                    <label for="priority" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">
                        Prioritas <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="priority" 
                        id="priority" 
                        required 
                        class="w-full px-3 py-2 border @error('priority') border-red-500 focus:ring-red-500 @else border-gray-300 focus:ring-blue-500 @enderror rounded-lg text-xs text-gray-800 focus:outline-none focus:ring-2 transition"
                    >
                        <option value="menyusul" {{ old('priority') === 'menyusul' ? 'selected' : '' }}>Menyusul / Normal</option>
                        <option value="penting" {{ old('priority') === 'penting' ? 'selected' : '' }}>[!] Penting / High Priority</option>
                    </select>
                    @error('priority')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-5">
                    <label for="due_date" class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">
                        Tenggat Waktu (Deadline)
                    </label>
                    <input 
                        type="datetime-local" 
                        name="due_date" 
                        id="due_date" 
                        value="{{ old('due_date') }}"
                        class="w-full px-3 py-2 border @error('due_date') border-red-500 focus:ring-red-500 @else border-gray-300 focus:ring-blue-500 @enderror rounded-lg text-xs text-gray-800 focus:outline-none focus:ring-2 transition"
                    >
                    @error('due_date')
                        <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-3">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-2.5 px-4 rounded-lg shadow-xs transition flex items-center justify-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>+ Tambah Tugas</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Filter & Daftar Tugas Section -->
    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-2 border-b border-gray-200">
            <h3 class="text-base font-bold text-gray-900 tracking-tight">
                DAFTAR TUGAS
            </h3>
            <!-- Filter Tabs -->
            <div class="flex items-center space-x-2 text-xs">
                <a href="{{ route('workspaces.show', $workspace) }}" class="px-3 py-1.5 rounded-lg font-medium transition {{ empty($status) ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Semua
                </a>
                <a href="{{ route('workspaces.show', [$workspace, 'status' => 'active']) }}" class="px-3 py-1.5 rounded-lg font-medium transition {{ $status === 'active' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Belum Selesai
                </a>
                <a href="{{ route('workspaces.show', [$workspace, 'status' => 'completed']) }}" class="px-3 py-1.5 rounded-lg font-medium transition {{ $status === 'completed' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Selesai
                </a>
                <a href="{{ route('workspaces.show', [$workspace, 'status' => 'penting']) }}" class="px-3 py-1.5 rounded-lg font-medium transition {{ $status === 'penting' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Penting
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
            <div class="space-y-3">
                @foreach ($tasks as $task)
                    <div class="p-4 rounded-xl border transition {{ $task->is_completed ? 'bg-gray-50 border-gray-200 opacity-75' : 'bg-white border-gray-200 hover:border-blue-300 shadow-xs' }} flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
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
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
