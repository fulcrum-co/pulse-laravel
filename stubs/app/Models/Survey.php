<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Survey extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'surveys';

    protected $fillable = [
        'org_id',
        'created_by_org_type',
        'cascaded_from_org_id',
        'accessible_to_org_ids',
        'name',
        'description',
        'survey_type',
        'questions',
        'segments',
        'use_conversational_mode',
        'llm_system_prompt',
        'llm_follow_up_enabled',
        'llm_extraction_schema',
        'delivery_methods',
        'default_delivery_method',
        'frequency',
        'trigger_day_of_week',
        'trigger_time',
        'assign_to_roles',
        'assign_to_specific_users',
        'status',
        'active',
        'created_by',
    ];

    protected $casts = [
        'accessible_to_org_ids' => 'array',
        'questions' => 'array',
        'segments' => 'array',
        'llm_extraction_schema' => 'array',
        'delivery_methods' => 'array',
        'assign_to_roles' => 'array',
        'assign_to_specific_users' => 'array',
        'use_conversational_mode' => 'boolean',
        'llm_follow_up_enabled' => 'boolean',
        'active' => 'boolean',
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
        return $this->hasMany(SurveyAttempt::class, 'survey_id');
    }

    /**
     * Get completed attempts for this survey.
     */
    public function completedAttempts(): HasMany
    {
        return $this->attempts()->where('status', 'completed');
    }

    /**
     * Check if this survey is accessible to an organization.
     */
    public function isAccessibleTo(string $orgId): bool
    {
        if ($this->org_id === $orgId) {
            return true;
        }

        return in_array($orgId, $this->accessible_to_org_ids ?? []);
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
        return $query->where('status', 'active')->where('active', true);
    }

    /**
     * Scope to filter by survey type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('survey_type', $type);
    }

    /**
     * Scope to filter conversational surveys.
     */
    public function scopeConversational($query)
    {
        return $query->where('use_conversational_mode', true);
    }

    /**
     * Scope to filter surveys accessible to an org.
     */
    public function scopeAccessibleTo($query, string $orgId)
    {
        return $query->where(function ($q) use ($orgId) {
            $q->where('org_id', $orgId)
                ->orWhere('accessible_to_org_ids', $orgId);
        });
    }
}
