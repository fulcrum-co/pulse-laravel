<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Classroom extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'classrooms';

    protected $fillable = [
        'org_id',
        'department_id',
        'name',
        'subject',
        'grade_level',
        'room_number',
        'primary_teacher_id',
        'co_teacher_ids',
        'learner_ids',
        'max_capacity',
        'meeting_schedule',
        'strategy_ids',
        'active',
    ];

    protected $casts = [
        'co_teacher_ids' => 'array',
        'learner_ids' => 'array',
        'meeting_schedule' => 'array',
        'strategy_ids' => 'array',
        'grade_level' => 'integer',
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
     * Get the primary teacher.
     */
    public function primaryTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_teacher_id');
    }

    /**
     * Get the learner count.
     */
    public function getLearnerCountAttribute(): int
    {
        return count($this->learner_ids ?? []);
    }

    /**
     * Check if classroom is at capacity.
     */
    public function isAtCapacity(): bool
    {
        return $this->learner_count >= $this->max_capacity;
    }

    /**
     * Add a learner to the classroom.
     */
    public function addLearner(string $learnerId): bool
    {
        if ($this->isAtCapacity()) {
            return false;
        }

        $learnerIds = $this->learner_ids ?? [];
        if (! in_array($learnerId, $learnerIds)) {
            $learnerIds[] = $learnerId;
            $this->update(['learner_ids' => $learnerIds]);
        }

        return true;
    }

    /**
     * Remove a learner from the classroom.
     */
    public function removeLearner(string $learnerId): void
    {
        $learnerIds = $this->learner_ids ?? [];
        $learnerIds = array_filter($learnerIds, fn ($id) => $id !== $learnerId);
        $this->update(['learner_ids' => array_values($learnerIds)]);
    }

    /**
     * Scope to filter active classrooms.
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
     * Scope to filter by teacher.
     */
    public function scopeForTeacher($query, string $teacherId)
    {
        return $query->where(function ($q) use ($teacherId) {
            $q->where('primary_teacher_id', $teacherId)
                ->orWhere('co_teacher_ids', $teacherId);
        });
    }
}
