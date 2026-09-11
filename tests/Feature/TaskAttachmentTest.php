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

class TaskAttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_user_can_upload_valid_attachment_to_task(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $user->id,
            'name' => 'Workspace Test',
        ]);
        $task = Task::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'title' => 'Tugas Praktikum PDF',
        ]);

        $file = UploadedFile::fake()->create('laporan.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($user)->post(route('workspaces.tasks.attachments.store', [$workspace->id, $task->id]), [
            'attachment' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Berkas berhasil diunggah.');

        $this->assertDatabaseHas('task_attachments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'original_name' => 'laporan.pdf',
            'mime_type' => 'application/pdf',
        ]);

        $attachment = TaskAttachment::first();
        Storage::disk('public')->assertExists($attachment->file_path);
    }

    public function test_upload_fails_when_file_exceeds_10mb(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $user->id,
            'name' => 'Workspace Test',
        ]);
        $task = Task::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'title' => 'Tugas Large File',
        ]);

        // 10241 KB > 10240 KB (10MB limit)
        $file = UploadedFile::fake()->create('heavy_file.zip', 10241, 'application/zip');

        $response = $this->actingAs($user)->post(route('workspaces.tasks.attachments.store', [$workspace->id, $task->id]), [
            'attachment' => $file,
        ]);

        $response->assertSessionHasErrors('attachment');
        $this->assertDatabaseCount('task_attachments', 0);
    }

    public function test_upload_fails_when_file_type_is_invalid(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $user->id,
            'name' => 'Workspace Test',
        ]);
        $task = Task::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'title' => 'Tugas Script Executable',
        ]);

        $file = UploadedFile::fake()->create('exploit.exe', 500, 'application/x-msdownload');

        $response = $this->actingAs($user)->post(route('workspaces.tasks.attachments.store', [$workspace->id, $task->id]), [
            'attachment' => $file,
        ]);

        $response->assertSessionHasErrors('attachment');
        $this->assertDatabaseCount('task_attachments', 0);
    }

    public function test_authorized_user_can_download_attachment(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $user->id,
            'name' => 'Workspace Test',
        ]);
        $task = Task::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'title' => 'Tugas Download Test',
        ]);

        $file = UploadedFile::fake()->create('dokumen.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $path = $file->store('task-attachments', 'public');

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'original_name' => 'dokumen.docx',
            'file_path' => $path,
            'file_size' => 500 * 1024,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);

        $response = $this->actingAs($user)->get(route('workspaces.tasks.attachments.download', [$workspace->id, $task->id, $attachment->id]));

        $response->assertOk();
        $response->assertHeader('content-disposition', 'attachment; filename=dokumen.docx');
    }

    public function test_unauthorized_user_cannot_download_attachment(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Private Workspace',
        ]);
        $task = Task::create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'title' => 'Private Task',
        ]);

        $file = UploadedFile::fake()->create('secret.pdf', 300, 'application/pdf');
        $path = $file->store('task-attachments', 'public');

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $owner->id,
            'original_name' => 'secret.pdf',
            'file_path' => $path,
            'file_size' => 300 * 1024,
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($stranger)->get(route('workspaces.tasks.attachments.download', [$workspace->id, $task->id, $attachment->id]));

        $response->assertStatus(403);
    }

    public function test_uploader_or_workspace_owner_can_delete_attachment(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Delete Test',
        ]);
        $task = Task::create([
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'title' => 'Task Delete Test',
        ]);

        $file = UploadedFile::fake()->create('image.png', 400, 'image/png');
        $path = $file->store('task-attachments', 'public');

        $attachment = TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => $owner->id,
            'original_name' => 'image.png',
            'file_path' => $path,
            'file_size' => 400 * 1024,
            'mime_type' => 'image/png',
        ]);

        $response = $this->actingAs($owner)->delete(route('workspaces.tasks.attachments.destroy', [$workspace->id, $task->id, $attachment->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Berkas berhasil dihapus.');

        $this->assertDatabaseMissing('task_attachments', [
            'id' => $attachment->id,
        ]);
        Storage::disk('public')->assertMissing($path);
    }
}
