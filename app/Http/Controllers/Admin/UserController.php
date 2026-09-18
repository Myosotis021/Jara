<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Tampilkan daftar semua pengguna.
     */
    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Tampilkan form tambah pengguna baru.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Simpan pengguna baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,user'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna ' . $validated['name'] . ' berhasil didaftarkan.');
    }

    /**
     * Hapus pengguna dari sistem secara atomic dan bersihkan file lampiran.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Collect file paths before user deletion (CASCADE will delete DB records)
        $ownedWorkspaceFilePaths = TaskAttachment::whereHas('task.workspace', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->pluck('file_path');

        $uploadedFilePaths = TaskAttachment::where('user_id', $user->id)->pluck('file_path');

        $filePaths = $ownedWorkspaceFilePaths->merge($uploadedFilePaths)
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        try {
            DB::transaction(function () use ($user) {
                $user->delete();
            });
        } catch (\Throwable $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Gagal menghapus pengguna. Silakan coba lagi.');
        }

        if (!empty($filePaths)) {
            Storage::disk('public')->delete($filePaths);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
