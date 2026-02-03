<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\Organization;
use App\Models\LearningGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class LearnerFactory extends Factory
{
    protected $model = Participant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $levels = ['K', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];

        return [
            'org_id' => Organization::factory(),
            'participant_id' => $this->faker->unique()->numerify('STU-######'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'level' => $this->faker->randomElement($levels),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-5 years'),
            'is_active' => true,
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the participant is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Assign to a specific organization.
     */
    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'org_id' => $organization->id,
        ]);
    }

    /**
     * Assign to a specific learning_group.
     */
    public function inClassroom(LearningGroup $learning_group): static
    {
        return $this->state(fn (array $attributes) => [
            'learning_group_id' => $learning_group->id,
            'org_id' => $learning_group->org_id,
        ]);
    }

    /**
     * Set a specific level level.
     */
    public function level(string $level): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $level,
        ]);
    }

    /**
     * Make the participant elementary age (K-5).
     */
    public function elementary(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $this->faker->randomElement(['K', '1', '2', '3', '4', '5']),
            'date_of_birth' => $this->faker->dateTimeBetween('-11 years', '-5 years'),
        ]);
    }

    /**
     * Make the participant middle organization age (6-8).
     */
    public function middleOrganization(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $this->faker->randomElement(['6', '7', '8']),
            'date_of_birth' => $this->faker->dateTimeBetween('-14 years', '-11 years'),
        ]);
    }

    /**
     * Make the participant high organization age (9-12).
     */
    public function highOrganization(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => $this->faker->randomElement(['9', '10', '11', '12']),
            'date_of_birth' => $this->faker->dateTimeBetween('-18 years', '-14 years'),
        ]);
    }
}
