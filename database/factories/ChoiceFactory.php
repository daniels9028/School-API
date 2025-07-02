<?php

namespace Database\Factories;

use App\Models\Choice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Choice>
 */
class ChoiceFactory extends Factory
{
    protected $model = Choice::class;

    public function definition(): array
    {
        return [
            'text' => $this->faker->sentence(),
            'is_correct' => $this->faker->boolean()
        ];
    }
}
