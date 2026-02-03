<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContactResourceSuggestion extends Model
{
    protected $fillable = [
        'org_id',
        'contact_type',
        'contact_id',
        'resource_id',
        'suggestion_source',
        'relevance_score',
        'matching_criteria',
        'ai_rationale',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'assignment_id',
    ];

    protected $casts = [
        'matching_criteria' => 'array',
        'relevance_score' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    // Suggestion sources
    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_AI_RECOMMENDATION = 'ai_recommendation';

    public const SOURCE_RULE_BASED = 'rule_based';

    public const SOURCE_PEER_SUCCESS = 'peer_success';

    // Status values
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_ASSIGNED = 'assigned';

    /**
     * Get the contact (Participant or User).
     */
    public function contact(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the suggested resource.
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * Get the user who reviewed this suggestion.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the resource assignment (if accepted).
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ResourceAssignment::class, 'assignment_id');
    }

    /**
     * Scope to filter pending suggestions.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by contact.
     */
    public function scopeForContact($query, string $type, int $id)
    {
        return $query->where('contact_type', $type)->where('contact_id', $id);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Accept the suggestion and create an assignment.
     */
    public function accept(int $userId): ResourceAssignment
    {
        $assignment = ResourceAssignment::create([
            'resource_id' => $this->resource_id,
            'participant_id' => $this->contact_type === Participant::class ? $this->contact_id : null,
            'assigned_by' => $userId,
            'assigned_at' => now(),
            'status' => 'assigned',
        ]);

        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'assignment_id' => $assignment->id,
        ]);

        return $assignment;
    }

    /**
     * Decline the suggestion.
     */
    public function decline(int $userId, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_DECLINED,
            'reviewed_by' => $userId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }
}
