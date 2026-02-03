<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Learner extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'learners';

    protected $fillable = [
        'user_id',
        'org_id',
        'district_id',
        'consultant_id',
        'learner_number',
        'grade_level',
        'graduation_year',
        'current_classrooms',
        'date_of_birth',
        'gender',
        'ethnicity',
        'primary_language',
        'iep_status',
        'section_504_status',
        'ell_status',
        'free_reduced_lunch',
        'emergency_contacts',
        'parent_ids',
        'teacher_ids',
        'mentor_ids',
        'counselor_id',
        'tags',
        'custom_fields',
        'enrollment_date',
        'withdrawal_date',
        'status',
    ];

    protected $casts = [
        'current_classrooms' => 'array',
        'date_of_birth' => 'date',
        'emergency_contacts' => 'array',
        'parent_ids' => 'array',
        'teacher_ids' => 'array',
        'mentor_ids' => 'array',
        'tags' => 'array',
        'custom_fields' => 'array',
        'enrollment_date' => 'date',
        'withdrawal_date' => 'date',
        'iep_status' => 'boolean',
        'section_504_status' => 'boolean',
        'ell_status' => 'boolean',
        'free_reduced_lunch' => 'boolean',
    ];

    /**
     * Get the associated user account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the learner's organization (organization).
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the learner's district.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'district_id');
    }

    /**
     * Get survey attempts about this learner.
     */
    public function surveyAttempts(): HasMany
    {
        return $this->hasMany(SurveyAttempt::class, 'surveyee_id');
    }

    /**
     * Get resource assignments for this learner.
     */
    public function resourceAssignments(): HasMany
    {
        return $this->hasMany(ResourceAssignment::class, 'assigned_to_user_id', 'user_id');
    }

    /**
     * Get the learner's full name from user.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user?->full_name ?? 'Unknown Learner';
    }

    /**
     * Get the latest survey attempt.
     */
    public function getLatestSurveyAttemptAttribute()
    {
        return $this->surveyAttempts()
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->first();
    }

    /**
     * Get the current risk level based on latest survey.
     */
    public function getCurrentRiskLevelAttribute(): string
    {
        $latest = $this->latest_survey_attempt;

        if (! $latest) {
            return 'unknown';
        }

        return $latest->survey_attempt_result['overall_risk_level'] ?? 'unknown';
    }

    /**
     * Scope to filter active learners.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by grade level.
     */
    public function scopeGradeLevel($query, int $grade)
    {
        return $query->where('grade_level', $grade);
    }

    /**
     * Scope to filter learners with IEP.
     */
    public function scopeWithIep($query)
    {
        return $query->where('iep_status', true);
    }

    /**
     * Scope to filter by teacher.
     */
    public function scopeForTeacher($query, string $teacherId)
    {
        return $query->where('teacher_ids', $teacherId);
    }

    /**
     * Scope to filter by parent.
     */
    public function scopeForParent($query, string $parentId)
    {
        return $query->where('parent_ids', $parentId);
    }
}
