<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkspaceMemberController extends Controller
{
    /**
     * Store a newly added member in workspace_members.
     */
    public function store(Request $request, Workspace $workspace)
    {
        // PRD 3 Section 23: Hanya pemilik workspace yang diizinkan mengundang anggota
        if ($workspace->user_id !== auth()->id()) {
            abort(403, 'Hanya pemilik workspace yang dapat mengundang anggota.');
        }

        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::notIn([$workspace->user_id]), // Tidak boleh mengundang diri sendiri (pemilik)
            ],
        ], [
            'user_id.required' => 'Silakan pilih pengguna yang ingin diundang.',
            'user_id.exists' => 'Pengguna tidak ditemukan di sistem.',
            'user_id.not_in' => 'Anda adalah pemilik workspace ini.',
        ]);

        // Cek duplikasi keanggotaan
        if ($workspace->members()->where('user_id', $validated['user_id'])->exists()) {
            return back()->withErrors(['user_id' => 'Pengguna ini sudah menjadi anggota workspace.']);
        }

        $workspace->members()->attach($validated['user_id']);

        $invitedUser = User::find($validated['user_id']);
        $userName = $invitedUser ? $invitedUser->name : 'Pengguna';

        return back()->with('success', "Pengguna {$userName} berhasil ditambahkan ke workspace.");
    }

    /**
     * Remove the specified member from workspace_members.
     */
    public function destroy(Workspace $workspace, User $user)
    {
        // PRD 3 Section 23: Hanya pemilik workspace yang diizinkan mengeluarkan anggota
        if ($workspace->user_id !== auth()->id()) {
            abort(403, 'Hanya pemilik workspace yang dapat mengeluarkan anggota.');
        }

        $workspace->members()->detach($user->id);

        return back()->with('success', 'Anggota berhasil dikeluarkan dari workspace.');
    }
}
