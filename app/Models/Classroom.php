<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'student_ids',
        'max_capacity',
        'meeting_schedule',
        'strategy_ids',
        'active',
    ];

    protected $casts = [
        'co_teacher_ids' => 'array',
        'student_ids' => 'array',
        'meeting_schedule' => 'array',
        'strategy_ids' => 'array',
        'grade_level' => 'integer',
        'max_capacity' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Get the school (organization).
     */
    public function school(): BelongsTo
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
     * Get the student count.
     */
    public function getStudentCountAttribute(): int
    {
        return count($this->student_ids ?? []);
    }

    /**
     * Check if classroom is at capacity.
     */
    public function isAtCapacity(): bool
    {
        return $this->student_count >= $this->max_capacity;
    }

    /**
     * Add a student to the classroom.
     */
    public function addStudent(string $studentId): bool
    {
        if ($this->isAtCapacity()) {
            return false;
        }

        $studentIds = $this->student_ids ?? [];
        if (!in_array($studentId, $studentIds)) {
            $studentIds[] = $studentId;
            $this->update(['student_ids' => $studentIds]);
        }

        return true;
    }

    /**
     * Remove a student from the classroom.
     */
    public function removeStudent(string $studentId): void
    {
        $studentIds = $this->student_ids ?? [];
        $studentIds = array_filter($studentIds, fn($id) => $id !== $studentId);
        $this->update(['student_ids' => array_values($studentIds)]);
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
