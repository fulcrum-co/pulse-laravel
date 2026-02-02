<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['curated', 'smart', 'personal'];

        return [
            'org_id' => Organization::factory(),
            'created_by' => User::factory(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement($types),
            'is_public' => false,
            'is_featured' => false,
            'rules' => [],
            'settings' => [],
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the collection is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the collection is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the collection is a smart collection (auto-populated by rules).
     */
    public function smart(array $rules = []): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'smart',
            'rules' => $rules ?: [
                'grade_levels' => ['9', '10', '11', '12'],
                'subject_areas' => ['math', 'science'],
            ],
        ]);
    }

    /**
     * Indicate that the collection is curated (manually managed).
     */
    public function curated(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'curated',
            'rules' => [],
        ]);
    }

    /**
     * Indicate that the collection is personal (user's private collection).
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'personal',
            'is_public' => false,
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
     * Assign to a specific creator.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }
}
