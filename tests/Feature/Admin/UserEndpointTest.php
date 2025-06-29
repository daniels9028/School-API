<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UserEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup permission and role
        Permission::create(['name' => 'manage users']);
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'api']);
        $adminRole->givePermissionTo('manage users');

        // Create and login user with token
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function can_list_users(): void
    {
        $users = User::factory()->create();

        $response = $this->getJson('/api/users');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Users retrieved successfully')
            ->assertJsonFragment(['email' => $users->email]);;
    }

    #[Test]
    public function can_create_user(): void
    {
        $password = rand();

        $data = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'User created successfully')
            ->assertJsonPath('data.email', $data['email']);

        $this->assertDatabaseHas('users', ['email' => $data['email']]);
    }

    #[Test]
    public function can_update_user_with_password(): void
    {
        $targetUser = User::factory()->create();

        $password = rand();

        $data = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $response = $this->putJson("api/users/{$targetUser->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'User updated successfully')
            ->assertJsonPath('data.email', $data['email']);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => $data['name'],
            'email' => $data['email']
        ]);
    }

    #[Test]
    public function can_update_user_without_password(): void
    {
        $targetUser = User::factory()->create();

        $password = rand();

        $data = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
        ];

        $response = $this->putJson("api/users/{$targetUser->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'User updated successfully')
            ->assertJsonPath('data.email', $data['email']);

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => $data['name'],
            'email' => $data['email']
        ]);
    }

    #[Test]
    public function can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("api/users/{$user->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'User deleted successfully')
            ->assertJsonPath('data', null);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    #[Test]
    public function can_assign_role_to_user(): void
    {
        // Buat role tambahan
        $role1 = Role::create(['name' => fake()->name()]);

        // Buat target user
        $targetUser = User::factory()->create();

        // Assign role
        $response = $this->postJson("/api/users/{$targetUser->id}/roles", [
            'role' => $role1->name,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Role assigned to user.')
            ->assertJsonPath('data.user', $targetUser->email)
            ->assertJsonPath('data.role', $role1->name);

        // Pastikan di DB
        $this->assertTrue($targetUser->fresh()->hasRole($role1->name));
    }
}
