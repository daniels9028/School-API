<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Quiz;

class QuizService
{
    public function getAllByCourse($course)
    {
        return Quiz::where('course_id', $course)->get();
    }

    public function store(Course $course, array $data): Quiz
    {
        return $course->quizzes()->create($data);
    }

    public function update(Quiz $quiz, array $data): Quiz
    {
        $quiz->update($data);

        return $quiz;
    }

    public function delete(Quiz $quiz): void
    {
        $quiz->delete();
    }
}
