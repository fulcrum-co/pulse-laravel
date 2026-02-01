<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMetric extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'org_id',
        'contact_type',
        'contact_id',
        'metric_category',
        'metric_key',
        'metric_label',
        'numeric_value',
        'text_value',
        'json_value',
        'normalized_score',
        'status',
        'source_type',
        'source_id',
        'source_survey_attempt_id',
        'period_start',
        'period_end',
        'period_type',
        'school_year',
        'quarter',
        'recorded_by_user_id',
        'recorded_at',
        'is_pii',
        'requires_consent',
    ];

    protected $casts = [
        'json_value' => 'array',
        'numeric_value' => 'decimal:4',
        'normalized_score' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'recorded_at' => 'datetime',
        'is_pii' => 'boolean',
        'requires_consent' => 'boolean',
    ];

    // Metric categories for students
    public const CATEGORY_ACADEMICS = 'academics';

    public const CATEGORY_ATTENDANCE = 'attendance';

    public const CATEGORY_BEHAVIOR = 'behavior';

    public const CATEGORY_LIFE_SKILLS = 'life_skills';

    public const CATEGORY_WELLNESS = 'wellness';

    public const CATEGORY_ENGAGEMENT = 'engagement';

    // Metric categories for teachers
    public const CATEGORY_CLASSROOM = 'classroom';

    public const CATEGORY_PD = 'professional_development';

    // Source types
    public const SOURCE_SIS_API = 'sis_api';

    public const SOURCE_SURVEY = 'survey';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_CALCULATED = 'calculated';

    public const SOURCE_CONVERSATION = 'conversation';

    // Status values
    public const STATUS_ON_TRACK = 'on_track';

    public const STATUS_AT_RISK = 'at_risk';

    public const STATUS_OFF_TRACK = 'off_track';

    public const STATUS_NOT_STARTED = 'not_started';

    /**
     * Get the contact (Student or User).
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
     * Get the user who recorded this metric.
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }

    /**
     * Get the associated survey attempt.
     */
    public function surveyAttempt(): BelongsTo
    {
        return $this->belongsTo(SurveyAttempt::class, 'source_survey_attempt_id');
    }

    /**
     * Scope for filtering by time period.
     */
    public function scopeForPeriod($query, $start, $end)
    {
        return $query->whereBetween('period_start', [$start, $end]);
    }

    /**
     * Scope for filtering by school year.
     */
    public function scopeForSchoolYear($query, string $year)
    {
        return $query->where('school_year', $year);
    }

    /**
     * Scope for filtering by quarter.
     */
    public function scopeForQuarter($query, int $quarter)
    {
        return $query->where('quarter', $quarter);
    }

    /**
     * Scope for filtering by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('metric_category', $category);
    }

    /**
     * Scope for filtering by metric key.
     */
    public function scopeByKey($query, string $key)
    {
        return $query->where('metric_key', $key);
    }

    /**
     * Scope for filtering by contact.
     */
    public function scopeForContact($query, string $type, int $id)
    {
        return $query->where('contact_type', $type)->where('contact_id', $id);
    }

    /**
     * Scope for filtering by organization.
     */
    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Get human-readable label for metric key.
     */
    public function getLabelAttribute(): string
    {
        if ($this->metric_label) {
            return $this->metric_label;
        }

        return match ($this->metric_key) {
            'gpa' => 'GPA',
            'wellness_score' => 'Health & Wellness',
            'emotional_wellbeing' => 'Emotional Well-Being',
            'engagement_score' => 'Engagement',
            'plan_progress' => 'Plan Progress',
            'attendance_rate' => 'Attendance Rate',
            default => ucwords(str_replace('_', ' ', $this->metric_key)),
        };
    }
}
