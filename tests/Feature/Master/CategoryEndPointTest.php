<?php

namespace Tests\Feature\Master;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Str;

class CategoryEndPointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'manage categories']);

        $adminRole = Role::create(['name' => 'admin', 'guard' => 'api']);

        $adminRole->givePermissionTo('manage categories');

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function can_get_all_categories(): void
    {
        $categories = Category::factory()->count(5)->create();

        $response = $this->getJson("api/categories");

        $response->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Categories retrieved successfully')
            ->assertJsonCount(5, 'data');

        // Assert that each created category exists in the response
        foreach ($categories as $category) {
            $response->assertJsonFragment([
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
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
    public function can_store_category(): void
    {
        $name = fake()->unique()->sentence();

        $data = [
            'name' => $name
        ];

        $response = $this->postJson("api/categories", $data);

        $response->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Category created successfully')
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
        $this->assertDatabaseHas('categories', [
            'name' => $name,
            'slug' => Str::slug($name)
        ]);
    }

    #[Test]
    public function can_update_category(): void
    {
        $category = Category::factory()->create();

        $name = fake()->unique()->sentence();

        $data = [
            'name' => $name
        ];

        $response = $this->putJson("api/categories/{$category->id}", $data);

        $response->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Category updated successfully')
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
        $this->assertDatabaseHas('categories', [
            'name' => $name,
            'slug' => Str::slug($name)
        ]);
    }

    #[Test]
    public function can_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->deleteJson("api/categories/{$category->id}");

        $response->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Category deleted successfully')
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);

        // Assert data in database
        $this->assertDatabaseMissing('categories', [
            'name' => $category->name,
            'slug' => $category->slug
        ]);
    }
}
