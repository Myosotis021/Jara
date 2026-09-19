<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkspaceMemberController extends Controller
{
    /**
     * Search available users by email or name from database.
     */
    public function search(Request $request, Workspace $workspace)
    {
        if ($workspace->user_id !== auth()->id()) {
            abort(403, 'Hanya pemilik workspace yang dapat mencari calon anggota.');
        }

        $query = trim($request->input('q', ''));
        $existingMemberIds = $workspace->members->pluck('id')->push($workspace->user_id)->toArray();

        $usersQuery = User::whereNotIn('id', $existingMemberIds);

        if ($query !== '') {
            $usersQuery->where(function ($q) use ($query) {
                $q->where('email', 'like', "%{$query}%")
                  ->orWhere('name', 'like', "%{$query}%");
            });
        }

        $users = $usersQuery->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'initials' => strtoupper(substr($user->name, 0, 2)),
                ];
            }),
        ]);
    }

    /**
     * Store a newly added member in workspace_members.
     */
    public function store(Request $request, Workspace $workspace)
    {
        // PRD 3 Section 23: Hanya pemilik workspace yang diizinkan mengundang anggota
        if ($workspace->user_id !== auth()->id()) {
            abort(403, 'Hanya pemilik workspace yang dapat mengundang anggota.');
        }

        // Dukungan pencarian dan penambahan berdasarkan email
        if ($request->filled('email') && !$request->filled('user_id')) {
            $matchedUser = User::where('email', trim($request->email))->first();
            if ($matchedUser) {
                $request->merge(['user_id' => $matchedUser->id]);
            } else {
                return back()->withErrors(['email' => 'Pengguna dengan email tersebut tidak ditemukan di database.'])
                    ->withInput();
            }
        }

        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                Rule::notIn([$workspace->user_id]), // Tidak boleh mengundang diri sendiri (pemilik)
            ],
        ], [
            'user_id.required' => 'Silakan pilih atau masukkan email pengguna yang ingin diundang.',
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
