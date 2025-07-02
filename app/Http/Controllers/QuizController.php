<?php

namespace App\Http\Controllers;

use App\Http\Requests\Quiz\StoreQuizRequest;
use App\Http\Requests\Quiz\UpdateQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Course;
use App\Models\Quiz;
use App\Services\QuizService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    public function __construct(private QuizService $quizService) {}

    public function index($courseId)
    {
        $quizzes = $this->quizService->getAllByCourse($courseId);

        return response()->json([
            'success' => true,
            'message' => 'Quizzes retrieved successfully',
            'data' => QuizResource::collection($quizzes)
        ]);
    }

    public function store(StoreQuizRequest $request, Course $course)
    {
        $quiz = $this->quizService->store($course, [
            ...$request->validated(),
            'created_by' => Auth::user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quiz created successfully',
            'data' => new QuizResource($quiz)
        ], 201);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        $updateQuiz = $this->quizService->update($quiz, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Quiz updated successfully',
            'data' => new QuizResource($updateQuiz)
        ]);
    }

    public function destroy(Quiz $quiz)
    {
        $this->quizService->delete($quiz);

        return response()->json([
            'success' => true,
            'message' => 'Quiz deleted successfully',
            'data' => null
        ]);
    }
}
