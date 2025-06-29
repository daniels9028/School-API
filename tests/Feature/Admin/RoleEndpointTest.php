<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Factories\Admin\PermissionFactory;
use Database\Factories\Admin\RoleFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RoleEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permission and role
        Permission::create(['name' => 'manage roles']);
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo('manage roles');

        // Create and login user with token
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function can_list_roles(): void
    {
        $role = RoleFactory::new()->create();

        $response = $this->getJson('/api/roles');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['name' => $role->name]);
    }

    #[Test]
    public function can_create_role(): void
    {
        $roleName = 'role_' . fake()->unique()->word;

        $response = $this->postJson('/api/roles', [
            'name' => $roleName,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', $roleName);

        $this->assertDatabaseHas('roles', ['name' => $roleName]);
    }

    #[Test]
    public function can_update_role(): void
    {
        $role = RoleFactory::new()->create();
        $newName = 'updated_' . fake()->unique()->word;

        $response = $this->putJson("/api/roles/{$role->id}", [
            'name' => $newName,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', $newName);

        $this->assertDatabaseHas('roles', ['name' => $newName]);
    }

    #[Test]
    public function can_delete_role(): void
    {
        $role = RoleFactory::new()->create();

        $response = $this->deleteJson("/api/roles/{$role->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Role deleted successfully.',
                'data' => null,
            ]);

        $this->assertDatabaseMissing('roles', ['name' => $role->name]);
    }

    #[Test]
    public function can_assign_permissions_to_role(): void
    {
        $permissions = PermissionFactory::new()->count(3)->create();

        $role = Role::create(['name' => 'teacher', 'guard_name' => 'api']);

        $permissionNames = $permissions->pluck('name')->toArray();

        $response = $this->postJson("/api/roles/{$role->id}/permissions", [
            'permissions' => $permissionNames,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.role', 'teacher')
            ->assertJsonPath('data.permissions', $permissionNames);

        foreach ($permissionNames as $name) {
            $this->assertTrue($role->fresh()->hasPermissionTo($name));
        }
    }
}
