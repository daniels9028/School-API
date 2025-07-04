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
    public function can_create_lesson_with_content_video_url_and_order(): void
    {
        $course = Course::factory()->create();

        $data = [
            'title' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'video_url' => fake()->url(),
            'order' => fake()->numberBetween(0, 100),
        ];

        $response = $this->postJson("api/courses/{$course->id}/lessons", $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Lesson created successfully')
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.content', $data['content'])
            ->assertJsonPath('data.video_url', $data['video_url'])
            ->assertJsonPath('data.order', $data['order']);

        $this->assertDatabaseHas('lessons', [
            'title' => $data['title'],
            'content' => $data['content'],
            'video_url' => $data['video_url'],
            'order' => $data['order'],
            'course_id' => $course->id,
            'created_by' => $this->user->id
        ]);
    }

    #[Test]
    public function can_create_lesson_without_content_video_url_and_order(): void
    {
        $course = Course::factory()->create();

        $data = [
            'title' => fake()->sentence(),
        ];

        $response = $this->postJson("api/courses/{$course->id}/lessons", $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Lesson created successfully')
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.content', null)
            ->assertJsonPath('data.video_url', null)
            ->assertJsonPath('data.order', null);

        $this->assertDatabaseHas('lessons', [
            'title' => $data['title'],
            'content' => null,
            'video_url' => null,
            'order' => 0,
            'course_id' => $course->id,
            'created_by' => $this->user->id
        ]);
    }

    #[Test]
    public function can_update_lesson_with_content_video_url_and_order(): void
    {
        $course = Course::factory()->create();

        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $data = [
            'title' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'video_url' => fake()->url(),
            'order' => fake()->numberBetween(0, 100),
        ];

        $response = $this->putJson("api/lessons/{$lesson->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Lesson updated successfully')
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.content', $data['content'])
            ->assertJsonPath('data.video_url', $data['video_url'])
            ->assertJsonPath('data.order', $data['order'])
            ->assertJsonPath('data.course_id', $course->id);

        $this->assertDatabaseHas('lessons', [
            'title' => $data['title'],
            'content' => $data['content'],
            'video_url' => $data['video_url'],
            'order' => $data['order'],
            'course_id' => $course->id,
        ]);
    }

    #[Test]
    public function can_update_lesson_without_content_video_url_and_order(): void
    {
        $course = Course::factory()->create();

        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $data = [
            'title' => fake()->sentence(),
        ];

        $response = $this->putJson("api/lessons/{$lesson->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Lesson updated successfully')
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.content', $lesson->content)
            ->assertJsonPath('data.video_url', $lesson->video_url)
            ->assertJsonPath('data.order', (int) $lesson->order)
            ->assertJsonPath('data.course_id', $course->id);

        $this->assertDatabaseHas('lessons', [
            'title' => $data['title'],
            'content' => $lesson->content,
            'video_url' => $lesson->video_url,
            'order' => (int) $lesson->order,
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

    #[Test]
    public function can_mark_lesson_completed()
    {
        $user = User::factory()->create();

        $lesson = Lesson::factory()->create([]);

        $response = $this->actingAs($user, 'api')->postJson("/api/lessons/{$lesson->id}/complete");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('lesson_completions', [
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function can_unmark_lesson_completed()
    {
        $user = User::factory()->create();

        $lesson = Lesson::factory()->create([]);

        $response = $this->actingAs($user, 'api')->postJson("/api/lessons/{$lesson->id}/uncomplete");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('lesson_completions', [
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
        ]);
    }
}
