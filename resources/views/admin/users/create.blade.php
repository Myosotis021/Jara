@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
<div class="max-w-xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Tambah Pengguna Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Daftarkan akun baru untuk anggota tim atau mahasiswa.</p>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            {{-- Name --}}
            <div class="mb-4">
                <label for="name" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Nama Lengkap
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    maxlength="255"
                    placeholder="Misal: Budi Santoso"
                    class="w-full px-3 py-2 border rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-blue-500 transition
                           {{ $errors->has('name') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}"
                >
                @error('name')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label for="email" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Alamat Email
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    maxlength="255"
                    placeholder="user@kampus.ac.id"
                    class="w-full px-3 py-2 border rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-blue-500 transition
                           {{ $errors->has('email') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}"
                >
                @error('email')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Role --}}
            <div class="mb-4">
                <label for="role" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Peran
                </label>
                <select
                    id="role"
                    name="role"
                    required
                    class="w-full px-3 py-2 border rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-blue-500 transition
                           {{ $errors->has('role') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}"
                >
                    <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>Pengguna / Anggota</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>
                @error('role')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-6">
                <label for="password" class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                    Kata Sandi Awal
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    minlength="6"
                    placeholder="Minimal 6 karakter"
                    class="w-full px-3 py-2 border rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-blue-500 transition
                           {{ $errors->has('password') ? 'border-red-500 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500' }}"
                >
                @error('password')
                    <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            {{-- Buttons --}}
            <div class="flex justify-between items-center">
                <a href="{{ route('admin.users.index') }}"
                   class="text-sm text-gray-600 hover:text-gray-800 font-medium px-4 py-2 rounded-lg hover:bg-gray-100 transition">
                    Batal
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2.5 rounded-lg transition shadow-sm cursor-pointer">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
