<?php

namespace Tests\Feature\Master;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuizEndPointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'manage quizzes']);

        $adminRole = Role::create(['name' => 'admin', 'guard' => 'api']);

        $adminRole->givePermissionTo('manage quizzes');

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function can_list_quiz_by_course(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $response = $this->getJson("api/courses/{$course->id}/quizzes");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Quizzes retrieved successfully')
            ->assertJsonFragment(['title' => $quiz->title])
            ->assertJsonFragment(['description' => $quiz->description])
            ->assertJsonFragment(['course_id' => $course->id]);
    }

    #[Test]
    public function can_create_quiz_with_description(): void
    {
        $course = Course::factory()->create();

        $data = [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph()
        ];

        $response = $this->postJson("api/courses/{$course->id}/quizzes", $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Quiz created successfully')
            ->assertJsonPath('data.course_id', $course->id)
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.created_by', $this->user->id);

        $this->assertDatabaseHas('quizzes', [
            'course_id' => $course->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'created_by' => $this->user->id
        ]);
    }

    #[Test]
    public function can_create_quiz_without_description(): void
    {
        $course = Course::factory()->create();

        $data = [
            'title' => fake()->sentence(),
        ];

        $response = $this->postJson("api/courses/{$course->id}/quizzes", $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Quiz created successfully')
            ->assertJsonPath('data.course_id', $course->id)
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.created_by', $this->user->id);

        $this->assertDatabaseHas('quizzes', [
            'course_id' => $course->id,
            'title' => $data['title'],
            'description' => null,
            'created_by' => $this->user->id
        ]);
    }

    #[Test]
    public function can_update_quiz_with_description(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $data = [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph()
        ];

        $response = $this->putJson("api/quizzes/{$quiz->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Quiz updated successfully')
            ->assertJsonPath('data.course_id', $course->id)
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.description', $data['description']);

        $this->assertDatabaseHas('quizzes', [
            'course_id' => $course->id,
            'title' => $data['title'],
            'description' => $data['description']
        ]);
    }

    #[Test]
    public function can_update_quiz_without_description(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $data = [
            'title' => fake()->sentence(),
        ];

        $response = $this->putJson("api/quizzes/{$quiz->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Quiz updated successfully')
            ->assertJsonPath('data.course_id', $course->id)
            ->assertJsonPath('data.title', $data['title']);

        $this->assertDatabaseHas('quizzes', [
            'course_id' => $course->id,
            'title' => $data['title'],
        ]);
    }

    #[Test]
    public function can_delete_quiz(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $response = $this->deleteJson("api/quizzes/{$quiz->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Quiz deleted successfully')
            ->assertJsonPath('data', null);

        $this->assertDatabaseMissing('quizzes', [
            'title' => $quiz->title,
            'description' => $quiz->description
        ]);
    }
}
