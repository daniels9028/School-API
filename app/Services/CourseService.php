<?php

namespace App\Services;

use App\Models\Course;

class CourseService
{
    public function getAll()
    {
        return Course::latest()->get();
    }

    public function store(array $data): Course
    {
        return Course::create($data);
    }

    public function update(Course $course, array $data): Course
    {
        $course->update($data);

        return $course;
    }

    public function delete(Course $course): void
    {
        $course->delete();
    }

    public function assignTeachers(Course $course, array $teacherIds): Course
    {
        // Sync = replace existing teachers with new list
        $course->teachers()->sync($teacherIds);

        // refresh to get updated teachers relation
        return $course->fresh('teachers');
    }

    public function assignStudents(Course $course, array $studentIds): Course
    {
        $course->students()->sync($studentIds);

        return $course->fresh('students');
    }

    public function listTeachers(Course $course)
    {
        return $course->teachers()->get();
    }

    public function listStudents(Course $course)
    {
        return $course->students()->get();
    }
}
