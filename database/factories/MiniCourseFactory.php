<?php

namespace Database\Factories;

use App\Models\MiniCourse;
use App\Models\Organization;
use App\Models\User;
use App\Models\Provider;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MiniCourse>
 */
class MiniCourseFactory extends Factory
{
    protected $model = MiniCourse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['draft', 'pending_review', 'published', 'archived'];
        $difficultyLevels = ['beginner', 'intermediate', 'advanced'];
        $courseTypes = ['standard', 'template', 'generated'];

        return [
            'org_id' => Organization::factory(),
            'created_by' => User::factory(),
            'provider_id' => null,
            'program_id' => null,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(3),
            'short_description' => $this->faker->sentence(10),
            'status' => $this->faker->randomElement($statuses),
            'difficulty_level' => $this->faker->randomElement($difficultyLevels),
            'course_type' => $this->faker->randomElement($courseTypes),
            'is_template' => false,
            'objectives' => [
                $this->faker->sentence(),
                $this->faker->sentence(),
                $this->faker->sentence(),
            ],
            'target_levels' => $this->faker->randomElements(['K', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'], 3),
            'estimated_duration_minutes' => $this->faker->numberBetween(15, 120),
            'tags' => $this->faker->words(3),
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the course is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the course is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the course is pending review.
     */
    public function pendingReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_review',
        ]);
    }

    /**
     * Indicate that the course is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    /**
     * Indicate that the course is a template.
     */
    public function template(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_template' => true,
            'course_type' => 'template',
        ]);
    }

    /**
     * Indicate that the course was AI-generated.
     */
    public function generated(): static
    {
        return $this->state(fn (array $attributes) => [
            'course_type' => 'generated',
            'generation_source' => 'ai',
        ]);
    }

    /**
     * Set the difficulty level to beginner.
     */
    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => 'beginner',
        ]);
    }

    /**
     * Set the difficulty level to intermediate.
     */
    public function intermediate(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => 'intermediate',
        ]);
    }

    /**
     * Set the difficulty level to advanced.
     */
    public function advanced(): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => 'advanced',
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
     * Assign to a provider and program.
     */
    public function withProvider(Provider $provider, ?Program $program = null): static
    {
        return $this->state(fn (array $attributes) => [
            'provider_id' => $provider->id,
            'program_id' => $program?->id,
        ]);
    }
}
