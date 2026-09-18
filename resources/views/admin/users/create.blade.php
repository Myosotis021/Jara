@extends('layouts.app')

@section('title', 'Tambah Pengguna — JARA')

@section('subnav_title')
    <div class="flex items-center space-x-3 truncate">
        <a href="{{ route('admin.users.index') }}" class="text-[#7a7a7a] hover:text-[#1d1d1f] transition text-[14px]">
            &larr; Kelola Pengguna
        </a>
        <span class="text-[#e0e0e0]">/</span>
        <span class="truncate">Tambah Pengguna</span>
    </div>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Store Utility Card Form (strict 4px spacing) -->
    <div class="apple-card p-8">
        <div class="mb-8">
            <span class="apple-chip !py-1 !px-3 !text-[12px] uppercase font-semibold tracking-wider mb-2">
                Administrator
            </span>
            <h1 class="typography-display-md text-[#1d1d1f] tracking-tight mt-2">
                Tambah Pengguna Baru
            </h1>
            <p class="typography-body text-[#7a7a7a] mt-1">
                Daftarkan akun pengguna baru ke dalam platform manajemen tugas JARA.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block typography-caption-strong text-[#1d1d1f] mb-1">
                    Nama Lengkap <span class="text-[#ff3b30]">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    maxlength="255"
                    placeholder="Misal: Budi Santoso"
                    class="apple-input {{ $errors->has('name') ? '!border-[#ff3b30]' : '' }}"
                >
                @error('name')
                    <p class="typography-caption text-[#ff3b30] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block typography-caption-strong text-[#1d1d1f] mb-1">
                    Alamat Email <span class="text-[#ff3b30]">*</span>
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    maxlength="255"
                    placeholder="nama@institusi.ac.id"
                    class="apple-input {{ $errors->has('email') ? '!border-[#ff3b30]' : '' }}"
                >
                @error('email')
                    <p class="typography-caption text-[#ff3b30] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role (Custom Apple Dropdown) -->
            <div>
                <label for="role" class="block typography-caption-strong text-[#1d1d1f] mb-1">
                    Peran Sistem <span class="text-[#ff3b30]">*</span>
                </label>
                <select
                    id="role"
                    name="role"
                    required
                    class="apple-custom-select apple-input !h-[44px] {{ $errors->has('role') ? '!border-[#ff3b30]' : '' }}"
                >
                    <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>Pengguna / Anggota</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>
                @error('role')
                    <p class="typography-caption text-[#ff3b30] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block typography-caption-strong text-[#1d1d1f] mb-1">
                    Kata Sandi Awal <span class="text-[#ff3b30]">*</span>
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    minlength="6"
                    placeholder="Minimal 6 karakter"
                    class="apple-input {{ $errors->has('password') ? '!border-[#ff3b30]' : '' }}"
                >
                @error('password')
                    <p class="typography-caption text-[#ff3b30] mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Action Buttons: Cancel and button-primary -->
            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-[#f0f0f0]">
                <a href="{{ route('admin.users.index') }}" class="apple-btn-secondary-compact">
                    Batal
                </a>
                <button type="submit" class="apple-btn-primary">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
