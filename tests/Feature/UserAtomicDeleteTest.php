<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserAtomicDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_cannot_delete_self(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $admin->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_admin_can_delete_user_atomically_with_file_cleanup(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'user']);
        $otherUser = User::factory()->create(['role' => 'user']);

        // 1. Target user's owned workspace and task with attachment
        $ownedWorkspace = Workspace::create([
            'user_id' => $targetUser->id,
            'name' => 'Target Owned Workspace',
        ]);
        $ownedTask = Task::create([
            'workspace_id' => $ownedWorkspace->id,
            'user_id' => $targetUser->id,
            'title' => 'Owned Task',
        ]);
        $ownedFile = UploadedFile::fake()->create('owned_doc.pdf', 500, 'application/pdf');
        $ownedPath = $ownedFile->store('task-attachments', 'public');
        $ownedAttachment = TaskAttachment::create([
            'task_id' => $ownedTask->id,
            'user_id' => $targetUser->id,
            'original_name' => 'owned_doc.pdf',
            'file_path' => $ownedPath,
            'file_size' => 500 * 1024,
            'mime_type' => 'application/pdf',
        ]);

        // 2. Other user's workspace where target user uploaded an attachment
        $otherWorkspace = Workspace::create([
            'user_id' => $otherUser->id,
            'name' => 'Other Workspace',
        ]);
        $otherTask = Task::create([
            'workspace_id' => $otherWorkspace->id,
            'user_id' => $otherUser->id,
            'title' => 'Other Task',
        ]);
        $uploadedFile = UploadedFile::fake()->create('uploaded_by_target.png', 300, 'image/png');
        $uploadedPath = $uploadedFile->store('task-attachments', 'public');
        $uploadedAttachment = TaskAttachment::create([
            'task_id' => $otherTask->id,
            'user_id' => $targetUser->id,
            'original_name' => 'uploaded_by_target.png',
            'file_path' => $uploadedPath,
            'file_size' => 300 * 1024,
            'mime_type' => 'image/png',
        ]);

        // Verify files exist in fake storage before deletion
        Storage::disk('public')->assertExists($ownedPath);
        Storage::disk('public')->assertExists($uploadedPath);

        // Perform deletion request by Admin
        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $targetUser->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'Pengguna berhasil dihapus.');

        // Database assertions (records removed via CASCADE)
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
        $this->assertDatabaseMissing('workspaces', ['id' => $ownedWorkspace->id]);
        $this->assertDatabaseMissing('tasks', ['id' => $ownedTask->id]);
        $this->assertDatabaseMissing('task_attachments', ['id' => $ownedAttachment->id]);
        $this->assertDatabaseMissing('task_attachments', ['id' => $uploadedAttachment->id]);

        // Physical file cleanup assertions
        Storage::disk('public')->assertMissing($ownedPath);
        Storage::disk('public')->assertMissing($uploadedPath);
    }

    public function test_admin_can_delete_user_without_attachments(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $targetUser->id));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success', 'Pengguna berhasil dihapus.');
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }
}
