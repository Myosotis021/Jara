@extends('layouts.app')

@section('title', 'Manajemen Pengguna — JARA')

@section('subnav_title')
    <div class="flex items-center space-x-3 truncate">
        <span>Kelola Pengguna</span>
    </div>
@endsection

@section('subnav_actions')
    <a href="{{ route('admin.users.create') }}" class="apple-btn-primary-compact">
        + Tambah Pengguna
    </a>
@endsection

@section('content')
<div class="space-y-8">
    <!-- Header Section (strict 4px spacing) -->
    <div class="pb-6 border-b border-[#e0e0e0] flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
        <div>
            <span class="apple-chip !py-1 !px-3 !text-[12px] uppercase font-semibold tracking-wider mb-2">
                Administrator
            </span>
            <h1 class="typography-display-md text-[#1d1d1f] tracking-tight mt-2">
                Manajemen Pengguna
            </h1>
            <p class="typography-body text-[#7a7a7a] mt-1">
                Daftar pengguna terdaftar yang memiliki akses ke sistem JARA.
            </p>
        </div>
        <div>
            <a href="{{ route('admin.users.create') }}" class="apple-btn-primary">
                + Tambah Pengguna
            </a>
        </div>
    </div>

    <!-- Table Card: store-utility-card style -->
    <div class="apple-card overflow-hidden !p-0">
        @if($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left typography-caption">
                    <thead class="bg-[#fafafc] border-b border-[#e0e0e0]">
                        <tr class="text-[#7a7a7a] uppercase text-[12px] tracking-wider">
                            <th class="px-6 py-4 font-semibold">Nama</th>
                            <th class="px-6 py-4 font-semibold">Email</th>
                            <th class="px-6 py-4 font-semibold">Peran</th>
                            <th class="px-6 py-4 font-semibold">Terdaftar</th>
                            <th class="px-6 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0f0f0]">
                        @foreach($users as $user)
                            <tr class="hover:bg-[#fafafc] transition">
                                <td class="px-6 py-4 font-semibold text-[#1d1d1f]">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-[#7a7a7a]">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    @if($user->isAdmin())
                                        <span class="apple-chip !py-0.5 !px-2.5 !text-[11px] font-semibold text-[#0066cc] !border-[#0066cc]">
                                            ADMIN
                                        </span>
                                    @else
                                        <span class="apple-chip !py-0.5 !px-2.5 !text-[11px] font-medium text-[#7a7a7a]">
                                            USER
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-[#7a7a7a]">
                                    {{ $user->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}"
                                              method="POST"
                                              class="inline"
                                              data-confirm="Apakah Anda yakin ingin menghapus akun pengguna ini? Seluruh data yang terkait akan terpengaruh."
                                              data-confirm-title="Hapus Akun Pengguna"
                                              data-confirm-btn="Hapus Pengguna">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 text-[#ff3b30] hover:underline font-normal cursor-pointer bg-transparent border-0 p-0 text-sm">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                                </svg>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[#7a7a7a] italic">Akun Anda</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-16 text-center">
                <p class="typography-body text-[#7a7a7a]">
                    Belum ada pengguna lain yang terdaftar.
                    <a href="{{ route('admin.users.create') }}" class="apple-text-link">Klik Tambah Pengguna</a>
                    untuk mendaftarkan akun baru.
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
