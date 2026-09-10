<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $myWorkspaces = auth()->user()->ownedWorkspaces()->latest()->get();
        $sharedWorkspaces = Schema::hasTable('workspace_members')
            ? auth()->user()->memberWorkspaces()->latest()->get()
            : collect();

        return view('workspaces.index', compact('myWorkspaces', 'sharedWorkspaces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('workspaces.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama workspace wajib diisi.',
            'name.max' => 'Nama workspace maksimal 100 karakter.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ]);

        auth()->user()->workspaces()->create($validated);

        return redirect()->route('workspaces.index')->with('success', 'Workspace berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Workspace $workspace): View
    {
        if ($workspace->user_id !== auth()->id()) {
            abort(403, 'Hanya pemilik yang dapat mengubah atau menghapus workspace ini.');
        }

        return view('workspaces.edit', compact('workspace'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Workspace $workspace): RedirectResponse
    {
        if ($workspace->user_id !== auth()->id()) {
            abort(403, 'Hanya pemilik yang dapat mengubah atau menghapus workspace ini.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Nama workspace wajib diisi.',
            'name.max' => 'Nama workspace maksimal 100 karakter.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ]);

        $workspace->update($validated);

        return redirect()->route('workspaces.index')->with('success', 'Workspace berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workspace $workspace): RedirectResponse
    {
        if ($workspace->user_id !== auth()->id()) {
            abort(403, 'Hanya pemilik yang dapat mengubah atau menghapus workspace ini.');
        }

        $workspace->delete();

        return redirect()->route('workspaces.index')->with('success', 'Workspace berhasil dihapus.');
    }
}
