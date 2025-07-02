<?php

namespace Tests\Feature\Master;

use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuestionEndPointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'manage questions']);

        $adminRole = Role::create(['name' => 'admin', 'guard' => 'api']);

        $adminRole->givePermissionTo('manage questions');

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function can_list_questions_by_quiz(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $questions = Question::factory()->count(5)->create(['quiz_id' => $quiz->id]);

        $response = $this->getJson("api/quizzes/{$quiz->id}/questions");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Questions retrieved successfully');

        foreach ($questions as $question) {
            $this->assertDatabaseHas('questions', [
                'id' => $question->id,
                'quiz_id' => $quiz->id,
                'question_text' => $question->question_text,
                'type' => $question->type,
                'answer' => $question->answer,
            ]);
        }
    }

    #[Test]
    public function can_create_question_by_quiz_with_answer(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $data = [
            'question_text' => fake()->sentence(),
            'type' =>  fake()->randomElement(['multiple_choice', 'essay']),
            'answer' => fake()->paragraph()
        ];

        $response = $this->postJson("api/quizzes/{$quiz->id}/questions", $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Question created successfully')
            ->assertJsonPath('data.quiz_id', $quiz->id)
            ->assertJsonPath('data.question_text', $data['question_text'])
            ->assertJsonPath('data.type', $data['type'])
            ->assertJsonPath('data.answer', $data['answer'])
            ->assertJsonPath('data.created_by', $this->user->id);

        $this->assertDatabaseHas('questions', [
            'quiz_id' => $quiz->id,
            'question_text' => $data['question_text'],
            'type' => $data['type'],
            'answer' => $data['answer'],
            'created_by' => $this->user->id
        ]);
    }

    #[Test]
    public function can_create_question_by_quiz_without_answer(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $data = [
            'question_text' => fake()->sentence(),
            'type' =>  fake()->randomElement(['multiple_choice', 'essay']),
        ];

        $response = $this->postJson("api/quizzes/{$quiz->id}/questions", $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Question created successfully')
            ->assertJsonPath('data.quiz_id', $quiz->id)
            ->assertJsonPath('data.question_text', $data['question_text'])
            ->assertJsonPath('data.type', $data['type'])
            ->assertJsonPath('data.answer', null)
            ->assertJsonPath('data.created_by', $this->user->id);

        $this->assertDatabaseHas('questions', [
            'quiz_id' => $quiz->id,
            'question_text' => $data['question_text'],
            'type' => $data['type'],
            'answer' => null,
            'created_by' => $this->user->id
        ]);
    }

    #[Test]
    public function can_update_question_with_answer(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $question = Question::factory()->create((['quiz_id' => $quiz->id]));

        $data = [
            'question_text' => fake()->sentence(),
            'type' =>  fake()->randomElement(['multiple_choice', 'essay']),
            'answer' => fake()->paragraph()
        ];

        $response = $this->putJson("api/questions/{$question->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Question updated successfully')
            ->assertJsonPath('data.quiz_id', $quiz->id)
            ->assertJsonPath('data.question_text', $data['question_text'])
            ->assertJsonPath('data.type', $data['type'])
            ->assertJsonPath('data.answer', $data['answer']);

        $this->assertDatabaseHas('questions', [
            'quiz_id' => $quiz->id,
            'question_text' => $data['question_text'],
            'type' => $data['type'],
            'answer' => $data['answer']
        ]);
    }

    #[Test]
    public function can_update_question_without_answer(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $question = Question::factory()->create((['quiz_id' => $quiz->id]));

        $data = [
            'question_text' => fake()->sentence(),
            'type' =>  fake()->randomElement(['multiple_choice', 'essay'])
        ];

        $response = $this->putJson("api/questions/{$question->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Question updated successfully')
            ->assertJsonPath('data.quiz_id', $quiz->id)
            ->assertJsonPath('data.question_text', $data['question_text'])
            ->assertJsonPath('data.type', $data['type'])
            ->assertJsonPath('data.answer', $question->answer);

        $this->assertDatabaseHas('questions', [
            'quiz_id' => $quiz->id,
            'question_text' => $data['question_text'],
            'type' => $data['type'],
            'answer' => $question->answer
        ]);
    }

    #[Test]
    public function can_delete_question(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $question = Question::factory()->create((['quiz_id' => $quiz->id]));

        $response = $this->deleteJson("api/questions/{$question->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Question deleted successfully')
            ->assertJsonPath('data', null);

        $this->assertDatabaseMissing('questions', [
            'question_text' => $question->question_text,
            'type' => $question->type,
            'answer' => $question->answer
        ]);
    }
}
