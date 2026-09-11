<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Store a newly created task in storage.
     */
    public function store(Request $request, Workspace $workspace): RedirectResponse
    {
        if (!$workspace->hasAccess(auth()->user())) {
            abort(403, 'Anda tidak memiliki hak akses ke workspace ini.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', 'in:penting,menyusul'],
            'due_date' => ['nullable', 'date'],
        ], [
            'title.required' => 'Judul tugas wajib diisi.',
            'title.max' => 'Judul tugas maksimal 255 karakter.',
            'description.max' => 'Deskripsi tugas maksimal 2000 karakter.',
            'priority.required' => 'Prioritas tugas wajib dipilih.',
            'priority.in' => 'Prioritas tugas harus berupa penting atau menyusul.',
        ]);

        $workspace->tasks()->create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'is_completed' => false,
        ]);

        return redirect()->route('workspaces.show', $workspace)->with('success', 'Tugas berhasil ditambahkan.');
    }

    /**
     * Toggle the completion status of the task.
     */
    public function toggleStatus(Workspace $workspace, Task $task): RedirectResponse
    {
        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        if (!$workspace->hasAccess(auth()->user())) {
            abort(403, 'Anda tidak memiliki hak akses ke workspace ini.');
        }

        $isCompleted = !$task->is_completed;
        $task->update([
            'is_completed' => $isCompleted,
            'completed_at' => $isCompleted ? now() : null,
        ]);

        return redirect()->route('workspaces.show', $workspace)->with('success', 'Status tugas berhasil diperbarui.');
    }

    /**
     * Show the form for editing the task.
     */
    public function edit(Workspace $workspace, Task $task): View
    {
        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        if (!$workspace->hasAccess(auth()->user())) {
            abort(403, 'Anda tidak memiliki hak akses ke workspace ini.');
        }

        return view('tasks.edit', compact('workspace', 'task'));
    }

    /**
     * Update the specified task in storage.
     */
    public function update(Request $request, Workspace $workspace, Task $task): RedirectResponse
    {
        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        if (!$workspace->hasAccess(auth()->user())) {
            abort(403, 'Anda tidak memiliki hak akses ke workspace ini.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', 'in:penting,menyusul'],
            'due_date' => ['nullable', 'date'],
        ], [
            'title.required' => 'Judul tugas wajib diisi.',
            'title.max' => 'Judul tugas maksimal 255 karakter.',
            'description.max' => 'Deskripsi tugas maksimal 2000 karakter.',
            'priority.required' => 'Prioritas tugas wajib dipilih.',
            'priority.in' => 'Prioritas tugas harus berupa penting atau menyusul.',
        ]);

        $task->update($validated);

        return redirect()->route('workspaces.show', $workspace)->with('success', 'Tugas berhasil diubah.');
    }

    /**
     * Remove the specified task from storage.
     */
    public function destroy(Workspace $workspace, Task $task): RedirectResponse
    {
        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        if (!$workspace->hasAccess(auth()->user())) {
            abort(403, 'Anda tidak memiliki hak akses ke workspace ini.');
        }

        $task->delete();

        return redirect()->route('workspaces.show', $workspace)->with('success', 'Tugas berhasil dihapus.');
    }
}
