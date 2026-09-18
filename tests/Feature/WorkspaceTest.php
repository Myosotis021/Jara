<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/workspaces');
        $response->assertRedirect('/login');

        $response = $this->get('/workspaces/create');
        $response->assertRedirect('/login');

        $response = $this->post('/workspaces', ['name' => 'Workspace Baru']);
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_workspace_index_with_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/workspaces');

        $response->assertStatus(200);
        $response->assertSee('Workspace & Daftar Tugas Saya', false);
        $response->assertSee('Belum Ada Workspace Dibuat');
    }

    public function test_authenticated_user_can_view_workspace_create_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/workspaces/create');

        $response->assertStatus(200);
        $response->assertSee('Buat Workspace Baru');
        $response->assertSee('Simpan Workspace');
    }

    public function test_user_can_create_workspace_with_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/workspaces', [
            'name' => 'Tugas Kuliah Semester 4',
            'description' => 'Kumpulan tugas mingguan.',
        ]);

        $response->assertRedirect('/workspaces');
        $response->assertSessionHas('success', 'Workspace berhasil dibuat.');

        $this->assertDatabaseHas('workspaces', [
            'user_id' => $user->id,
            'name' => 'Tugas Kuliah Semester 4',
            'description' => 'Kumpulan tugas mingguan.',
        ]);
    }

    public function test_workspace_creation_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/workspaces', [
            'name' => '',
            'description' => 'Tanpa nama',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('workspaces', 0);
    }

    public function test_workspace_creation_validates_max_length(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/workspaces', [
            'name' => str_repeat('A', 101),
            'description' => str_repeat('B', 501),
        ]);

        $response->assertSessionHasErrors(['name', 'description']);
        $this->assertDatabaseCount('workspaces', 0);
    }

    public function test_owner_can_view_workspace_edit_form(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $user->id,
            'name' => 'Projek Akhir',
            'description' => 'Deskripsi lama',
        ]);

        $response = $this->actingAs($user)->get("/workspaces/{$workspace->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Workspace');
        $response->assertSee('Hapus Workspace');
        $response->assertSee('Projek Akhir');
    }

    public function test_non_owner_cannot_view_workspace_edit_form(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Projek Rahasia',
        ]);

        $response = $this->actingAs($stranger)->get("/workspaces/{$workspace->id}/edit");

        $response->assertStatus(403);
    }

    public function test_owner_can_update_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $user->id,
            'name' => 'Projek Lama',
            'description' => 'Deskripsi lama',
        ]);

        $response = $this->actingAs($user)->put("/workspaces/{$workspace->id}", [
            'name' => 'Projek Baru Diperbarui',
            'description' => 'Deskripsi baru',
        ]);

        $response->assertRedirect('/workspaces');
        $response->assertSessionHas('success', 'Workspace berhasil diperbarui.');

        $this->assertDatabaseHas('workspaces', [
            'id' => $workspace->id,
            'name' => 'Projek Baru Diperbarui',
            'description' => 'Deskripsi baru',
        ]);
    }

    public function test_non_owner_cannot_update_workspace(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($stranger)->put("/workspaces/{$workspace->id}", [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('workspaces', [
            'id' => $workspace->id,
            'name' => 'Original Name',
        ]);
    }

    public function test_owner_can_delete_workspace(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $user->id,
            'name' => 'Akan Dihapus',
        ]);

        $response = $this->actingAs($user)->delete("/workspaces/{$workspace->id}");

        $response->assertRedirect('/workspaces');
        $response->assertSessionHas('success', 'Workspace berhasil dihapus.');

        $this->assertDatabaseMissing('workspaces', [
            'id' => $workspace->id,
        ]);
    }

    public function test_non_owner_cannot_delete_workspace(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Penting',
        ]);

        $response = $this->actingAs($stranger)->delete("/workspaces/{$workspace->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('workspaces', [
            'id' => $workspace->id,
        ]);
    }
}
