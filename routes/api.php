<?php

use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ChoiceController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizSubmissionController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Users Management
    Route::middleware('permission:manage users')->group(function () {
        Route::apiResource('users', UserController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::post('/users/{user}/roles', [UserController::class, 'assignRole']);
    });

    // Roles Management
    Route::middleware('permission:manage roles')->group(function () {
        Route::apiResource('roles', RoleController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::post('/roles/{role}/permissions', [RoleController::class, 'assignPermissions']);
    });

    // Permissions Management
    Route::apiResource('permissions', PermissionController::class)
        ->middleware('permission:manage permissions')
        ->only(['index', 'store', 'update', 'destroy']);

    // Courses Management
    Route::middleware('permission:manage courses')->group(function () {
        Route::apiResource('courses', CourseController::class);

        Route::post('courses/{course}/assign-teachers', [CourseController::class, 'assignTeachers']);

        Route::post('courses/{course}/assign-students', [CourseController::class, 'assignStudents']);

        Route::get('/courses/{course}/teachers', [CourseController::class, 'listTeachers']);

        Route::get('/courses/{course}/students', [CourseController::class, 'listStudents']);
    });

    Route::middleware('permission:manage lessons')->group(function () {
        Route::prefix('courses/{course}')->group(function () {
            Route::get('lessons', [LessonController::class, 'index']);
            Route::post('lessons', [LessonController::class, 'store']);
        });

        Route::put('lessons/{lesson}', [LessonController::class, 'update']);
        Route::delete('lessons/{lesson}', [LessonController::class, 'destroy']);
    });

    Route::middleware('permission:manage quizzes')->group(function () {
        Route::prefix('courses/{course}')->group(function () {
            Route::get('quizzes', [QuizController::class, 'index']);
            Route::post('quizzes', [QuizController::class, 'store']);
        });

        Route::put('quizzes/{quiz}', [QuizController::class, 'update']);
        Route::delete('quizzes/{quiz}', [QuizController::class, 'destroy']);
    });

    Route::middleware('permission:manage questions')->group(function () {
        Route::prefix('quizzes/{quiz}')->group(function () {
            Route::get('questions', [QuestionController::class, 'index']);
            Route::post('questions', [QuestionController::class, 'store']);
        });

        Route::put('questions/{question}', [QuestionController::class, 'update']);
        Route::delete('questions/{question}', [QuestionController::class, 'destroy']);
    });

    Route::middleware('permission:manage choices')->group(function () {
        Route::prefix('questions/{question}')->group(function () {
            Route::get('choices', [ChoiceController::class, 'index']);
            Route::post('choices', [ChoiceController::class, 'store']);
        });

        Route::put('choices/{choice}', [ChoiceController::class, 'update']);
        Route::delete('choices/{choice}', [ChoiceController::class, 'destroy']);
    });

    Route::middleware('permission:manage quiz submissions')->group(function () {
        Route::post('quizzes/{quiz}/submit', [QuizSubmissionController::class, 'submit']);
        Route::get('quizzes/{quiz}/submissions', [QuizSubmissionController::class, 'index']);
        Route::get('quiz-submissions/{quizSubmission}', [QuizSubmissionController::class, 'show']);
    });
});
