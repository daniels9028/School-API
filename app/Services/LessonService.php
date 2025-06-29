<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;

class LessonService
{
    public function listAll(Course $course)
    {
        return $course->lessons;
    }

    public function store(Course $course, array $data): Lesson
    {
        return $course->lessons()->create($data);
    }

    public function update(Lesson $lesson, array $data): Lesson
    {
        $lesson->update($data);
        return $lesson;
    }

    public function delete(Lesson $lesson): void
    {
        $lesson->delete();
    }
}
