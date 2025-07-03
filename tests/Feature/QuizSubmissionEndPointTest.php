<?php

namespace Tests\Feature;

use App\Models\Choice;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuizSubmissionEndPointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'manage quiz submissions']);

        $adminRole = Role::create(['name' => 'admin', 'guard' => 'api']);

        $adminRole->givePermissionTo('manage quiz submissions');

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function student_can_submit_quiz(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $questions = Question::factory()->count(3)->create(['quiz_id' => $quiz->id]);

        $choices = [];

        foreach ($questions as $question) {
            $choices[$question->id] = Choice::factory()->count(2)->create(['question_id' => $question->id]);
        }

        $payload = [
            'answers' => []
        ];

        foreach ($questions as $question) {
            $payload['answers'][] = [
                'question_id' => $question->id,
                'choice_id' => $choices[$question->id]->first()->id,
                'answer_text' => null,
            ];
        }

        $response = $this
            ->postJson("/api/quizzes/{$quiz->id}/submit", $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Quiz submitted successfully')
            ->assertJsonStructure(['data' => ['submission_id', 'score']]);

        $this->assertDatabaseHas('quiz_submissions', ['quiz_id' => $quiz->id, 'user_id' => $this->user->id]);
    }

    #[Test]
    public function can_list_my_submissions_by_quiz(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $submissions = QuizSubmission::factory()->count(5)->create(['quiz_id' => $quiz->id, 'user_id' => $this->user->id]);

        $response = $this->getJson("/api/quizzes/{$quiz->id}/submissions");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['quiz_id' => $quiz->id]);

        foreach ($submissions as $submission) {
            $this->assertDatabaseHas('quiz_submissions', [
                'quiz_id' => $quiz->id,
                'user_id' => $this->user->id,
                'score' => $submission->score,
            ]);
        }
    }

    #[Test]
    public function can_list_submissions_by_user(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $submissions = QuizSubmission::factory()->count(5)->create(['quiz_id' => $quiz->id, 'user_id' => $this->user->id]);

        $response = $this->getJson("/api/quiz-submissions");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['quiz_id' => $quiz->id]);

        foreach ($submissions as $submission) {
            $this->assertDatabaseHas('quiz_submissions', [
                'quiz_id' => $quiz->id,
                'user_id' => $this->user->id,
                'score' => $submission->score,
            ]);
        }
    }

    #[Test]
    public function can_show_detail_submission(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $submission = QuizSubmission::factory()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $this->user->id
        ]);

        $response = $this->getJson("/api/quiz-submissions/{$submission->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['quiz_id' => $quiz->id]);
    }
}
