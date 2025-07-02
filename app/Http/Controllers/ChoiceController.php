<?php

namespace App\Http\Controllers;

use App\Http\Requests\Choice\StoreChoiceRequest;
use App\Http\Requests\Choice\UpdateChoiceRequest;
use App\Http\Resources\ChoiceResource;
use App\Models\Choice;
use App\Models\Question;
use App\Services\ChoiceService;
use Illuminate\Http\Request;

class ChoiceController extends Controller
{
    public function __construct(private ChoiceService $choiceService) {}

    public function index(Question $question)
    {
        $choices = $this->choiceService->getAllByQuestion($question);

        return response()->json([
            'success' => true,
            'message' => 'Choices retrieved successfully',
            'data' => ChoiceResource::collection($choices)
        ]);
    }

    public function store(StoreChoiceRequest $request, Question $question)
    {
        $choice = $this->choiceService->store($question, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Choice created successfully',
            'data' => new ChoiceResource($choice)
        ], 201);
    }

    public function update(UpdateChoiceRequest $request, Choice $choice)
    {
        $updateChoice = $this->choiceService->update($choice, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Choice updated successfully',
            'data' => new ChoiceResource($updateChoice)
        ]);
    }

    public function destroy(Choice $choice)
    {
        $this->choiceService->delete($choice);

        return response()->json([
            'success' => true,
            'message' => 'Choice deleted successfully',
            'data' => null
        ]);
    }
}
