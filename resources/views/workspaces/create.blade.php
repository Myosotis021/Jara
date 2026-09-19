@extends('layouts.app')

@section('title', 'Buat Workspace Baru — JARA')

@section('subnav_title')
    <div class="flex items-center space-x-3 truncate">
        <a href="{{ route('workspaces.index') }}" class="text-[#7a7a7a] hover:text-[#1d1d1f] transition text-[14px]">
            &larr; Workspaces
        </a>
        <span class="text-[#e0e0e0]">/</span>
        <span class="truncate">Buat Workspace Baru</span>
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
                Buat Workspace Baru
            </h1>
            <p class="typography-body text-[#7a7a7a] mt-1">
                Wadah baru untuk memisahkan dan mengorganisasi kategori tugas proyek atau tim Anda.
            </p>
        </div>

        <form action="{{ route('workspaces.store') }}" method="POST" class="space-y-6">
            @csrf

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
                    value="{{ old('name') }}"
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
                >{{ old('description') }}</textarea>
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
                <a href="{{ route('workspaces.index') }}" class="apple-btn-secondary-compact">
                    Batal
                </a>
                <button type="submit" class="apple-btn-primary">
                    Simpan Workspace
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
