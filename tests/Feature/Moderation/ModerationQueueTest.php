<?php

namespace Tests\Feature\Moderation;

use App\Models\MiniCourse;
use App\Models\ModerationQueueItem;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationQueueTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $reviewer;
    protected User $contentCreator;
    protected Organization $organization;

    protected function setUp(): void
    {
        direct_supervisor::setUp();

        $this->organization = Organization::factory()->create();
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->reviewer = User::factory()->create([
            'role' => 'moderator',
        ]);
        $this->contentCreator = User::factory()->create([
            'role' => 'content_creator',
        ]);
    }

    /** @test */
    public function content_is_added_to_moderation_queue_when_submitted_for_review(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->draft()
            ->create(['created_by' => $this->contentCreator->id]);

        $this->actingAs($this->contentCreator)
            ->post(route('courses.submit-for-review', $course))
            ->assertRedirect();

        $this->assertDatabaseHas('moderation_queue_items', [
            'content_type' => MiniCourse::class,
            'content_id' => $course->id,
            'status' => 'pending',
            'submitted_by' => $this->contentCreator->id,
        ]);
    }

    /** @test */
    public function queue_items_are_sorted_by_priority_and_sla(): void
    {
        // Create items with different priorities
        $urgentItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->urgent()
            ->pending()
            ->create();

        $normalItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->pending()
            ->create(['priority' => 'normal']);

        $overdueItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->overdue()
            ->create();

        $this->actingAs($this->reviewer)
            ->get(route('moderation.queue'))
            ->assertSuccessful()
            ->assertSeeInOrder([
                $overdueItem->id,
                $urgentItem->id,
                $normalItem->id,
            ]);
    }

    /** @test */
    public function reviewer_can_claim_queue_item(): void
    {
        $queueItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->pending()
            ->create();

        $this->actingAs($this->reviewer)
            ->post(route('moderation.claim', $queueItem))
            ->assertRedirect();

        $queueItem->refresh();

        $this->assertEquals('in_review', $queueItem->status);
        $this->assertEquals($this->reviewer->id, $queueItem->assigned_to);
        $this->assertNotNull($queueItem->started_at);
    }

    /** @test */
    public function reviewer_can_release_claimed_item(): void
    {
        $queueItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->inReview($this->reviewer)
            ->create();

        $this->actingAs($this->reviewer)
            ->post(route('moderation.release', $queueItem))
            ->assertRedirect();

        $queueItem->refresh();

        $this->assertEquals('pending', $queueItem->status);
        $this->assertNull($queueItem->assigned_to);
    }

    /** @test */
    public function sla_status_is_calculated_correctly(): void
    {
        // On-time item
        $onTimeItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->pending()
            ->create([
                'sla_hours' => 24,
                'sla_deadline' => now()->addHours(12),
            ]);

        // Warning item (close to deadline)
        $warningItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->pending()
            ->create([
                'sla_hours' => 24,
                'sla_deadline' => now()->addHours(2),
            ]);

        // Overdue item
        $overdueItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->pending()
            ->create([
                'sla_hours' => 24,
                'sla_deadline' => now()->subHours(2),
            ]);

        $this->assertEquals('on_time', $onTimeItem->sla_status);
        $this->assertEquals('warning', $warningItem->sla_status);
        $this->assertEquals('overdue', $overdueItem->sla_status);
    }

    /** @test */
    public function admin_can_escalate_queue_item(): void
    {
        $queueItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->pending()
            ->create();

        $this->actingAs($this->admin)
            ->post(route('moderation.escalate', $queueItem), [
                'reason' => 'Requires senior review due to policy concerns',
            ])
            ->assertRedirect();

        $queueItem->refresh();

        $this->assertEquals('escalated', $queueItem->status);
        $this->assertEquals('urgent', $queueItem->priority);
        $this->assertStringContainsString('policy concerns', $queueItem->notes);
    }

    /** @test */
    public function content_creator_cannot_access_moderation_queue(): void
    {
        $this->actingAs($this->contentCreator)
            ->get(route('moderation.queue'))
            ->assertForbidden();
    }

    /** @test */
    public function reviewer_cannot_claim_already_claimed_item(): void
    {
        $anotherReviewer = User::factory()->create(['role' => 'moderator']);

        $queueItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->inReview($anotherReviewer)
            ->create();

        $this->actingAs($this->reviewer)
            ->post(route('moderation.claim', $queueItem))
            ->assertStatus(422);

        $queueItem->refresh();

        // Should still be assigned to original reviewer
        $this->assertEquals($anotherReviewer->id, $queueItem->assigned_to);
    }

    /** @test */
    public function completed_items_are_removed_from_active_queue(): void
    {
        $completedItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->completed()
            ->create();

        $pendingItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->pending()
            ->create();

        $this->actingAs($this->reviewer)
            ->get(route('moderation.queue'))
            ->assertSuccessful()
            ->assertSee($pendingItem->id)
            ->assertDontSee($completedItem->id);
    }

    /** @test */
    public function queue_can_be_filtered_by_content_type(): void
    {
        $courseItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->pending()
            ->create(['content_type' => MiniCourse::class]);

        $this->actingAs($this->reviewer)
            ->get(route('moderation.queue', ['content_type' => 'mini_course']))
            ->assertSuccessful()
            ->assertSee($courseItem->id);
    }

    /** @test */
    public function queue_shows_correct_counts_by_status(): void
    {
        ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->pending()
            ->count(5)
            ->create();

        ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->inReview($this->reviewer)
            ->count(3)
            ->create();

        ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->completed()
            ->count(10)
            ->create();

        $this->actingAs($this->reviewer)
            ->get(route('moderation.queue.stats'))
            ->assertSuccessful()
            ->assertJson([
                'pending' => 5,
                'in_review' => 3,
                'completed_today' => 10,
            ]);
    }
}
