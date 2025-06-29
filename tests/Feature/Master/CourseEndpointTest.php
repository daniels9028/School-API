<?php

namespace Tests\Feature\Master;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CourseEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'manage courses']);
        $adminRole = Role::create(['name' => 'admin', 'guard' => 'api']);

        $adminRole->givePermissionTo('manage courses');

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function can_list_courses(): void
    {
        $courses = Course::factory()->create();

        $response = $this->getJson('api/courses');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Courses retrieved successfully')
            ->assertJsonFragment(['title' => $courses->title])
            ->assertJsonFragment(['description' => $courses->description]);
    }

    #[Test]
    public function can_create_course_with_description(): void
    {
        $data = [
            'title' => fake()->name(),
            'description' => fake()->text()
        ];

        $response = $this->postJson('api/courses', $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Course created successfully')
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.created_by', $this->user->id);

        $this->assertDatabaseHas('courses', [
            'title' => $data['title'],
            'description' => $data['description'],
            'created_by' => $this->user->id,
        ]);
    }

    #[Test]
    public function can_create_course_without_description(): void
    {
        $data = [
            'title' => fake()->name(),
            'description' => null,
        ];

        $response = $this->postJson('api/courses', $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Course created successfully')
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.created_by', $this->user->id);

        $this->assertDatabaseHas('courses', [
            'title' => $data['title'],
            'description' => $data['description'],
            'created_by' => $this->user->id,
        ]);
    }

    #[Test]
    public function can_get_course(): void
    {
        $course = Course::factory()->create();

        $response = $this->getJson("api/courses/{$course->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Course detail')
            ->assertJsonPath('data.title', $course->title)
            ->assertJsonPath('data.description', $course->description)
            ->assertJsonPath('data.created_by', $course->created_by);
    }

    #[Test]
    public function can_update_course_with_description(): void
    {
        $data = [
            'title' => fake()->name(),
            'description' => fake()->text(),
        ];

        $targetCourse = Course::factory()->create();

        $response = $this->putJson("api/courses/{$targetCourse->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Course updated successfully')
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.description', $data['description']);

        $this->assertDatabaseHas('courses', [
            'title' => $data['title'],
            'description' => $data['description']
        ]);
    }

    #[Test]
    public function can_update_course_without_description(): void
    {
        $data = [
            'title' => fake()->name(),
            'description' => null
        ];

        $targetCourse = Course::factory()->create();

        $response = $this->putJson("api/courses/{$targetCourse->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Course updated successfully')
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.description', $data['description']);

        $this->assertDatabaseHas('courses', [
            'title' => $data['title'],
            'description' => $data['description']
        ]);
    }

    #[Test]
    public function can_delete_course(): void
    {
        $course = Course::factory()->create();

        $response = $this->deleteJson("api/courses/{$course->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Course deleted successfully')
            ->assertJsonPath('data', null);

        $this->assertDatabaseMissing('courses', [
            "title" => $course->title,
            "description" => $course->description,
            "created_by" => $course->created_by
        ]);
    }

    #[Test]
    public function can_assign_teachers_to_course(): void
    {
        $course = Course::factory()->create();

        Role::create(['name' => 'teacher', 'guard' => 'api']);

        $teachers = User::factory()->count(2)->create();

        foreach ($teachers as $teacher) {
            $teacher->assignRole('teacher');
        }

        $teacherIds = $teachers->pluck('id')->toArray();

        $response = $this->postJson("api/courses/{$course->id}/assign-teachers", [
            'teacher_ids' => $teacherIds
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Teachers assigned to course successfully')
            ->assertJsonFragment(['course' => $course->title]);

        foreach ($teachers as $teacher) {
            $this->assertTrue($course->fresh()->teachers->contains($teacher));
        }
    }

    #[Test]
    public function can_assign_students_to_course(): void
    {
        $course = Course::factory()->create();

        Role::create(['name' => 'student', 'guard' => 'api']);

        $students = User::factory()->count(2)->create();

        foreach ($students as $student) {
            $student->assignRole('student');
        }

        $studentIds = $students->pluck('id')->toArray();

        $response = $this->postJson("api/courses/{$course->id}/assign-students", [
            'student_ids' => $studentIds
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Students assigned to course successfully')
            ->assertJsonFragment(['course' => $course->title]);

        foreach ($students as $student) {
            $this->assertTrue($course->fresh()->students->contains($student));
        }
    }

    #[Test]
    public function can_list_teachers_in_course(): void
    {
        $course = Course::factory()->create();

        $teacher = User::factory()->create();

        $course->teachers()->attach($teacher->id);

        $response = $this->getJson("api/courses/{$course->id}/teachers");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'List of teachers in course')
            ->assertJsonFragment(['email' => $teacher->email]);
    }

    #[Test]
    public function can_list_students_in_course(): void
    {
        $course = Course::factory()->create();
        $student = User::factory()->create();
        $course->students()->attach($student->id);

        $response = $this->getJson("/api/courses/{$course->id}/students");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'List of students in course')
            ->assertJsonFragment(['email' => $student->email]);
    }
}
