@extends('layouts.app')

@section('title', 'Workspace Saya - JARA')

@section('content')
<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-gray-200">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Workspace &amp; Daftar Tugas Saya</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola dan atur seluruh ruang kerja tugas Anda di satu tempat.</p>
        </div>
        <div>
            <a href="{{ route('workspaces.create') }}" class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg shadow-xs transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>+ Buat Workspace</span>
            </a>
        </div>
    </div>

    <!-- My Workspaces -->
    <div>
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center space-x-2">
            <span>Workspace Saya</span>
            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium">{{ $myWorkspaces->count() }}</span>
        </h2>

        @if ($myWorkspaces->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center max-w-lg mx-auto shadow-xs my-6">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Belum Ada Workspace Dibuat</h3>
                <p class="text-sm text-gray-500 mb-6">Kelompokkan tugas kuliah, kantor, atau proyek Anda dalam satu wadah.</p>
                <a href="{{ route('workspaces.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-xs transition">
                    + Buat Workspace Baru
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($myWorkspaces as $workspace)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-xs hover:shadow-md transition p-5 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <h3 class="text-lg font-bold text-gray-900 tracking-tight hover:text-blue-600 transition break-words">
                                    {{ $workspace->name }}
                                </h3>
                            </div>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2 break-words">
                                {{ $workspace->description ?: 'Tidak ada deskripsi.' }}
                            </p>
                        </div>
                        <div>
                            <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded bg-blue-50 text-blue-700 self-start mb-4">
                                Pemilik: Anda
                            </span>
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-xs">
                                <a href="{{ route('workspaces.show', $workspace) }}" class="text-blue-600 font-semibold hover:text-blue-800">
                                    Buka
                                </a>
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('workspaces.edit', $workspace) }}" class="text-gray-600 hover:text-gray-900 font-medium">
                                        Edit
                                    </a>
                                    <form action="{{ route('workspaces.destroy', $workspace) }}" method="POST" class="inline" onsubmit="return confirm('Hapus workspace ini? Seluruh tugas di dalamnya akan ikut terhapus.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 font-medium">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Shared Workspaces Section -->
    @if ($sharedWorkspaces->isNotEmpty())
        <div class="pt-6 border-t border-gray-200">
            <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center space-x-2">
                <span>Dibagikan dengan Saya</span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium">{{ $sharedWorkspaces->count() }}</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($sharedWorkspaces as $shared)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-xs hover:shadow-md transition p-5 flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 tracking-tight hover:text-blue-600 transition break-words">
                                {{ $shared->name }}
                            </h3>
                            <p class="text-sm text-gray-500 mb-4 line-clamp-2 break-words">
                                {{ $shared->description ?: 'Tidak ada deskripsi.' }}
                            </p>
                        </div>
                        <div>
                            <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded bg-amber-50 text-amber-700 self-start mb-4">
                                Anggota (Shared)
                            </span>
                            <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-xs">
                                <a href="{{ route('workspaces.show', $shared) }}" class="text-blue-600 font-semibold hover:text-blue-800">
                                    Buka
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
