<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyAttempt extends Model
{
    protected $fillable = [
        'survey_id',
        'student_id',
        'user_id',
        'status',
        'responses',
        'results',
        'overall_score',
        'risk_level',
        'ai_analysis',
        'started_at',
        'completed_at',
        'duration_seconds',
    ];

    protected $casts = [
        'responses' => 'array',
        'results' => 'array',
        'ai_analysis' => 'array',
        'overall_score' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the survey.
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the student.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user who took the survey.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this attempt is high risk.
     */
    public function isHighRisk(): bool
    {
        return $this->risk_level === 'high';
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
        return $query->where('risk_level', $level);
    }

    /**
     * Scope to filter high risk attempts.
     */
    public function scopeHighRisk($query)
    {
        return $query->where('risk_level', 'high');
    }
}
