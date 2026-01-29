<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyAttempt extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'survey_attempts';

    protected $fillable = [
        'survey_id',
        'surveyor_id',
        'surveyee_id',
        'org_id',
        'classroom_id',
        'delivery_method',
        'conversation_transcript',
        'voice_recording_url',
        'conversation_duration_seconds',
        'llm_extracted_data',
        'llm_confidence_score',
        'llm_flags',
        'llm_summary',
        'attempt_questions',
        'survey_attempt_result',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'llm_extracted_data' => 'array',
        'llm_flags' => 'array',
        'attempt_questions' => 'array',
        'survey_attempt_result' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'llm_confidence_score' => 'float',
        'conversation_duration_seconds' => 'integer',
    ];

    /**
     * Get the survey.
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class, 'survey_id');
    }

    /**
     * Get the surveyor (person who filled out the survey).
     */
    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'surveyor_id');
    }

    /**
     * Get the surveyee (student being surveyed about).
     */
    public function surveyee(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'surveyee_id', 'user_id');
    }

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the classroom.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'classroom_id');
    }

    /**
     * Get the overall risk level.
     */
    public function getRiskLevelAttribute(): string
    {
        return $this->survey_attempt_result['overall_risk_level'] ?? 'unknown';
    }

    /**
     * Check if this attempt flagged high risk.
     */
    public function isHighRisk(): bool
    {
        return $this->risk_level === 'high';
    }

    /**
     * Check if LLM extraction found specific flags.
     */
    public function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->llm_flags ?? []);
    }

    /**
     * Scope to filter completed attempts.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter by risk level.
     */
    public function scopeRiskLevel($query, string $level)
    {
        return $query->where('survey_attempt_result.overall_risk_level', $level);
    }

    /**
     * Scope to filter high risk attempts.
     */
    public function scopeHighRisk($query)
    {
        return $query->riskLevel('high');
    }

    /**
     * Scope to filter by delivery method.
     */
    public function scopeViaMethod($query, string $method)
    {
        return $query->where('delivery_method', $method);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeCompletedBetween($query, $start, $end)
    {
        return $query->whereBetween('completed_at', [$start, $end]);
    }
}
