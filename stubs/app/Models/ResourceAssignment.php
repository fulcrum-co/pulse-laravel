<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class ResourceAssignment extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'resource_assignments';

    protected $fillable = [
        'resource_id',
        'assigned_to_user_id',
        'assigned_by_user_id',
        'org_id',
        'assignment_reason',
        'related_survey_attempt_id',
        'auto_assigned',
        'status',
        'started_at',
        'completed_at',
        'progress_percentage',
        'rating',
        'feedback_text',
    ];

    protected $casts = [
        'auto_assigned' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percentage' => 'integer',
        'rating' => 'integer',
    ];

    /**
     * Get the resource.
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'resource_id');
    }

    /**
     * Get the user this was assigned to.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Get the user who made the assignment.
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the related survey attempt.
     */
    public function surveyAttempt(): BelongsTo
    {
        return $this->belongsTo(SurveyAttempt::class, 'related_survey_attempt_id');
    }

    /**
     * Mark as started.
     */
    public function markStarted(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark as completed.
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);

        // Update resource stats
        $this->resource->increment('completion_count');
    }

    /**
     * Update progress.
     */
    public function updateProgress(int $percentage): void
    {
        $this->update([
            'progress_percentage' => min(100, max(0, $percentage)),
        ]);
    }

    /**
     * Add rating and feedback.
     */
    public function addFeedback(int $rating, ?string $feedback = null): void
    {
        $this->update([
            'rating' => $rating,
            'feedback_text' => $feedback,
        ]);

        // Update resource average rating
        $avgRating = ResourceAssignment::where('resource_id', $this->resource_id)
            ->whereNotNull('rating')
            ->avg('rating');

        $this->resource->update(['avg_rating' => $avgRating]);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter auto-assigned.
     */
    public function scopeAutoAssigned($query)
    {
        return $query->where('auto_assigned', true);
    }

    /**
     * Scope to filter completed.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
