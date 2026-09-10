<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_invite_member_to_workspace(): void
    {
        $owner = User::factory()->create(['name' => 'Owner']);
        $candidate = User::factory()->create(['name' => 'Siti']);

        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Proyek Web',
        ]);

        $response = $this->actingAs($owner)->post(route('workspaces.members.store', $workspace->id), [
            'user_id' => $candidate->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $candidate->id,
        ]);
    }

    public function test_cannot_invite_owner_to_own_workspace(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Owner',
        ]);

        $response = $this->actingAs($owner)->post(route('workspaces.members.store', $workspace->id), [
            'user_id' => $owner->id,
        ]);

        $response->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('workspace_members', 0);
    }

    public function test_cannot_invite_duplicate_member(): void
    {
        $owner = User::factory()->create();
        $candidate = User::factory()->create();

        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Proyek',
        ]);

        $workspace->members()->attach($candidate->id);

        $response = $this->actingAs($owner)->post(route('workspaces.members.store', $workspace->id), [
            'user_id' => $candidate->id,
        ]);

        $response->assertSessionHasErrors('user_id');
        $this->assertDatabaseCount('workspace_members', 1);
    }

    public function test_owner_can_remove_member_from_workspace(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Proyek',
        ]);

        $workspace->members()->attach($member->id);

        $response = $this->actingAs($owner)->delete(route('workspaces.members.destroy', [$workspace->id, $member->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Anggota berhasil dikeluarkan dari workspace.');

        $this->assertDatabaseMissing('workspace_members', [
            'workspace_id' => $workspace->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_non_owner_cannot_invite_members(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $stranger = User::factory()->create();

        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Proyek',
        ]);

        $workspace->members()->attach($member->id);

        // Anggota biasa mencoba mengundang pengguna lain
        $response = $this->actingAs($member)->post(route('workspaces.members.store', $workspace->id), [
            'user_id' => $stranger->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_invited_member_can_access_workspace(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $workspace = Workspace::create([
            'user_id' => $owner->id,
            'name' => 'Workspace Kolaborasi',
        ]);

        $workspace->members()->attach($member->id);

        $response = $this->actingAs($member)->get(route('workspaces.show', $workspace->id));

        $response->assertOk();
    }
}
