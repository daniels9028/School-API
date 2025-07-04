<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\AssignStudentRequest;
use App\Http\Requests\Course\AssignTagsRequest;
use App\Http\Requests\Course\AssignTeacherRequest;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Resources\UserResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class CourseController extends Controller
{
    public function __construct(private CourseService $courseService) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Courses retrieved successfully',
            'data' => $this->courseService->getAll()
        ]);
    }

    public function store(StoreCourseRequest $request)
    {
        $data = $request->validated();

        // Upload thumbnail jika ada
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails');
        }

        $data['created_by'] = Auth::user()->id;

        $course = $this->courseService->store($data);

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully',
            'data' => $course
        ], 201);
    }

    public function show(Course $course)
    {
        return response()->json([
            'success' => true,
            'message' => 'Course detail',
            'data' => $course
        ]);
    }

    public function update(Course $course, UpdateCourseRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails');
        }

        $course = $this->courseService->update($course, $data);

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully',
            'data' => $course
        ]);
    }

    public function destroy(Course $course)
    {
        $this->courseService->delete($course);

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully',
            'data' => null
        ]);
    }

    public function assignTeachers(AssignTeacherRequest $request, Course $course)
    {
        $course = $this->courseService->assignTeachers($course, $request->teacher_ids);

        return response()->json([
            'success' => true,
            'message' => 'Teachers assigned to course successfully',
            'data' => [
                'course' => $course->title,
                'teachers' => $course->teachers->pluck('name')
            ]
        ]);
    }

    public function assignStudents(AssignStudentRequest $request, Course $course)
    {
        $course = $this->courseService->assignStudents($course, $request->student_ids);

        return response()->json([
            'success' => true,
            'message' => 'Students assigned to course successfully',
            'data' => [
                'course' => $course->title,
                'students' => $course->students->pluck('name')
            ]
        ]);
    }

    public function assignTags(AssignTagsRequest $request, Course $course)
    {
        $course = $this->courseService->assignTags($course, $request->tag_ids);

        return response()->json([
            'success' => true,
            'message' => 'Tags assigned to course successfully',
            'data' => [
                'course' => $course->title,
                'tags' => $course->tags->pluck('name')
            ]
        ]);
    }

    public function listTeachers(Course $course)
    {
        $teachers = $this->courseService->listTeachers($course);

        return response()->json([
            'success' => true,
            'message' => 'List of teachers in course',
            'data' => UserResource::collection($teachers)
        ]);
    }

    public function listStudents(Course $course)
    {
        $students = $this->courseService->listStudents($course);

        return response()->json([
            'success' => true,
            'message' => 'List of students in course',
            'data' => UserResource::collection($students)
        ]);
    }
}
