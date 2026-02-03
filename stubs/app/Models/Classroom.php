<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class LearningGroup extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'learning_groups';

    protected $fillable = [
        'org_id',
        'department_id',
        'name',
        'subject',
        'level',
        'room_number',
        'primary_instructor_id',
        'co_instructor_ids',
        'participant_ids',
        'max_capacity',
        'meeting_schedule',
        'strategy_ids',
        'active',
    ];

    protected $casts = [
        'co_instructor_ids' => 'array',
        'participant_ids' => 'array',
        'meeting_schedule' => 'array',
        'strategy_ids' => 'array',
        'level' => 'integer',
        'max_capacity' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Get the organization (organization).
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Get the primary instructor.
     */
    public function primaryInstructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_instructor_id');
    }

    /**
     * Get the participant count.
     */
    public function getLearnerCountAttribute(): int
    {
        return count($this->participant_ids ?? []);
    }

    /**
     * Check if learning_group is at capacity.
     */
    public function isAtCapacity(): bool
    {
        return $this->learner_count >= $this->max_capacity;
    }

    /**
     * Add a participant to the learning_group.
     */
    public function addLearner(string $participantId): bool
    {
        if ($this->isAtCapacity()) {
            return false;
        }

        $participantIds = $this->participant_ids ?? [];
        if (! in_array($participantId, $participantIds)) {
            $participantIds[] = $participantId;
            $this->update(['participant_ids' => $participantIds]);
        }

        return true;
    }

    /**
     * Remove a participant from the learning_group.
     */
    public function removeLearner(string $participantId): void
    {
        $participantIds = $this->participant_ids ?? [];
        $participantIds = array_filter($participantIds, fn ($id) => $id !== $participantId);
        $this->update(['participant_ids' => array_values($participantIds)]);
    }

    /**
     * Scope to filter active learning_groups.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter by subject.
     */
    public function scopeSubject($query, string $subject)
    {
        return $query->where('subject', $subject);
    }

    /**
     * Scope to filter by instructor.
     */
    public function scopeForInstructor($query, string $instructorId)
    {
        return $query->where(function ($q) use ($instructorId) {
            $q->where('primary_instructor_id', $instructorId)
                ->orWhere('co_instructor_ids', $instructorId);
        });
    }
}
