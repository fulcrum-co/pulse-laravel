<?php

namespace Database\Factories;

use App\Models\ModerationQueueItem;
use App\Models\MiniCourse;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ModerationQueueItem>
 */
class ModerationQueueItemFactory extends Factory
{
    protected $model = ModerationQueueItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['pending', 'in_review', 'completed', 'escalated'];
        $priorities = ['low', 'normal', 'high', 'urgent'];

        return [
            'org_id' => Organization::factory(),
            'content_type' => MiniCourse::class,
            'content_id' => MiniCourse::factory(),
            'status' => $this->faker->randomElement($statuses),
            'priority' => $this->faker->randomElement($priorities),
            'submitted_by' => User::factory(),
            'assigned_to' => null,
            'sla_hours' => 24,
            'sla_deadline' => now()->addHours(24),
            'started_at' => null,
            'completed_at' => null,
            'notes' => null,
            'metadata' => [],
        ];
    }

    /**
     * Indicate that the queue item is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'assigned_to' => null,
            'started_at' => null,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the queue item is in review.
     */
    public function inReview(User $reviewer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_review',
            'assigned_to' => $reviewer?->id ?? User::factory(),
            'started_at' => now(),
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the queue item is completed.
     */
    public function completed(): static
    {
        $startedAt = now()->subHours($this->faker->numberBetween(1, 12));

        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'assigned_to' => User::factory(),
            'started_at' => $startedAt,
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the queue item is escalated.
     */
    public function escalated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'escalated',
            'priority' => 'urgent',
            'notes' => 'Escalated due to complexity or policy concerns.',
        ]);
    }

    /**
     * Set the priority to urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'urgent',
            'sla_hours' => 4,
            'sla_deadline' => now()->addHours(4),
        ]);
    }

    /**
     * Set the priority to high.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 'high',
            'sla_hours' => 12,
            'sla_deadline' => now()->addHours(12),
        ]);
    }

    /**
     * Make the SLA overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'sla_deadline' => now()->subHours($this->faker->numberBetween(1, 24)),
            'created_at' => now()->subHours($this->faker->numberBetween(25, 48)),
        ]);
    }

    /**
     * Assign to a specific reviewer.
     */
    public function assignedTo(User $reviewer): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => $reviewer->id,
            'status' => 'in_review',
            'started_at' => now(),
        ]);
    }

    /**
     * Set the content being moderated.
     */
    public function forContent($content): static
    {
        return $this->state(fn (array $attributes) => [
            'content_type' => get_class($content),
            'content_id' => $content->id,
            'org_id' => $content->org_id ?? Organization::factory(),
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
