<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'question_text' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['multiple_choice', 'essay']),
            'answer' => $this->faker->sentence(),
            'created_by' => User::factory(),
        ];
    }
}
