<?php

namespace Database\Factories;

use App\Models\Resource;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resource>
 */
class ResourceFactory extends Factory
{
    protected $model = Resource::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['document', 'video', 'audio', 'link', 'image', 'interactive'];
        $statuses = ['draft', 'published', 'archived'];

        return [
            'org_id' => Organization::factory(),
            'created_by' => User::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement($types),
            'status' => $this->faker->randomElement($statuses),
            'url' => $this->faker->url(),
            'file_path' => null,
            'file_size' => null,
            'mime_type' => null,
            'target_levels' => $this->faker->randomElements(['K', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'], 3),
            'subject_areas' => $this->faker->randomElements(['math', 'science', 'english', 'history', 'art'], 2),
            'tags' => $this->faker->words(4),
            'is_featured' => false,
            'view_count' => $this->faker->numberBetween(0, 1000),
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the resource is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the resource is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the resource is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    /**
     * Indicate that the resource is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'status' => 'published',
        ]);
    }

    /**
     * Set the resource type to video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'url' => 'https://www.youtube.com/watch?v=' . $this->faker->regexify('[A-Za-z0-9]{11}'),
            'mime_type' => 'video/mp4',
        ]);
    }

    /**
     * Set the resource type to document.
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'document',
            'file_path' => 'resources/' . $this->faker->uuid() . '.pdf',
            'file_size' => $this->faker->numberBetween(100000, 10000000),
            'mime_type' => 'application/pdf',
        ]);
    }

    /**
     * Set the resource type to link.
     */
    public function link(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'link',
            'url' => $this->faker->url(),
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
