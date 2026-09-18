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

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_workspace_detail_or_tasks(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Rahasia',
        ]);

        $response = $this->get("/workspaces/{$workspace->id}");
        $response->assertRedirect('/login');

        $response = $this->post("/workspaces/{$workspace->id}/tasks", [
            'title' => 'Tugas Baru',
            'priority' => 'menyusul',
        ]);
        $response->assertRedirect('/login');
    }

    public function test_unauthorized_user_cannot_view_workspace_or_manage_tasks(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Pribadi',
        ]);

        $task = $workspace->tasks()->create([
            'user_id' => $owner->id,
            'title' => 'Tugas Pribadi',
            'priority' => 'menyusul',
        ]);

        // Stranger cannot view workspace
        $this->actingAs($stranger)->get("/workspaces/{$workspace->id}")
            ->assertStatus(403);

        // Stranger cannot add task
        $this->actingAs($stranger)->post("/workspaces/{$workspace->id}/tasks", [
            'title' => 'Tugas Ilegal',
            'priority' => 'menyusul',
        ])->assertStatus(403);

        // Stranger cannot toggle status
        $this->actingAs($stranger)->patch("/workspaces/{$workspace->id}/tasks/{$task->id}/toggle-status")
            ->assertStatus(403);

        // Stranger cannot delete task
        $this->actingAs($stranger)->delete("/workspaces/{$workspace->id}/tasks/{$task->id}")
            ->assertStatus(403);
    }

    public function test_owner_can_view_workspace_with_empty_tasks_and_zero_progress(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Projek Skripsi',
        ]);

        $response = $this->actingAs($owner)->get("/workspaces/{$workspace->id}");

        $response->assertStatus(200);
        $response->assertSee('Projek Skripsi');
        $response->assertSee('PROGRES TUGAS WORKSPACE');
        $response->assertSee('0%');
        $response->assertSee('Belum Ada Tugas di Workspace Ini');
    }

    public function test_owner_can_create_task_with_valid_data(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Projek Laravel',
        ]);

        $response = $this->actingAs($owner)->post("/workspaces/{$workspace->id}/tasks", [
            'title' => 'Membuat ERD Database',
            'description' => 'Gunakan MySQL Workbench',
            'priority' => 'penting',
            'due_date' => '2026-09-15 18:00:00',
        ]);

        $response->assertRedirect("/workspaces/{$workspace->id}");
        $response->assertSessionHas('success', 'Tugas berhasil ditambahkan.');

        $this->assertDatabaseHas('tasks', [
            'workspace_id' => $workspace->id,
            'user_id' => $owner->id,
            'title' => 'Membuat ERD Database',
            'description' => 'Gunakan MySQL Workbench',
            'priority' => 'penting',
            'is_completed' => false,
        ]);
    }

    public function test_task_creation_validates_required_fields_and_rules(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Projek Validasi',
        ]);

        // Missing title
        $response = $this->actingAs($owner)->post("/workspaces/{$workspace->id}/tasks", [
            'title' => '',
            'priority' => 'penting',
        ]);
        $response->assertSessionHasErrors('title');

        // Invalid priority
        $response = $this->actingAs($owner)->post("/workspaces/{$workspace->id}/tasks", [
            'title' => 'Judul Valid',
            'priority' => 'urgent_sekali',
        ]);
        $response->assertSessionHasErrors('priority');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_user_can_toggle_task_status_and_update_progress(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Projek Progres',
        ]);

        $task = $workspace->tasks()->create([
            'user_id' => $owner->id,
            'title' => 'Tugas 1',
            'priority' => 'penting',
            'is_completed' => false,
        ]);

        // Toggle to completed
        $response = $this->actingAs($owner)->patch("/workspaces/{$workspace->id}/tasks/{$task->id}/toggle-status");
        $response->assertRedirect("/workspaces/{$workspace->id}");
        $response->assertSessionHas('success', 'Status tugas berhasil diperbarui.');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_completed' => true,
        ]);
        $this->assertNotNull($task->fresh()->completed_at);

        // Check progress 100% on page
        $page = $this->actingAs($owner)->get("/workspaces/{$workspace->id}");
        $page->assertSee('100%');
        $page->assertSee('(1 dari 1 selesai)');

        // Toggle back to incomplete
        $this->actingAs($owner)->patch("/workspaces/{$workspace->id}/tasks/{$task->id}/toggle-status");
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_completed' => false,
            'completed_at' => null,
        ]);
    }

    public function test_user_can_edit_and_update_task(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Projek Edit',
        ]);

        $task = $workspace->tasks()->create([
            'user_id' => $owner->id,
            'title' => 'Judul Awal',
            'priority' => 'menyusul',
        ]);

        // Show edit form
        $this->actingAs($owner)->get("/workspaces/{$workspace->id}/tasks/{$task->id}/edit")
            ->assertStatus(200)
            ->assertSee('Edit Tugas')
            ->assertSee('Judul Awal');

        // Update task
        $response = $this->actingAs($owner)->put("/workspaces/{$workspace->id}/tasks/{$task->id}", [
            'title' => 'Judul Diperbarui',
            'description' => 'Deskripsi baru',
            'priority' => 'penting',
        ]);

        $response->assertRedirect("/workspaces/{$workspace->id}");
        $response->assertSessionHas('success', 'Tugas berhasil diubah.');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Judul Diperbarui',
            'description' => 'Deskripsi baru',
            'priority' => 'penting',
        ]);
    }

    public function test_user_can_delete_task(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Projek Hapus',
        ]);

        $task = $workspace->tasks()->create([
            'user_id' => $owner->id,
            'title' => 'Tugas Yang Akan Dihapus',
            'priority' => 'menyusul',
        ]);

        $response = $this->actingAs($owner)->delete("/workspaces/{$workspace->id}/tasks/{$task->id}");

        $response->assertRedirect("/workspaces/{$workspace->id}");
        $response->assertSessionHas('success', 'Tugas berhasil dihapus.');

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_user_can_filter_tasks_by_status(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Projek Filter',
        ]);

        $activeTask = $workspace->tasks()->create([
            'user_id' => $owner->id,
            'title' => 'Tugas Masih Aktif',
            'priority' => 'menyusul',
            'is_completed' => false,
        ]);

        $completedTask = $workspace->tasks()->create([
            'user_id' => $owner->id,
            'title' => 'Tugas Sudah Selesai',
            'priority' => 'penting',
            'is_completed' => true,
            'completed_at' => now(),
        ]);

        // Filter active
        $response = $this->actingAs($owner)->get("/workspaces/{$workspace->id}?status=active");
        $response->assertSee('Tugas Masih Aktif');
        $response->assertDontSee('Tugas Sudah Selesai');

        // Filter completed
        $response = $this->actingAs($owner)->get("/workspaces/{$workspace->id}?status=completed");
        $response->assertSee('Tugas Sudah Selesai');
        $response->assertDontSee('Tugas Masih Aktif');
    }

    public function test_task_deletion_removes_physical_attachment_files(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Projek Hapus File',
        ]);

        $task = $workspace->tasks()->create([
            'user_id' => $owner->id,
            'title' => 'Tugas dengan Lampiran',
            'priority' => 'penting',
        ]);

        $file1 = UploadedFile::fake()->create('dokumen1.pdf', 100, 'application/pdf');
        $path1 = $file1->store('task-attachments', 'public');

        $file2 = UploadedFile::fake()->create('dokumen2.png', 200, 'image/png');
        $path2 = $file2->store('task-attachments', 'public');

        $task->attachments()->create([
            'user_id' => $owner->id,
            'original_name' => 'dokumen1.pdf',
            'file_path' => $path1,
            'file_size' => 100 * 1024,
            'mime_type' => 'application/pdf',
        ]);

        $task->attachments()->create([
            'user_id' => $owner->id,
            'original_name' => 'dokumen2.png',
            'file_path' => $path2,
            'file_size' => 200 * 1024,
            'mime_type' => 'image/png',
        ]);

        Storage::disk('public')->assertExists($path1);
        Storage::disk('public')->assertExists($path2);

        $response = $this->actingAs($owner)->delete("/workspaces/{$workspace->id}/tasks/{$task->id}");

        $response->assertRedirect("/workspaces/{$workspace->id}");
        $response->assertSessionHas('success', 'Tugas berhasil dihapus.');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseMissing('task_attachments', ['file_path' => $path1]);
        $this->assertDatabaseMissing('task_attachments', ['file_path' => $path2]);

        Storage::disk('public')->assertMissing($path1);
        Storage::disk('public')->assertMissing($path2);
    }

    public function test_task_deletion_rolls_back_and_preserves_files_when_database_fails(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Projek Rollback',
        ]);

        $task = $workspace->tasks()->create([
            'user_id' => $owner->id,
            'title' => 'Tugas yang Gagal Dihapus',
            'priority' => 'penting',
        ]);

        $file = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');
        $path = $file->store('task-attachments', 'public');

        $attachment = $task->attachments()->create([
            'user_id' => $owner->id,
            'original_name' => 'dokumen.pdf',
            'file_path' => $path,
            'file_size' => 100 * 1024,
            'mime_type' => 'application/pdf',
        ]);

        // Simulasikan kegagalan transaksi database dengan model event
        Task::deleting(function () {
            throw new \Exception('Database query failure during deletion');
        });

        $response = $this->actingAs($owner)->delete("/workspaces/{$workspace->id}/tasks/{$task->id}");

        $response->assertRedirect("/workspaces/{$workspace->id}");
        $response->assertSessionHas('error', 'Gagal menghapus tugas. Silakan coba lagi.');

        // Data di database dan file di disk harus tetap ada
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
        $this->assertDatabaseHas('task_attachments', ['id' => $attachment->id]);
        Storage::disk('public')->assertExists($path);
    }
}
