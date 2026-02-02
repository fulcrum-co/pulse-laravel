<?php

namespace Tests\Feature\Moderation;

use App\Models\ContentModerationResult;
use App\Models\MiniCourse;
use App\Models\ModerationQueueItem;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $reviewer;
    protected User $contentCreator;
    protected Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = Organization::factory()->create();
        $this->reviewer = User::factory()->create([
            'role' => 'moderator',
        ]);
        $this->contentCreator = User::factory()->create([
            'role' => 'content_creator',
        ]);
    }

    /** @test */
    public function reviewer_can_approve_content(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->pendingReview()
            ->create();

        $queueItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->inReview($this->reviewer)
            ->forContent($course)
            ->create();

        $this->actingAs($this->reviewer)
            ->post(route('moderation.approve', $queueItem), [
                'feedback' => 'Great content! Approved for publication.',
            ])
            ->assertRedirect();

        $queueItem->refresh();
        $course->refresh();

        $this->assertEquals('completed', $queueItem->status);
        $this->assertEquals('published', $course->status);
        $this->assertNotNull($course->published_at);

        $this->assertDatabaseHas('content_moderation_results', [
            'moderatable_type' => MiniCourse::class,
            'moderatable_id' => $course->id,
            'status' => 'approved',
            'reviewer_id' => $this->reviewer->id,
        ]);
    }

    /** @test */
    public function reviewer_can_reject_content(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->pendingReview()
            ->create();

        $queueItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->inReview($this->reviewer)
            ->forContent($course)
            ->create();

        $this->actingAs($this->reviewer)
            ->post(route('moderation.reject', $queueItem), [
                'feedback' => 'Content contains inappropriate material.',
                'flagged_issues' => ['inappropriate_content', 'policy_violation'],
            ])
            ->assertRedirect();

        $queueItem->refresh();
        $course->refresh();

        $this->assertEquals('completed', $queueItem->status);
        $this->assertEquals('rejected', $course->status);

        $this->assertDatabaseHas('content_moderation_results', [
            'moderatable_type' => MiniCourse::class,
            'moderatable_id' => $course->id,
            'status' => 'rejected',
            'reviewer_id' => $this->reviewer->id,
        ]);
    }

    /** @test */
    public function reviewer_can_request_revisions(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->pendingReview()
            ->create();

        $queueItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->inReview($this->reviewer)
            ->forContent($course)
            ->create();

        $suggestions = [
            'Improve clarity in the introduction',
            'Add more examples in section 2',
            'Fix grammatical errors in conclusion',
        ];

        $this->actingAs($this->reviewer)
            ->post(route('moderation.request-revision', $queueItem), [
                'feedback' => 'Good start, but needs some improvements.',
                'suggestions' => $suggestions,
            ])
            ->assertRedirect();

        $queueItem->refresh();
        $course->refresh();

        $this->assertEquals('completed', $queueItem->status);
        $this->assertEquals('needs_revision', $course->status);

        $moderationResult = ContentModerationResult::where('moderatable_id', $course->id)->first();
        $this->assertEquals('needs_revision', $moderationResult->status);
        $this->assertEquals($suggestions, $moderationResult->suggestions);
    }

    /** @test */
    public function content_creator_is_notified_of_moderation_decision(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->pendingReview()
            ->create(['created_by' => $this->contentCreator->id]);

        $queueItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->inReview($this->reviewer)
            ->forContent($course)
            ->create();

        $this->actingAs($this->reviewer)
            ->post(route('moderation.approve', $queueItem), [
                'feedback' => 'Approved!',
            ]);

        $this->assertDatabaseHas('user_notifications', [
            'user_id' => $this->contentCreator->id,
            'type' => 'moderation_decision',
            'data->course_id' => $course->id,
            'data->decision' => 'approved',
        ]);
    }

    /** @test */
    public function revised_content_goes_back_to_moderation_queue(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->create([
                'status' => 'needs_revision',
                'created_by' => $this->contentCreator->id,
            ]);

        // Simulate content creator making revisions and resubmitting
        $this->actingAs($this->contentCreator)
            ->post(route('courses.resubmit', $course), [
                'title' => 'Updated Course Title',
                'description' => 'Updated description with improvements.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('moderation_queue_items', [
            'content_type' => MiniCourse::class,
            'content_id' => $course->id,
            'status' => 'pending',
        ]);

        $course->refresh();
        $this->assertEquals('pending_review', $course->status);
    }

    /** @test */
    public function moderation_history_is_preserved(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->pendingReview()
            ->create();

        // First moderation - request revision
        $queueItem1 = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->inReview($this->reviewer)
            ->forContent($course)
            ->create();

        $this->actingAs($this->reviewer)
            ->post(route('moderation.request-revision', $queueItem1), [
                'feedback' => 'Needs work.',
                'suggestions' => ['Improve clarity'],
            ]);

        // Second moderation - approve
        $course->update(['status' => 'pending_review']);
        $queueItem2 = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->inReview($this->reviewer)
            ->forContent($course)
            ->create();

        $this->actingAs($this->reviewer)
            ->post(route('moderation.approve', $queueItem2), [
                'feedback' => 'Much better! Approved.',
            ]);

        // Should have 2 moderation results
        $results = ContentModerationResult::where('moderatable_id', $course->id)
            ->orderBy('created_at')
            ->get();

        $this->assertCount(2, $results);
        $this->assertEquals('needs_revision', $results[0]->status);
        $this->assertEquals('approved', $results[1]->status);
    }

    /** @test */
    public function workflow_enforces_required_feedback(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->pendingReview()
            ->create();

        $queueItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->inReview($this->reviewer)
            ->forContent($course)
            ->create();

        // Reject without feedback should fail
        $this->actingAs($this->reviewer)
            ->post(route('moderation.reject', $queueItem), [
                'flagged_issues' => ['inappropriate_content'],
                // Missing 'feedback'
            ])
            ->assertSessionHasErrors('feedback');

        $queueItem->refresh();
        $this->assertEquals('in_review', $queueItem->status);
    }

    /** @test */
    public function reviewer_can_add_scores_during_review(): void
    {
        $course = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->pendingReview()
            ->create();

        $queueItem = ModerationQueueItem::factory()
            ->forOrganization($this->organization)
            ->inReview($this->reviewer)
            ->forContent($course)
            ->create();

        $this->actingAs($this->reviewer)
            ->post(route('moderation.approve', $queueItem), [
                'feedback' => 'Excellent content!',
                'clarity_score' => 0.95,
                'engagement_score' => 0.90,
                'accuracy_score' => 0.98,
                'appropriateness_score' => 1.0,
            ])
            ->assertRedirect();

        $result = ContentModerationResult::where('moderatable_id', $course->id)->first();

        $this->assertEquals(0.95, $result->clarity_score);
        $this->assertEquals(0.90, $result->engagement_score);
        $this->assertEquals(0.98, $result->accuracy_score);
        $this->assertEquals(1.0, $result->appropriateness_score);
    }

    /** @test */
    public function bulk_approval_works_for_multiple_items(): void
    {
        $courses = MiniCourse::factory()
            ->forOrganization($this->organization)
            ->pendingReview()
            ->count(3)
            ->create();

        $queueItems = $courses->map(function ($course) {
            return ModerationQueueItem::factory()
                ->forOrganization($this->organization)
                ->inReview($this->reviewer)
                ->forContent($course)
                ->create();
        });

        $this->actingAs($this->reviewer)
            ->post(route('moderation.bulk-approve'), [
                'queue_item_ids' => $queueItems->pluck('id')->toArray(),
                'feedback' => 'Bulk approved after review.',
            ])
            ->assertRedirect();

        foreach ($queueItems as $item) {
            $item->refresh();
            $this->assertEquals('completed', $item->status);
        }

        foreach ($courses as $course) {
            $course->refresh();
            $this->assertEquals('published', $course->status);
        }
    }
}
