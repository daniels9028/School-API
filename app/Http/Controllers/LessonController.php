<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lesson\StoreLessonRequest;
use App\Http\Requests\Lesson\UpdateLessonRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\LessonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonController extends Controller
{
    public function __construct(private LessonService $lessonService) {}

    public function index(Course $course)
    {
        return response()->json([
            'success' => true,
            'message' => 'Lesson retrieved successfully',
            'data' => $this->lessonService->listAll($course)
        ]);
    }

    public function store(StoreLessonRequest $request, Course $course)
    {
        $lesson = $this->lessonService->store($course, [
            ...$request->validated(),
            'created_by' => Auth::user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lesson created successfully',
            'data' => $lesson
        ], 201);
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson)
    {
        $updatedLesson = $this->lessonService->update($lesson, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Lesson updated successfully',
            'data' => $updatedLesson
        ]);
    }

    public function destroy(Lesson $lesson)
    {
        $this->lessonService->delete($lesson);

        return response()->json([
            'success' => true,
            'message' => 'Lesson deleted successfully',
            'data' => null
        ]);
    }

    public function markCompleted(Request $request, Lesson $lesson)
    {
        $userId = $request->user()->id;
        $this->lessonService->markCompleted($lesson, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Lesson marked as completed',
        ]);
    }

    public function unmarkCompleted(Request $request, Lesson $lesson)
    {
        $userId = $request->user()->id;
        $this->lessonService->unmarkCompleted($lesson, $userId);

        return response()->json([
            'success' => true,
            'message' => 'Lesson unmarked as completed',
        ]);
    }
}
