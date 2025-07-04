<?php

namespace Tests\Feature;

use App\Models\Choice;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ChoiceEndPointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'manage choices']);

        $adminRole = Role::create(['name' => 'admin', 'guard' => 'api']);

        $adminRole->givePermissionTo('manage choices');

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
        $this->actingAs($this->user, 'api');
    }

    #[Test]
    public function can_list_choices_by_question(): void
    {
        $question = Question::factory()->create();

        $choices = Choice::factory()->count(5)->create(['question_id' => $question->id]);

        $response = $this->getJson("api/questions/{$question->id}/choices");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Choices retrieved successfully');

        foreach ($choices as $choice) {
            $this->assertDatabaseHas('choices', [
                'question_id' => $question->id,
                'choice_text' => $choice->choice_text,
                'is_correct' => $choice->is_correct,
            ]);
        }
    }

    #[Test]
    public function can_create_choice_by_question(): void
    {
        $question = Question::factory()->create();

        $data = [
            'choice_text' => fake()->sentence(),
            'is_correct' => fake()->boolean()
        ];

        $response = $this->postJson("api/questions/{$question->id}/choices", $data);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Choice created successfully')
            ->assertJsonPath('data.choice_text', $data['choice_text'])
            ->assertJsonPath('data.is_correct', $data['is_correct']);

        $this->assertDatabaseHas('choices', [
            'choice_text' => $data['choice_text'],
            'is_correct' => $data['is_correct'],
        ]);
    }

    #[Test]
    public function can_update_choice_by_question(): void
    {
        $choice = Choice::factory()->create();

        $data = [
            'choice_text' => fake()->sentence(),
            'is_correct' => fake()->boolean()
        ];

        $response = $this->putJson("api/choices/{$choice->id}", $data);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Choice updated successfully')
            ->assertJsonPath('data.choice_text', $data['choice_text'])
            ->assertJsonPath('data.is_correct', $data['is_correct']);

        $this->assertDatabaseHas('choices', [
            'choice_text' => $data['choice_text'],
            'is_correct' => $data['is_correct'],
        ]);
    }

    #[Test]
    public function can_delete_choice_by_question(): void
    {
        $choice = Choice::factory()->create();

        $response = $this->deleteJson("api/choices/{$choice->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Choice deleted successfully')
            ->assertJsonPath('data', null);

        $this->assertDatabaseMissing('choices', [
            'choice_text' => $choice->choice_text,
            'is_correct' => $choice->is_correct,
        ]);
    }
}
