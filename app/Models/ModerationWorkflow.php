<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModerationWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_id',
        'workflow_id',
        'content_type',
        'trigger_conditions',
        'is_default',
        'priority',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'is_default' => 'boolean',
        'priority' => 'integer',
    ];

    public const CONTENT_TYPE_MINI_COURSE = 'mini_course';

    public const CONTENT_TYPE_CONTENT_BLOCK = 'content_block';

    public const CONTENT_TYPE_ALL = 'all';

    // Relationships

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function queueItems(): HasMany
    {
        return $this->hasMany(ModerationQueueItem::class, 'workflow_id');
    }

    // Scopes

    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    public function scopeForContentType($query, string $contentType)
    {
        return $query->where(function ($q) use ($contentType) {
            $q->where('content_type', $contentType)
                ->orWhere('content_type', self::CONTENT_TYPE_ALL);
        });
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeActive($query)
    {
        return $query->whereHas('workflow', function ($q) {
            $q->where('status', Workflow::STATUS_ACTIVE);
        });
    }

    // Methods

    public function matchesTriggerConditions(ContentModerationResult $result): bool
    {
        if (empty($this->trigger_conditions)) {
            return true;
        }

        foreach ($this->trigger_conditions as $condition) {
            if (! $this->evaluateCondition($condition, $result)) {
                return false;
            }
        }

        return true;
    }

    protected function evaluateCondition(array $condition, ContentModerationResult $result): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? 'equals';
        $value = $condition['value'] ?? null;

        if (! $field) {
            return true;
        }

        $actualValue = match ($field) {
            'overall_score' => $result->overall_score,
            'age_appropriateness_score' => $result->age_appropriateness_score,
            'clinical_safety_score' => $result->clinical_safety_score,
            'cultural_sensitivity_score' => $result->cultural_sensitivity_score,
            'accuracy_score' => $result->accuracy_score,
            'status' => $result->status,
            'has_flags' => ! empty($result->flags),
            default => null,
        };

        return match ($operator) {
            'equals', '==' => $actualValue == $value,
            'not_equals', '!=' => $actualValue != $value,
            'greater_than', '>' => $actualValue > $value,
            'greater_than_or_equal', '>=' => $actualValue >= $value,
            'less_than', '<' => $actualValue < $value,
            'less_than_or_equal', '<=' => $actualValue <= $value,
            'between' => $actualValue >= ($value['min'] ?? 0) && $actualValue <= ($value['max'] ?? 1),
            'contains' => is_array($actualValue) && in_array($value, $actualValue),
            default => true,
        };
    }
}
