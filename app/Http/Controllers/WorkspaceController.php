<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $myWorkspaces = auth()->user()->ownedWorkspaces()->latest()->get();
        $sharedWorkspaces = auth()->user()->memberWorkspaces()->latest()->get();

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
     * Display the specified resource.
     */
    public function show(Workspace $workspace): View
    {
        if (!$workspace->hasAccess(auth()->user())) {
            abort(403, 'Anda tidak memiliki hak akses ke workspace ini.');
        }

        // Eager load workspace owner and members
        $workspace->load(['owner', 'members']);

        $existingMemberIds = $workspace->members->pluck('id')->push($workspace->user_id)->toArray();
        $availableUsers = User::whereNotIn('id', $existingMemberIds)->take(10)->get();

        // Eager load task creator and attachment uploaders to eliminate N+1 queries
        $status = request('status');
        $query = $workspace->tasks()->with(['creator', 'attachments.uploader'])->latest();

        if ($status === 'active') {
            $query->where('is_completed', false);
        } elseif ($status === 'completed') {
            $query->where('is_completed', true);
        } elseif ($status === 'penting') {
            $query->where('priority', 'penting');
        }

        $tasks = $query->get();

        // Single aggregation query for progress stats instead of 3 separate count queries
        $stats = $workspace->tasks()->selectRaw("
            COUNT(*) as total,
            COALESCE(SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END), 0) as completed,
            COALESCE(SUM(CASE WHEN priority = 'penting' THEN 1 ELSE 0 END), 0) as penting
        ")->first();

        $totalTasks = (int) ($stats->total ?? 0);
        $completedTasks = (int) ($stats->completed ?? 0);
        $pentingTasks = (int) ($stats->penting ?? 0);
        $progressPercentage = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

        return view('workspaces.show', compact(
            'workspace',
            'availableUsers',
            'tasks',
            'totalTasks',
            'completedTasks',
            'pentingTasks',
            'progressPercentage',
            'status'
        ));
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

        // Kumpulkan path file dari lampiran tugas workspace ini
        $filePaths = $workspace->tasks()
            ->with('attachments')
            ->get()
            ->flatMap(fn ($task) => $task->attachments->pluck('file_path'))
            ->filter()
            ->toArray();

        try {
            DB::transaction(function () use ($workspace) {
                $workspace->delete();
            });
        } catch (\Throwable $e) {
            return redirect()->route('workspaces.index')
                ->with('error', 'Gagal menghapus workspace. Silakan coba lagi.');
        }

        if (!empty($filePaths)) {
            Storage::disk('public')->delete($filePaths);
        }

        return redirect()->route('workspaces.index')
            ->with('success', 'Workspace berhasil dihapus.');
    }
}
