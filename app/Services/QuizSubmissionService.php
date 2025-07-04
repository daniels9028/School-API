<?php

namespace App\Services;

use App\Models\Choice;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\SubmissionAnswer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use PDO;

class QuizSubmissionService
{
    public function submit(User $user, Quiz $quiz, array $answerData): QuizSubmission
    {
        DB::beginTransaction();

        try {
            // Create submission
            $submission = QuizSubmission::create([
                'quiz_id' => $quiz->id,
                'user_id' => $user->id,
                'score' => 0 // default value
            ]);

            $totalQuestions = 0;
            $totalScore = 0;

            foreach ($answerData as $item) {
                $question = Question::findOrFail($item['question_id']);
                $userAnswer = $item['answer'];

                $isCorrect = null;

                if ($question->type === 'multiple_choice') {
                    $isCorrect = $question->choices()
                        ->where('choice_text', $userAnswer)
                        ->where('is_correct', true)
                        ->exists();
                } else if ($question->type === 'essay') {
                    $isCorrect = strtolower(trim($question->answer)) === strtolower(trim($userAnswer));
                }

                SubmissionAnswer::create([
                    'quiz_submission_id' => $submission->id,
                    'question_id' => $question->id,
                    'answer' => $userAnswer,
                    'is_correct' => $isCorrect,
                ]);

                if ($isCorrect) {
                    $totalScore++;
                }

                $totalQuestions++;
            }

            // Hitung skor
            $score = $totalQuestions > 0 ? ($totalScore / $totalQuestions) * 100 : 0;

            $submission->update(['score' => $score]);

            DB::commit();

            return $submission->load('submissionAnswers.question');
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
