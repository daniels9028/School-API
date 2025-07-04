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

        Permission::create(['name' => 'view analytics']);

        $adminRole = Role::create(['name' => 'admin', 'guard' => 'api']);

        $adminRole->givePermissionTo('view analytics');

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function student_can_submit_quiz(): void
    {
        $user = User::factory()->create();

        $quiz = Quiz::factory()->create();

        $questions = Question::factory()->count(5)->create([
            'quiz_id' => $quiz->id,
            'type' => 'multiple_choice'
        ]);

        $payload = [
            'answers' => []
        ];

        foreach ($questions as $question) {
            // Buat 2 choice, salah satunya benar
            $correctChoice = Choice::factory()->create([
                'question_id' => $question->id,
                'choice_text' => 'Correct Answer',
                'is_correct' => true,
            ]);

            Choice::factory()->create([
                'question_id' => $question->id,
                'choice_text' => 'Wrong Answer',
                'is_correct' => false,
            ]);

            // Ambil choice_text dari yang benar
            $payload['answers'][] = [
                'question_id' => $question->id,
                'answer' => $correctChoice->choice_text,
            ];
        }

        $response = $this
            ->actingAs($user)
            ->postJson("/api/quizzes/{$quiz->id}/submit", $payload);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Quiz submitted successfully')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'quiz_id',
                    'user_id',
                    'score',
                    'submission_answers' => [
                        ['id', 'question_id', 'answer', 'is_correct']
                    ],
                ],
            ]);

        // Pastikan skor 100 (benar semua)
        $this->assertEquals(100, $response->json('data.score'));

        // Cek database
        $this->assertDatabaseHas('quiz_submissions', [
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
        ]);

        foreach ($payload['answers'] as  $answer) {
            $this->assertDatabaseHas('submission_answers', [
                'quiz_submission_id' => $response->json('data.id'),
                'question_id' => $answer['question_id'],
                'answer' => $answer['answer'],
            ]);
        }
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

    #[Test]
    public function can_get_quiz_summary(): void
    {
        $course = Course::factory()->create();

        $quiz = Quiz::factory()->create(['course_id' => $course->id]);

        $submissions = QuizSubmission::factory()
            ->count(10)
            ->create([
                'quiz_id' => $quiz->id,
                'user_id' => $this->user->id
            ]);

        $total_submissions = $submissions->count();

        $average_score = $submissions->average('score');

        $response = $this->getJson("api/quizzes/{$quiz->id}/submissions/summary");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Quiz summary retrieved successfully')
            ->assertJsonPath('data.total_submissions', $total_submissions)
            ->assertJsonPath('data.average_score', $average_score);
    }
}
