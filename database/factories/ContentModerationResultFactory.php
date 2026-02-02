<?php

namespace Database\Factories;

use App\Models\ContentModerationResult;
use App\Models\MiniCourse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContentModerationResult>
 */
class ContentModerationResultFactory extends Factory
{
    protected $model = ContentModerationResult::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'approved', 'rejected', 'needs_revision'];

        return [
            'moderatable_type' => MiniCourse::class,
            'moderatable_id' => MiniCourse::factory(),
            'status' => $this->faker->randomElement($statuses),
            'clarity_score' => $this->faker->randomFloat(2, 0, 1),
            'engagement_score' => $this->faker->randomFloat(2, 0, 1),
            'accuracy_score' => $this->faker->randomFloat(2, 0, 1),
            'appropriateness_score' => $this->faker->randomFloat(2, 0, 1),
            'overall_score' => $this->faker->randomFloat(2, 0, 1),
            'feedback' => $this->faker->paragraph(),
            'suggestions' => [
                $this->faker->sentence(),
                $this->faker->sentence(),
            ],
            'flagged_issues' => [],
            'reviewer_id' => null,
            'reviewed_at' => null,
            'ai_moderated' => true,
            'ai_model_version' => 'claude-3-opus',
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the moderation result is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'reviewer_id' => null,
            'reviewed_at' => null,
        ]);
    }

    /**
     * Indicate that the moderation result is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'overall_score' => $this->faker->randomFloat(2, 0.8, 1),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that the moderation result is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'overall_score' => $this->faker->randomFloat(2, 0, 0.4),
            'flagged_issues' => [
                'inappropriate_content',
                'factual_inaccuracy',
            ],
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that the content needs revision.
     */
    public function needsRevision(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'needs_revision',
            'overall_score' => $this->faker->randomFloat(2, 0.4, 0.7),
            'suggestions' => [
                'Improve clarity in section 2',
                'Add more engaging examples',
                'Verify factual claims with sources',
            ],
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that the moderation was done by AI only.
     */
    public function aiOnly(): static
    {
        return $this->state(fn (array $attributes) => [
            'ai_moderated' => true,
            'reviewer_id' => null,
        ]);
    }

    /**
     * Indicate that the moderation was reviewed by a human.
     */
    public function humanReviewed(User $reviewer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'reviewer_id' => $reviewer?->id ?? User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Set high quality scores.
     */
    public function highQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'clarity_score' => $this->faker->randomFloat(2, 0.85, 1),
            'engagement_score' => $this->faker->randomFloat(2, 0.85, 1),
            'accuracy_score' => $this->faker->randomFloat(2, 0.85, 1),
            'appropriateness_score' => $this->faker->randomFloat(2, 0.85, 1),
            'overall_score' => $this->faker->randomFloat(2, 0.85, 1),
        ]);
    }

    /**
     * Set low quality scores.
     */
    public function lowQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'clarity_score' => $this->faker->randomFloat(2, 0, 0.4),
            'engagement_score' => $this->faker->randomFloat(2, 0, 0.4),
            'accuracy_score' => $this->faker->randomFloat(2, 0, 0.4),
            'appropriateness_score' => $this->faker->randomFloat(2, 0, 0.4),
            'overall_score' => $this->faker->randomFloat(2, 0, 0.4),
        ]);
    }

    /**
     * Set the moderatable model.
     */
    public function forModel($model): static
    {
        return $this->state(fn (array $attributes) => [
            'moderatable_type' => get_class($model),
            'moderatable_id' => $model->id,
        ]);
    }
}
