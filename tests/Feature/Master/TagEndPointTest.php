<?php

namespace Tests\Feature\Master;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Str;

class TagEndPointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'manage tags']);

        $adminRole = Role::create(['name' => 'admin', 'guard' => 'api']);

        $adminRole->givePermissionTo('manage tags');

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function can_get_all_tags(): void
    {
        $tags = Tag::factory()->count(5)->create();

        $response = $this->getJson("api/tags");

        $response->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tags retrieved successfully')
            ->assertJsonCount(5, 'data');

        // Assert that each created tags exists in the response
        foreach ($tags as $tag) {
            $response->assertJsonFragment([
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ]);
        }

        // Optionally: check response structure (for clarity & maintenance)
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [ // each item
                    'id',
                    'name',
                    'slug'
                ],
            ],
        ]);
    }

    #[Test]
    public function can_store_tag(): void
    {
        $name = fake()->unique()->sentence();

        $data = [
            'name' => $name
        ];

        $response = $this->postJson("api/tags", $data);

        $response->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tag created successfully')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'slug',
                ],
            ]);

        // Assert data in database
        $this->assertDatabaseHas('tags', [
            'name' => $name,
            'slug' => Str::slug($name)
        ]);
    }

    #[Test]
    public function can_update_tag(): void
    {
        $tag = Tag::factory()->create();

        $name = fake()->unique()->sentence();

        $data = [
            'name' => $name
        ];

        $response = $this->putJson("api/tags/{$tag->id}", $data);

        $response->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tag updated successfully')
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'slug',
                ],
            ]);

        // Assert data in database
        $this->assertDatabaseHas('tags', [
            'name' => $name,
            'slug' => Str::slug($name)
        ]);
    }

    #[Test]
    public function can_delete_tag(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson("api/tags/{$tag->id}");

        $response->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Tag deleted successfully')
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);

        // Assert data in database
        $this->assertDatabaseMissing('tags', [
            'name' => $tag->name,
            'slug' => $tag->slug
        ]);
    }
}
