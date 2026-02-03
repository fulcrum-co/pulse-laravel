<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use SoftDeletes;

    protected $table = 'participants';

    protected $fillable = [
        'user_id',
        'org_id',
        'participant_number',
        'level',
        'date_of_birth',
        'gender',
        'ethnicity',
        'iep_status',
        'ell_status',
        'free_reduced_lunch',
        'enrollment_status',
        'enrollment_date',
        'risk_level',
        'risk_score',
        'tags',
        'custom_fields',
        'support_person_user_id',
        'homeroom_learning_group_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'iep_status' => 'boolean',
        'ell_status' => 'boolean',
        'free_reduced_lunch' => 'boolean',
        'tags' => 'array',
        'custom_fields' => 'array',
        'risk_score' => 'decimal:2',
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
     * Get the participant's support_person.
     */
    public function supportPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'support_person_user_id');
    }

    /**
     * Get the participant's homeroom learning_group.
     */
    public function homeroomLearningGroup(): BelongsTo
    {
        return $this->belongsTo(LearningGroup::class, 'homeroom_learning_group_id');
    }

    /**
     * Get the learning_groups the participant is enrolled in.
     */
    public function learningGroups(): BelongsToMany
    {
        return $this->belongsToMany(LearningGroup::class, 'learning_group_participant')->withTimestamps();
    }

    /**
     * Get survey attempts for this participant.
     */
    public function surveyAttempts(): HasMany
    {
        return $this->hasMany(SurveyAttempt::class);
    }

    /**
     * Get resource assignments for this participant.
     */
    public function resourceAssignments(): HasMany
    {
        return $this->hasMany(ResourceAssignment::class);
    }

    /**
     * Get conversations for this participant.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Get metrics for this participant (contact view).
     */
    public function metrics(): MorphMany
    {
        return $this->morphMany(ContactMetric::class, 'contact');
    }

    /**
     * Get notes for this participant (contact view).
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(ContactNote::class, 'contact');
    }

    /**
     * Get resource suggestions for this participant.
     */
    public function resourceSuggestions(): MorphMany
    {
        return $this->morphMany(ContactResourceSuggestion::class, 'contact');
    }

    /**
     * Get mini-course enrollments for this participant.
     */
    public function miniCourseEnrollments(): HasMany
    {
        return $this->hasMany(MiniCourseEnrollment::class);
    }

    /**
     * Get active mini-course enrollments.
     */
    public function activeCourseEnrollments(): HasMany
    {
        return $this->hasMany(MiniCourseEnrollment::class)
            ->whereIn('status', [
                MiniCourseEnrollment::STATUS_ENROLLED,
                MiniCourseEnrollment::STATUS_IN_PROGRESS,
            ]);
    }

    /**
     * Get mini-course suggestions for this participant.
     */
    public function courseSuggestions(): MorphMany
    {
        return $this->morphMany(MiniCourseSuggestion::class, 'contact');
    }

    /**
     * Get pending mini-course suggestions.
     */
    public function pendingCourseSuggestions(): MorphMany
    {
        return $this->morphMany(MiniCourseSuggestion::class, 'contact')
            ->where('status', MiniCourseSuggestion::STATUS_PENDING);
    }

    /**
     * Get strategic plans targeting this participant.
     */
    public function strategicPlans(): HasMany
    {
        return $this->hasMany(StrategicPlan::class, 'target_id')
            ->where('target_type', self::class);
    }

    /**
     * Get heat map data for participant plan progress.
     */
    public function getHeatMapData(string $organizationYear): array
    {
        $metrics = $this->metrics()
            ->forOrganizationYear($organizationYear)
            ->whereIn('metric_category', [
                ContactMetric::CATEGORY_ACADEMICS,
                ContactMetric::CATEGORY_ATTENDANCE,
                ContactMetric::CATEGORY_BEHAVIOR,
                ContactMetric::CATEGORY_LIFE_SKILLS,
            ])
            ->get()
            ->groupBy(['metric_category', 'quarter']);

        return $metrics->toArray();
    }

    /**
     * Get the participant's full name from user.
     */
    public function getFullNameAttribute(): string
    {
        return $this->user?->full_name ?? 'Unknown Participant';
    }

    /**
     * Get the latest completed survey attempt.
     */
    public function getLatestSurveyAttemptAttribute()
    {
        return $this->surveyAttempts()
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->first();
    }

    /**
     * Scope to filter by risk level.
     */
    public function scopeRiskLevel($query, string $level)
    {
        return $query->where('risk_level', $level);
    }

    /**
     * Scope to filter active participants.
     */
    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'active');
    }

    /**
     * Scope to filter by level level.
     */
    public function scopeLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Legacy compatibility.
     */
    public function scopeGradeLevel($query, string $level)
    {
        return $this->scopeLevel($query, $level);
    }

    /**
     * Scope to filter participants with IEP.
     */
    public function scopeWithIep($query)
    {
        return $query->where('iep_status', true);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }
}
