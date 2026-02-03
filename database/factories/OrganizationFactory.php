<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['district', 'organization', 'pulse_admin'];

        return [
            'name' => $this->faker->company(),
            'type' => $this->faker->randomElement($types),
            'slug' => $this->faker->unique()->slug(),
            'settings' => [],
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the organization is a district.
     */
    public function district(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'district',
        ]);
    }

    /**
     * Indicate that the organization is a organization.
     */
    public function organization(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'organization',
        ]);
    }

    /**
     * Indicate that the organization is a Pulse admin org.
     */
    public function pulseAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pulse_admin',
        ]);
    }

    /**
     * Set the parent organization (for organizations under districts).
     */
    public function withParent(Organization $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }
}
