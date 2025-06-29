<?php

namespace Tests\Feature\Master;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LessonEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'manage lessons']);
        $adminRole = Role::create(['name' => 'admin', 'guard' => 'api']);

        $adminRole->givePermissionTo('manage lessons');

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function can_list_lessons(): void
    {
        $course = Course::factory()->create();

        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $response = $this->getJson("api/courses/{$course->id}/lessons");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Lesson retrieved successfully')
            ->assertJsonFragment(['title' => $lesson->title]);
    }

    #[Test]
    public function can_create_lesson_with_content(): void
    {
        $course = Course::factory()->create();

        $data = [
            'title' => fake()->sentence(),
            'content' => fake()->paragraph()
        ];

        $response = $this->postJson("api/courses/{$course->id}/lessons", $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Lesson created successfully')
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.content', $data['content']);

        $this->assertDatabaseHas('lessons', [
            'title' => $data['title'],
            'content' => $data['content'],
            'course_id' => $course->id,
            'created_by' => $this->user->id
        ]);
    }

    #[Test]
    public function can_create_lesson_without_content(): void
    {
        $course = Course::factory()->create();

        $data = [
            'title' => fake()->sentence(),
            'content' => null
        ];

        $response = $this->postJson("api/courses/{$course->id}/lessons", $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Lesson created successfully')
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.content', $data['content']);

        $this->assertDatabaseHas('lessons', [
            'title' => $data['title'],
            'content' => $data['content'],
            'course_id' => $course->id,
            'created_by' => $this->user->id
        ]);
    }

    #[Test]
    public function can_update_lesson_with_content(): void
    {
        $course = Course::factory()->create();

        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $data = [
            'title' => fake()->sentence(),
            'content' => fake()->paragraph()
        ];

        $response = $this->putJson("api/lessons/{$lesson->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Lesson updated successfully')
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.content', $data['content'])
            ->assertJsonPath('data.course_id', $course->id);

        $this->assertDatabaseHas('lessons', [
            'title' => $data['title'],
            'content' => $data['content'],
            'course_id' => $course->id,
        ]);
    }

    #[Test]
    public function can_update_lesson_without_content(): void
    {
        $course = Course::factory()->create();

        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $data = [
            'title' => fake()->sentence(),
            'content' => null
        ];

        $response = $this->putJson("api/lessons/{$lesson->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Lesson updated successfully')
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.content', $data['content'])
            ->assertJsonPath('data.course_id', $course->id);

        $this->assertDatabaseHas('lessons', [
            'title' => $data['title'],
            'content' => $data['content'],
            'course_id' => $course->id,
        ]);
    }

    #[Test]
    public function can_delete_lesson(): void
    {
        $course = Course::factory()->create();

        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $response = $this->deleteJson("api/lessons/{$lesson->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Lesson deleted successfully')
            ->assertJsonPath('data', null);

        $this->assertDatabaseMissing('lessons', [
            'course_id' => $course->id,
            'title' => $lesson->id,
            'content' => $lesson->content
        ]);
    }
}
