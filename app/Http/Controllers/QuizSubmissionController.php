<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuizSubmission\SubmitQuizSubmissionRequest;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Services\QuizSubmissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizSubmissionController extends Controller
{
    public function __construct(private QuizSubmissionService $quizSubmissionService) {}

    public function submit(SubmitQuizSubmissionRequest $request, Quiz $quiz)
    {
        $submission = $this->quizSubmissionService->submit(Auth::user(), $quiz, $request->answers);

        return response()->json([
            'success' => true,
            'message' => 'Quiz submitted successfully',
            'data' => [
                'submission_id' => $submission->id,
                'score' => $submission->score
            ]
        ], 200);
    }

    // LIST SUBMISSIONS BY QUIZ
    public function index(Quiz $quiz)
    {
        $user = Auth::user();

        $submissions = $this->quizSubmissionService->listSubmissionsByQuiz($quiz, $user);

        return response()->json([
            'success' => true,
            'message' => 'List quiz submissions retrieved successfully',
            'data' => $submissions
        ]);
    }

    // LIST SUBMISSION BY USER
    public function quizSubmissions()
    {
        $user = Auth::user();

        $submissions = $this->quizSubmissionService->listSubmissionsByUser($user);

        return response()->json([
            'success' => true,
            'message' => 'List quiz submissions by user retrieved successfully',
            'data' => $submissions
        ]);
    }

    public function show(QuizSubmission $quizSubmission)
    {
        $user = Auth::user();

        $detailSubmission = $this->quizSubmissionService->getDetailSubmission($quizSubmission, $user);

        return response()->json([
            'success' => true,
            'message' => 'Quiz submission detail',
            'data' => $detailSubmission
        ]);
    }

    public function summary(Quiz $quiz)
    {
        $summary = $this->quizSubmissionService->getQuizSummary($quiz);

        return response()->json([
            'success' => true,
            'message' => 'Quiz summary retrieved successfully',
            'data' => $summary
        ]);
    }
}
