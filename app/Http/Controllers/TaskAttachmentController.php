<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    /**
     * Store a newly created attachment in storage.
     */
    public function store(Request $request, Workspace $workspace, Task $task)
    {
        if ($task->workspace_id !== $workspace->id) {
            abort(404);
        }

        $this->authorizeWorkspaceAccess($workspace, $task);

        $request->validate([
            'attachment' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,zip,jpg,jpeg,png',
                'max:10240', // Maksimal 10 MB (10240 KB)
            ],
        ], [
            'attachment.required' => 'Silakan pilih berkas yang ingin diunggah.',
            'attachment.file' => 'Berkas tidak valid.',
            'attachment.mimes' => 'Format berkas tidak didukung. Hanya diperbolehkan PDF, DOC, DOCX, ZIP, JPG, dan PNG.',
            'attachment.max' => 'Ukuran berkas tidak boleh melebihi 10MB.',
        ]);

        $file = $request->file('attachment');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getClientMimeType() ?: ($file->getMimeType() ?: 'application/octet-stream');

        // Store file with secure random hash name in storage/app/public/task-attachments
        $path = $file->store('task-attachments', 'public');

        try {
            DB::transaction(function () use ($task, $path, $originalName, $fileSize, $mimeType) {
                $task->attachments()->create([
                    'user_id' => auth()->id(),
                    'original_name' => $originalName,
                    'file_path' => $path,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                ]);
            });
        } catch (\Exception $e) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
            return back()->withErrors(['attachment' => 'Gagal menyimpan data lampiran ke database.']);
        }

        return back()->with('success', 'Berkas berhasil diunggah.');
    }

    /**
     * Download the specified attachment.
     */
    public function download(Workspace $workspace, Task $task, TaskAttachment $attachment)
    {
        if ($task->workspace_id !== $workspace->id || $attachment->task_id !== $task->id) {
            abort(404);
        }

        $this->authorizeWorkspaceAccess($workspace, $task);

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'Berkas fisik tidak ditemukan di server.');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(Workspace $workspace, Task $task, TaskAttachment $attachment)
    {
        if ($task->workspace_id !== $workspace->id || $attachment->task_id !== $task->id) {
            abort(404);
        }

        // Section 23: Penghapusan lampiran hanya untuk Pengunggah atau Pemilik Workspace
        if ($attachment->user_id !== auth()->id() && $workspace->user_id !== auth()->id()) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus berkas ini.');
        }

        DB::transaction(function () use ($attachment) {
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }
            $attachment->delete();
        });

        return back()->with('success', 'Berkas berhasil dihapus.');
    }

    /**
     * Helper to verify if user has access to workspace/task.
     */
    private function authorizeWorkspaceAccess(Workspace $workspace, Task $task): void
    {
        $isOwner = $workspace->user_id === auth()->id();
        $isTaskCreator = $task->user_id === auth()->id();
        $isMember = $workspace->members()->where('user_id', auth()->id())->exists();

        if (!$isOwner && !$isTaskCreator && !$isMember) {
            abort(403, 'Akses ditolak. Anda tidak memiliki akses ke workspace ini.');
        }
    }
}
