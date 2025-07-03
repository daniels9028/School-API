<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuizSubmission>
 */
class QuizSubmissionFactory extends Factory
{
    protected $model = QuizSubmission::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'user_id' => User::factory(),
            'score' => $this->faker->numberBetween(0, 100)
        ];
    }
}
