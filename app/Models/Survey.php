<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'org_id',
        'title',
        'description',
        'survey_type',
        'questions',
        'status',
        'is_anonymous',
        'estimated_duration_minutes',
        'start_date',
        'end_date',
        'target_grades',
        'target_classrooms',
        'created_by',
    ];

    protected $casts = [
        'questions' => 'array',
        'target_grades' => 'array',
        'target_classrooms' => 'array',
        'is_anonymous' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get the organization that owns this survey.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who created this survey.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all attempts for this survey.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(SurveyAttempt::class);
    }

    /**
     * Get completed attempts for this survey.
     */
    public function completedAttempts(): HasMany
    {
        return $this->attempts()->where('status', 'completed');
    }

    /**
     * Get question count.
     */
    public function getQuestionCountAttribute(): int
    {
        return count($this->questions ?? []);
    }

    /**
     * Scope to filter active surveys.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by survey type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('survey_type', $type);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }
}
