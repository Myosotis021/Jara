@extends('layouts.app')

@section('title', 'Edit Workspace - JARA')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('workspaces.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Daftar Workspace
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-xs border border-gray-200 p-6 sm:p-8">
        <h1 class="text-xl font-bold text-gray-900 tracking-tight mb-1">Edit Workspace</h1>
        <p class="text-sm text-gray-500 mb-6">Perbarui nama atau keterangan workspace Anda.</p>

        <form action="{{ route('workspaces.update', $workspace) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Nama Workspace -->
            <div>
                <label for="name" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Nama Workspace <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    required 
                    maxlength="100"
                    placeholder="Misal: Tugas Kuliah Semester 4"
                    value="{{ old('name', $workspace->name) }}"
                    class="w-full px-3.5 py-2.5 border @error('name') border-red-500 focus:ring-red-500 @else border-gray-300 focus:ring-blue-500 @enderror rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 transition"
                >
                @error('name')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Deskripsi Workspace -->
            <div>
                <label for="description" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Deskripsi Singkat (Opsional)
                </label>
                <textarea 
                    name="description" 
                    id="description" 
                    rows="4" 
                    maxlength="500"
                    placeholder="Deskripsi singkat mengenai daftar tugas ini..."
                    class="w-full px-3.5 py-2.5 border @error('description') border-red-500 focus:ring-red-500 @else border-gray-300 focus:ring-blue-500 @enderror rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 transition"
                >{{ old('description', $workspace->description) }}</textarea>
                <div class="flex justify-between items-center mt-1">
                    @error('description')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @else
                        <span></span>
                    @enderror
                    <span class="text-xs text-gray-400">Maksimal 500 karakter</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                <a href="{{ route('workspaces.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition">
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
