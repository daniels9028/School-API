<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Factories\Admin\PermissionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PermissionEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permission and role
        Permission::create(['name' => 'manage permissions']);
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo('manage permissions');

        // Create and login user with token
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function can_list_permissions(): void
    {
        $permission = PermissionFactory::new()->create();

        $response = $this->getJson('/api/permissions');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['name' => $permission->name]);
    }

    #[Test]
    public function can_create_permission(): void
    {
        $permissionName = 'permission_' . fake()->unique()->word;

        $response = $this->postJson('/api/permissions', [
            'name' => $permissionName
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', $permissionName);

        $this->assertDatabaseHas('permissions', ['name' => $permissionName]);
    }

    #[Test]
    public function can_update_permission(): void
    {
        $permission = PermissionFactory::new()->create();
        $newName = 'updated_' . fake()->unique()->word;

        $response = $this->putJson("/api/permissions/{$permission->id}", [
            'name' => $newName,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', $newName);

        $this->assertDatabaseHas('permissions', ['name' => $newName]);
    }

    #[Test]
    public function can_delete_permission(): void
    {
        $permission = PermissionFactory::new()->create();

        $response = $this->deleteJson("/api/permissions/{$permission->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Permission deleted successfully.',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('permissions', ['name' => $permission->name]);
    }
}
