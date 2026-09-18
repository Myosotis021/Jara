@extends('layouts.app')

@section('title', 'Edit Workspace — JARA')

@section('subnav_title')
    <div class="flex items-center space-x-3 truncate">
        <a href="{{ route('workspaces.show', $workspace) }}" class="text-[#7a7a7a] hover:text-[#1d1d1f] transition text-[14px]">
            &larr; {{ $workspace->name }}
        </a>
        <span class="text-[#e0e0e0]">/</span>
        <span class="truncate">Pengaturan</span>
    </div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Store Utility Card Form -->
    <div class="apple-card p-8 sm:p-10">
        <div class="mb-8">
            <span class="apple-chip !py-1 !px-3 !text-[12px] uppercase font-semibold tracking-wider mb-2">
                Workspace
            </span>
            <h1 class="typography-display-md text-[#1d1d1f] tracking-tight mt-2">
                Edit Workspace
            </h1>
            <p class="typography-body text-[#7a7a7a] mt-1">
                Perbarui nama atau rincian keterangan untuk ruang kerja ini.
            </p>
        </div>

        <form action="{{ route('workspaces.update', $workspace) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Nama Workspace -->
            <div>
                <label for="name" class="block typography-caption-strong text-[#1d1d1f] mb-1.5">
                    Nama Workspace <span class="text-[#ff3b30]">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    required 
                    maxlength="100"
                    placeholder="Misal: Tugas Kuliah Semester 4"
                    value="{{ old('name', $workspace->name) }}"
                    class="apple-input {{ $errors->has('name') ? '!border-[#ff3b30]' : '' }}"
                >
                @error('name')
                    <p class="typography-caption text-[#ff3b30] mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Deskripsi Workspace -->
            <div>
                <label for="description" class="block typography-caption-strong text-[#1d1d1f] mb-1.5">
                    Deskripsi Singkat (Opsional)
                </label>
                <textarea 
                    name="description" 
                    id="description" 
                    rows="4" 
                    maxlength="500"
                    placeholder="Deskripsi singkat mengenai daftar tugas ini..."
                    class="apple-input-box {{ $errors->has('description') ? '!border-[#ff3b30]' : '' }}"
                >{{ old('description', $workspace->description) }}</textarea>
                <div class="flex justify-between items-center mt-1.5 typography-fine-print text-[#7a7a7a]">
                    @error('description')
                        <p class="typography-caption text-[#ff3b30]">{{ $message }}</p>
                    @else
                        <span></span>
                    @enderror
                    <span>Maksimal 500 karakter</span>
                </div>
            </div>

            <!-- Action Buttons: Cancel and button-primary -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-[#f0f0f0]">
                <a href="{{ route('workspaces.show', $workspace) }}" class="apple-btn-secondary-compact">
                    Batal
                </a>
                <button type="submit" class="apple-btn-primary">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <div class="apple-card p-8 sm:p-10 mt-6" style="border-color:#f5c6c2;">
        <h2 class="typography-tagline text-[#1d1d1f] m-0">
            Hapus Workspace
        </h2>
        <p class="typography-body text-[#7a7a7a] mt-2 mb-6">
            Tindakan ini permanen. Seluruh tugas dan file lampiran di dalam workspace ini akan ikut terhapus.
        </p>
        <form action="{{ route('workspaces.destroy', $workspace) }}"
              method="POST"
              style="margin:0;"
              data-confirm="Hapus workspace ini? Seluruh tugas dan file lampiran di dalamnya akan ikut terhapus permanen."
              data-confirm-title="Hapus Workspace"
              data-confirm-btn="Hapus Workspace">
            @csrf
            @method('DELETE')
            <button type="submit" class="apple-btn-danger" style="display:inline-flex;align-items:center;gap:6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
                <span>Hapus Workspace</span>
            </button>
        </form>
    </div>
</div>
@endsection
