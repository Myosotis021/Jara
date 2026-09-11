@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@section('content')
<div>
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h1>
        <a href="{{ route('admin.users.create') }}"
           class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition shadow-sm">
            + Tambah Pengguna
        </a>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-6 py-3 text-gray-500 uppercase text-xs font-semibold tracking-wider">Nama</th>
                            <th class="text-left px-6 py-3 text-gray-500 uppercase text-xs font-semibold tracking-wider">Email</th>
                            <th class="text-left px-6 py-3 text-gray-500 uppercase text-xs font-semibold tracking-wider">Peran</th>
                            <th class="text-left px-6 py-3 text-gray-500 uppercase text-xs font-semibold tracking-wider">Terdaftar</th>
                            <th class="text-right px-6 py-3 text-gray-500 uppercase text-xs font-semibold tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($users as $user)
                            <tr class="text-sm text-gray-700 hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                                <td class="px-6 py-4">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    @if($user->isAdmin())
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Admin
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            User
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-500">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}"
                                              method="POST"
                                              class="inline"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun pengguna ini? Seluruh data yang terkait akan terpengaruh.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 hover:text-red-800 text-xs font-semibold py-1 px-2.5 rounded hover:bg-red-50 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Akun Anda</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="text-gray-500 text-sm">
                    Belum ada pengguna lain yang terdaftar.
                    <a href="{{ route('admin.users.create') }}" class="text-blue-600 hover:underline font-medium">Klik Tambah Pengguna</a>
                    untuk mendaftarkan akun baru.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
