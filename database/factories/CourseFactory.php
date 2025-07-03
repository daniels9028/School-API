<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->name,
            'description' => $this->faker->text,
            'thumbnail' => $this->faker->url,
            'category_id' => Category::factory()->create(),
            'status' => $this->faker->randomElement(['draft', 'published']),
            'created_by' => User::factory(),
        ];
    }
}
