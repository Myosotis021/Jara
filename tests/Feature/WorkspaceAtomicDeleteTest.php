<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkspaceAtomicDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_delete_workspace_and_clean_up_attachments_and_files(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Dengan File',
        ]);

        $task = Task::create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'title' => 'Tugas Lampiran',
            'priority' => 'penting',
        ]);

        $file = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');
        $storedPath = $file->store('task-attachments', 'public');

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $owner->id,
            'original_name' => 'dokumen.pdf',
            'file_path' => $storedPath,
            'file_size' => 102400,
            'mime_type' => 'application/pdf',
        ]);

        Storage::disk('public')->assertExists($storedPath);

        $response = $this->actingAs($owner)->delete("/workspaces/{$workspace->id}");

        $response->assertRedirect('/workspaces');
        $response->assertSessionHas('success', 'Workspace berhasil dihapus.');

        // Database assertions (cascade)
        $this->assertDatabaseMissing('workspaces', ['id' => $workspace->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseMissing('task_attachments', ['id' => $attachment->id]);

        // Storage file assertion (no orphaned files)
        Storage::disk('public')->assertMissing($storedPath);
    }

    public function test_non_owner_cannot_delete_workspace(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Milik Orang Lain',
        ]);

        $task = Task::create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'title' => 'Tugas Rahasia',
            'priority' => 'penting',
        ]);

        $file = UploadedFile::fake()->create('rahasia.pdf', 50, 'application/pdf');
        $storedPath = $file->store('task-attachments', 'public');

        TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $owner->id,
            'original_name' => 'rahasia.pdf',
            'file_path' => $storedPath,
            'file_size' => 51200,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($stranger)->delete("/workspaces/{$workspace->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('workspaces', ['id' => $workspace->id]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_atomic_rollback_and_error_message_when_deletion_fails(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Rollback Test',
        ]);

        $task = Task::create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'title' => 'Tugas Rollback',
            'priority' => 'menyusul',
        ]);

        $file = UploadedFile::fake()->create('laporan.pdf', 80, 'application/pdf');
        $storedPath = $file->store('task-attachments', 'public');

        TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $owner->id,
            'original_name' => 'laporan.pdf',
            'file_path' => $storedPath,
            'file_size' => 81920,
            'mime_type' => 'application/pdf',
        ]);

        // Simulasikan database error saat event deleting Workspace
        Workspace::deleting(function () {
            throw new \Exception('Simulasi kegagalan database query');
        });

        $response = $this->actingAs($owner)->delete("/workspaces/{$workspace->id}");

        $response->assertRedirect('/workspaces');
        $response->assertSessionHas('error', 'Gagal menghapus workspace. Silakan coba lagi.');

        // Data tidak boleh terhapus (rollback)
        $this->assertDatabaseHas('workspaces', ['id' => $workspace->id]);
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);

        // File fisik juga tidak boleh terhapus
        Storage::disk('public')->assertExists($storedPath);
    }
}
