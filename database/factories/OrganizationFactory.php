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
        $types = ['section', 'organization', 'pulse_admin'];

        return [
            'name' => $this->faker->company(),
            'org_type' => $this->faker->randomElement($types),
            'slug' => $this->faker->unique()->slug(),
            'settings' => [],
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the organization is a section.
     */
    public function section(): static
    {
        return $this->state(fn (array $attributes) => [
            'org_type' => 'section',
        ]);
    }

    /**
     * Indicate that the organization is a organization.
     */
    public function organization(): static
    {
        return $this->state(fn (array $attributes) => [
            'org_type' => 'organization',
        ]);
    }

    /**
     * Indicate that the organization is a Pulse admin org.
     */
    public function pulseAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'org_type' => 'pulse_admin',
        ]);
    }

    /**
     * Set the parent organization (for organizations under sections).
     */
    public function withParent(Organization $parentOrganization): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_org_id' => $parentOrganization->id,
        ]);
    }
}
