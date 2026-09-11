@extends('layouts.app')

@section('title', 'Edit Tugas - JARA')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('workspaces.show', $workspace) }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Workspace
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-xs border border-gray-200 p-6 sm:p-8">
        <h1 class="text-xl font-bold text-gray-900 tracking-tight mb-1">Edit Tugas</h1>
        <p class="text-sm text-gray-500 mb-6">Perbarui informasi tugas pada workspace <strong>{{ $workspace->name }}</strong>.</p>

        <form action="{{ route('workspaces.tasks.update', [$workspace, $task]) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Judul Tugas -->
            <div>
                <label for="title" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Judul Tugas <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="title" 
                    id="title" 
                    required 
                    maxlength="255"
                    value="{{ old('title', $task->title) }}"
                    placeholder="Tulis apa yang perlu dikerjakan..."
                    class="w-full px-3.5 py-2.5 border @error('title') border-red-500 focus:ring-red-500 @else border-gray-300 focus:ring-blue-500 @enderror rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 transition"
                >
                @error('title')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Catatan / Deskripsi -->
            <div>
                <label for="description" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Deskripsi / Catatan Tambahan (Opsional)
                </label>
                <textarea 
                    name="description" 
                    id="description" 
                    rows="4" 
                    maxlength="2000"
                    placeholder="Tulis catatan detail di sini..."
                    class="w-full px-3.5 py-2.5 border @error('description') border-red-500 focus:ring-red-500 @else border-gray-300 focus:ring-blue-500 @enderror rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 transition"
                >{{ old('description', $task->description) }}</textarea>
                <div class="flex justify-between items-center mt-1">
                    @error('description')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @else
                        <span></span>
                    @enderror
                    <span class="text-xs text-gray-400">Maksimal 2000 karakter</span>
                </div>
            </div>

            <!-- Prioritas -->
            <div>
                <label for="priority" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Prioritas <span class="text-red-500">*</span>
                </label>
                <select 
                    name="priority" 
                    id="priority" 
                    required 
                    class="w-full px-3.5 py-2.5 border @error('priority') border-red-500 focus:ring-red-500 @else border-gray-300 focus:ring-blue-500 @enderror rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 transition"
                >
                    <option value="menyusul" {{ old('priority', $task->priority) === 'menyusul' ? 'selected' : '' }}>Menyusul / Normal</option>
                    <option value="penting" {{ old('priority', $task->priority) === 'penting' ? 'selected' : '' }}>[!] Penting / High Priority</option>
                </select>
                @error('priority')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tenggat Waktu -->
            <div>
                <label for="due_date" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Tenggat Waktu (Deadline)
                </label>
                <input 
                    type="datetime-local" 
                    name="due_date" 
                    id="due_date" 
                    value="{{ old('due_date', $task->due_date ? $task->due_date->format('Y-m-d\TH:i') : '') }}"
                    class="w-full px-3.5 py-2.5 border @error('due_date') border-red-500 focus:ring-red-500 @else border-gray-300 focus:ring-blue-500 @enderror rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 transition"
                >
                @error('due_date')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="{{ route('workspaces.show', $workspace) }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-xs transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
