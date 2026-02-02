<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\Provider;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'org_id' => Organization::factory(),
            'provider_id' => Provider::factory(),
            'name' => $this->faker->sentence(3),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(2),
            'short_description' => $this->faker->sentence(10),
            'target_grades' => $this->faker->randomElements(['K', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'], 4),
            'subject_areas' => $this->faker->randomElements(['math', 'science', 'english', 'history', 'art', 'music'], 2),
            'is_active' => true,
            'settings' => [],
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the program is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Assign to a specific provider.
     */
    public function forProvider(Provider $provider): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_id' => $provider->id,
            'org_id' => $provider->org_id,
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
}
