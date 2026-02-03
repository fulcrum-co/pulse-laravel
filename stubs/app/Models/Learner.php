<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Participant extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'participants';

    protected $fillable = [
        'user_id',
        'org_id',
        'section_id',
        'consultant_id',
        'learner_number',
        'level',
        'graduation_year',
        'current_learning_groups',
        'date_of_birth',
        'gender',
        'ethnicity',
        'primary_language',
        'iep_status',
        'section_504_status',
        'ell_status',
        'free_reduced_lunch',
        'emergency_contacts',
        'direct_supervisor_ids',
        'instructor_ids',
        'mentor_ids',
        'support_person_id',
        'tags',
        'custom_fields',
        'enrollment_date',
        'withdrawal_date',
        'status',
    ];

    protected $casts = [
        'current_learning_groups' => 'array',
        'date_of_birth' => 'date',
        'emergency_contacts' => 'array',
        'direct_supervisor_ids' => 'array',
        'instructor_ids' => 'array',
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
     * Get the participant's organization (organization).
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the participant's section.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'section_id');
    }

    /**
     * Get survey attempts about this participant.
     */
    public function surveyAttempts(): HasMany
    {
        return $this->hasMany(SurveyAttempt::class, 'surveyee_id');
    }

    /**
     * Get resource assignments for this participant.
     */
    public function resourceAssignments(): HasMany
    {
        return $this->hasMany(ResourceAssignment::class, 'assigned_to_user_id', 'user_id');
    }

    /**
     * Get the participant's full name from user.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user?->full_name ?? 'Unknown Participant';
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
     * Scope to filter active participants.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by level.
     */
    public function scopeLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope to filter participants with support plans.
     */
    public function scopeWithSupportPlan($query)
    {
        return $query->where('iep_status', true);
    }

    /**
     * Scope to filter by instructor.
     */
    public function scopeForInstructor($query, string $instructorId)
    {
        return $query->where('instructor_ids', $instructorId);
    }

    /**
     * Scope to filter by direct supervisor.
     */
    public function scopeForSupervisor($query, string $supervisorId)
    {
        return $query->where('direct_supervisor_ids', $supervisorId);
    }
}
