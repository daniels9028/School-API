<?php

namespace App\Http\Controllers;

use App\Http\Requests\Question\StoreQuestionRequest;
use App\Http\Requests\Question\UpdateQuestionRequest;
use App\Http\Resources\QuestionResource;
use App\Models\Question;
use App\Models\Quiz;
use App\Services\QuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function __construct(private QuestionService $questionService) {}

    public function index(Quiz $quiz)
    {
        $questions = $this->questionService->getAllByQuiz($quiz);

        return response()->json([
            'success' => true,
            'message' => 'Questions retrieved successfully',
            'data' => QuestionResource::collection($questions)
        ]);
    }

    public function store(StoreQuestionRequest $request, Quiz $quiz)
    {
        $question = $this->questionService->store($quiz, [
            ...$request->validated(),
            'created_by' => Auth::user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Question created successfully',
            'data' => new QuestionResource($question)
        ], 201);
    }

    public function update(UpdateQuestionRequest $request, Question $question)
    {
        $updateQuestion = $this->questionService->update($question, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Question updated successfully',
            'data' => new QuestionResource($updateQuestion)
        ]);
    }

    public function destroy(Question $question)
    {
        $this->questionService->delete($question);

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully',
            'data' => null
        ]);
    }
}
