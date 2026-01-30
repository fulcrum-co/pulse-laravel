<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class MiniCourseSuggestion extends Model
{
    // Suggestion sources
    public const SOURCE_AI_GENERATED = 'ai_generated';
    public const SOURCE_AI_RECOMMENDED = 'ai_recommended';
    public const SOURCE_RULE_BASED = 'rule_based';
    public const SOURCE_PEER_SUCCESS = 'peer_success';
    public const SOURCE_MANUAL = 'manual';

    // Statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_AUTO_ENROLLED = 'auto_enrolled';

    protected $fillable = [
        'org_id',
        'contact_type',
        'contact_id',
        'mini_course_id',
        'suggestion_source',
        'relevance_score',
        'trigger_signals',
        'ai_rationale',
        'ai_explanation',
        'intended_outcomes',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'enrollment_id',
    ];

    protected $casts = [
        'trigger_signals' => 'array',
        'ai_explanation' => 'array',
        'intended_outcomes' => 'array',
        'relevance_score' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    /**
     * Get available suggestion sources.
     */
    public static function getSuggestionSources(): array
    {
        return [
            self::SOURCE_AI_GENERATED => 'AI Generated',
            self::SOURCE_AI_RECOMMENDED => 'AI Recommended',
            self::SOURCE_RULE_BASED => 'Rule Based',
            self::SOURCE_PEER_SUCCESS => 'Peer Success',
            self::SOURCE_MANUAL => 'Manual',
        ];
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_ACCEPTED => 'Accepted',
            self::STATUS_DECLINED => 'Declined',
            self::STATUS_AUTO_ENROLLED => 'Auto Enrolled',
        ];
    }

    /**
     * Organization relationship.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Polymorphic contact relationship (Student or User).
     */
    public function contact(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The suggested mini-course.
     */
    public function miniCourse(): BelongsTo
    {
        return $this->belongsTo(MiniCourse::class);
    }

    /**
     * Who reviewed the suggestion.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * The resulting enrollment (if accepted).
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(MiniCourseEnrollment::class);
    }

    /**
     * Scope to pending suggestions.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to accepted suggestions.
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope by status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by source.
     */
    public function scopeFromSource(Builder $query, string $source): Builder
    {
        return $query->where('suggestion_source', $source);
    }

    /**
     * Scope for a specific contact.
     */
    public function scopeForContact(Builder $query, string $type, int $id): Builder
    {
        return $query->where('contact_type', $type)->where('contact_id', $id);
    }

    /**
     * Scope by organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope by minimum relevance score.
     */
    public function scopeMinRelevance(Builder $query, float $score): Builder
    {
        return $query->where('relevance_score', '>=', $score);
    }

    /**
     * Check if suggestion is AI-generated.
     */
    public function isAiGenerated(): bool
    {
        return in_array($this->suggestion_source, [self::SOURCE_AI_GENERATED, self::SOURCE_AI_RECOMMENDED]);
    }

    /**
     * Accept the suggestion and create enrollment.
     */
    public function accept(int $userId): MiniCourseEnrollment
    {
        // Create enrollment
        $enrollment = MiniCourseEnrollment::create([
            'mini_course_id' => $this->mini_course_id,
            'mini_course_version_id' => $this->miniCourse->current_version_id,
            'student_id' => $this->contact_id,
            'enrolled_by' => $userId,
            'enrollment_source' => match ($this->suggestion_source) {
                self::SOURCE_AI_GENERATED, self::SOURCE_AI_RECOMMENDED => MiniCourseEnrollment::SOURCE_AI_SUGGESTED,
                self::SOURCE_RULE_BASED => MiniCourseEnrollment::SOURCE_RULE_TRIGGERED,
                default => MiniCourseEnrollment::SOURCE_MANUAL,
            },
            'suggestion_id' => $this->id,
            'status' => MiniCourseEnrollment::STATUS_ENROLLED,
        ]);

        // Update suggestion
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'enrollment_id' => $enrollment->id,
        ]);

        return $enrollment;
    }

    /**
     * Decline the suggestion.
     */
    public function decline(int $userId, string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_DECLINED,
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    /**
     * Auto-enroll (for auto-create feature).
     */
    public function autoEnroll(): MiniCourseEnrollment
    {
        // Create enrollment
        $enrollment = MiniCourseEnrollment::create([
            'mini_course_id' => $this->mini_course_id,
            'mini_course_version_id' => $this->miniCourse->current_version_id,
            'student_id' => $this->contact_id,
            'enrollment_source' => MiniCourseEnrollment::SOURCE_AI_SUGGESTED,
            'suggestion_id' => $this->id,
            'status' => MiniCourseEnrollment::STATUS_ENROLLED,
        ]);

        // Update suggestion
        $this->update([
            'status' => self::STATUS_AUTO_ENROLLED,
            'enrollment_id' => $enrollment->id,
        ]);

        return $enrollment;
    }

    /**
     * Get explanation summary.
     */
    public function getExplanationSummaryAttribute(): string
    {
        if ($this->ai_rationale) {
            return $this->ai_rationale;
        }

        if ($this->ai_explanation && isset($this->ai_explanation['summary'])) {
            return $this->ai_explanation['summary'];
        }

        return 'Suggested based on student data analysis.';
    }

    /**
     * Get trigger signal descriptions.
     */
    public function getSignalDescriptionsAttribute(): array
    {
        $descriptions = [];

        foreach ($this->trigger_signals ?? [] as $signal) {
            if (is_array($signal) && isset($signal['description'])) {
                $descriptions[] = $signal['description'];
            } elseif (is_string($signal)) {
                $descriptions[] = $signal;
            }
        }

        return $descriptions;
    }

    /**
     * Get formatted relevance score.
     */
    public function getFormattedRelevanceAttribute(): string
    {
        return round($this->relevance_score ?? 0) . '%';
    }
}
