<?php

namespace App\Services;

use App\Models\Choice;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PDO;

class QuizSubmissionService
{
    public function submit(User $user, Quiz $quiz, array $answerData): QuizSubmission
    {
        DB::beginTransaction();

        try {
            $submission = QuizSubmission::create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'score' => 0
            ]);

            $score = 0;

            foreach ($answerData as $data) {
                $question = Question::find($data['question_id']);

                $submission->answers()->create([
                    'question_id' => $data['question_id'],
                    'answer_text' => $data['answer_text'] ?? null,
                    'choice_id' => $data['choice_id'] ?? null
                ]);

                // optional: auto calculate score for multiple_choice
                if ($question->type == 'multiple_choice' && $data['choice_id']) {
                    $selectedChoice = Choice::find($data['choice_id']);
                    if ($selectedChoice->is_correct) {
                        $score++;
                    }
                }
            }

            $submission->update(['score' => $score]);

            DB::commit();
            return $submission;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function listSubmissionsByQuiz(Quiz $quiz, User $user)
    {
        return $quiz->submissions()->where('user_id', $user->id)->get();
    }

    public function listSubmissionsByUser(User $user)
    {
        return QuizSubmission::with('quiz')
            ->where('user_id', $user->id) // eager load quiz
            ->get();
    }

    public function getDetailSubmission(QuizSubmission $quizSubmission, User $user): QuizSubmission
    {
        if ($quizSubmission->user_id !== $user->id) {
            throw new \Exception('Unauthorized');
        }

        return $quizSubmission->load('quiz');
    }

    public function getQuizSummary(Quiz $quiz)
    {
        $total = $quiz->submissions()->count();

        $average = $quiz->submissions()->avg('score');

        return [
            'total_submissions' => $total,
            'average_score' => $average
        ];
    }
}
