<?php

namespace Database\Factories;

use App\Models\Provider;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Provider>
 */
class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $providerTypes = ['curriculum', 'assessment', 'tutoring', 'enrichment', 'professional_development'];

        return [
            'org_id' => Organization::factory(),
            'name' => $this->faker->company(),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(2),
            'type' => $this->faker->randomElement($providerTypes),
            'website_url' => $this->faker->url(),
            'contact_email' => $this->faker->companyEmail(),
            'contact_phone' => $this->faker->phoneNumber(),
            'logo_url' => $this->faker->imageUrl(200, 200, 'business'),
            'is_active' => true,
            'is_verified' => false,
            'settings' => [],
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the provider is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the provider is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set the provider type to curriculum.
     */
    public function curriculum(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'curriculum',
        ]);
    }

    /**
     * Set the provider type to assessment.
     */
    public function assessment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'assessment',
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
