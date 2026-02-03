<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseTemplate extends Model
{
    use SoftDeletes;

    // Statuses
    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'org_id',
        'name',
        'slug',
        'description',
        'course_type',
        'template_data',
        'target_risk_factors',
        'target_levels',
        'estimated_duration_minutes',
        'is_system',
        'status',
        'usage_count',
        'created_by',
    ];

    protected $casts = [
        'template_data' => 'array',
        'target_risk_factors' => 'array',
        'target_levels' => 'array',
        'is_system' => 'boolean',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'is_system' => false,
        'usage_count' => 0,
    ];

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }

    /**
     * Organization relationship.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Creator relationship.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Courses generated from this template.
     */
    public function generatedCourses(): HasMany
    {
        return $this->hasMany(MiniCourse::class, 'template_id');
    }

    /**
     * Generation requests using this template.
     */
    public function generationRequests(): HasMany
    {
        return $this->hasMany(CourseGenerationRequest::class, 'template_id');
    }

    /**
     * Scope to active templates.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to system templates.
     */
    public function scopeSystem(Builder $query): Builder
    {
        return $query->where('is_system', true);
    }

    /**
     * Scope to organization templates.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope for available templates (org-specific or system).
     */
    public function scopeAvailableFor(Builder $query, int $orgId): Builder
    {
        return $query->where(function ($q) use ($orgId) {
            $q->where('org_id', $orgId)
                ->orWhere('is_system', true);
        });
    }

    /**
     * Scope by course type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('course_type', $type);
    }

    /**
     * Scope by risk factors.
     */
    public function scopeForRiskFactors(Builder $query, array $factors): Builder
    {
        return $query->where(function ($q) use ($factors) {
            foreach ($factors as $factor) {
                $q->orWhereJsonContains('target_risk_factors', $factor);
            }
        });
    }

    /**
     * Scope by level levels.
     */
    public function scopeForLevels(Builder $query, array $levels): Builder
    {
        return $query->where(function ($q) use ($levels) {
            foreach ($levels as $level) {
                $q->orWhereJsonContains('target_levels', $level);
            }
        });
    }

    /**
     * Legacy compatibility.
     */
    public function scopeForGradeLevels(Builder $query, array $levels): Builder
    {
        return $this->scopeForLevels($query, $levels);
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Get step count from template data.
     */
    public function getStepCountAttribute(): int
    {
        return count($this->template_data['steps'] ?? []);
    }

    /**
     * Get objectives template.
     */
    public function getObjectivesTemplateAttribute(): array
    {
        return $this->template_data['objectives_template'] ?? [];
    }

    /**
     * Get steps template.
     */
    public function getStepsTemplateAttribute(): array
    {
        return $this->template_data['steps'] ?? [];
    }

    /**
     * Get template variables/placeholders.
     */
    public function getVariablesAttribute(): array
    {
        return $this->template_data['variables'] ?? [];
    }

    /**
     * Fill template with actual values.
     * Returns the template_data with placeholders replaced.
     */
    public function fillTemplate(array $values): array
    {
        $data = $this->template_data;

        // Recursively replace placeholders
        return $this->replacePlaceholders($data, $values);
    }

    /**
     * Recursively replace placeholders in template data.
     */
    protected function replacePlaceholders($data, array $values)
    {
        if (is_string($data)) {
            foreach ($values as $key => $value) {
                if (is_string($value)) {
                    $data = str_replace('{'.$key.'}', $value, $data);
                }
            }

            return $data;
        }

        if (is_array($data)) {
            foreach ($data as $key => $item) {
                $data[$key] = $this->replacePlaceholders($item, $values);
            }
        }

        return $data;
    }

    /**
     * Validate that all required variables are provided.
     */
    public function validateVariables(array $values): array
    {
        $errors = [];
        $variables = $this->variables;

        foreach ($variables as $name => $config) {
            if (($config['required'] ?? false) && ! isset($values[$name])) {
                $errors[] = "Missing required variable: {$name}";
            }
        }

        return $errors;
    }

    /**
     * Extract all placeholders from template.
     */
    public function extractPlaceholders(): array
    {
        $json = json_encode($this->template_data);
        preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $json, $matches);

        return array_unique($matches[1] ?? []);
    }
}
