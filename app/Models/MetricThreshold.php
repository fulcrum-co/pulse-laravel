<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetricThreshold extends Model
{
    protected $fillable = [
        'org_id',
        'metric_category',
        'metric_key',
        'contact_type',
        'on_track_min',
        'at_risk_min',
        'off_track_min',
        'color_on_track',
        'color_at_risk',
        'color_off_track',
        'color_no_data',
        'label_on_track',
        'label_at_risk',
        'label_off_track',
        'invert_scale',
        'active',
    ];

    protected $casts = [
        'on_track_min' => 'decimal:4',
        'at_risk_min' => 'decimal:4',
        'off_track_min' => 'decimal:4',
        'invert_scale' => 'boolean',
        'active' => 'boolean',
    ];

    // Default colors
    public const DEFAULT_COLOR_ON_TRACK = '#22c55e';  // green-500

    public const DEFAULT_COLOR_AT_RISK = '#eab308';   // yellow-500

    public const DEFAULT_COLOR_OFF_TRACK = '#ef4444'; // red-500

    public const DEFAULT_COLOR_NO_DATA = '#9ca3af';   // gray-400

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Scope to filter active thresholds.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to filter by metric category.
     */
    public function scopeForCategory($query, string $category)
    {
        return $query->where('metric_category', $category);
    }

    /**
     * Scope to filter by metric key.
     */
    public function scopeForKey($query, string $key)
    {
        return $query->where('metric_key', $key);
    }

    /**
     * Calculate status based on value.
     */
    public function calculateStatus(float $value): string
    {
        if ($this->invert_scale) {
            // Lower is better (e.g., absences, discipline incidents)
            if ($this->on_track_min !== null && $value <= $this->on_track_min) {
                return ContactMetric::STATUS_ON_TRACK;
            }
            if ($this->at_risk_min !== null && $value <= $this->at_risk_min) {
                return ContactMetric::STATUS_AT_RISK;
            }

            return ContactMetric::STATUS_OFF_TRACK;
        }

        // Higher is better (e.g., GPA, attendance rate)
        if ($this->on_track_min !== null && $value >= $this->on_track_min) {
            return ContactMetric::STATUS_ON_TRACK;
        }
        if ($this->at_risk_min !== null && $value >= $this->at_risk_min) {
            return ContactMetric::STATUS_AT_RISK;
        }

        return ContactMetric::STATUS_OFF_TRACK;
    }

    /**
     * Get the color for a given status.
     */
    public function getColorForStatus(?string $status): string
    {
        return match ($status) {
            ContactMetric::STATUS_ON_TRACK => $this->color_on_track ?? self::DEFAULT_COLOR_ON_TRACK,
            ContactMetric::STATUS_AT_RISK => $this->color_at_risk ?? self::DEFAULT_COLOR_AT_RISK,
            ContactMetric::STATUS_OFF_TRACK => $this->color_off_track ?? self::DEFAULT_COLOR_OFF_TRACK,
            default => $this->color_no_data ?? self::DEFAULT_COLOR_NO_DATA,
        };
    }

    /**
     * Get the label for a given status.
     */
    public function getLabelForStatus(?string $status): string
    {
        return match ($status) {
            ContactMetric::STATUS_ON_TRACK => $this->label_on_track ?? 'On Track',
            ContactMetric::STATUS_AT_RISK => $this->label_at_risk ?? 'At Risk',
            ContactMetric::STATUS_OFF_TRACK => $this->label_off_track ?? 'Off Track',
            ContactMetric::STATUS_NOT_STARTED => 'Not Started',
            default => 'No Data',
        };
    }

    /**
     * Get default thresholds for common metrics.
     */
    public static function getDefaults(): array
    {
        return [
            'academics' => [
                'gpa' => ['on_track' => 3.0, 'at_risk' => 2.0, 'off_track' => 0, 'invert' => false],
                'homework_completion' => ['on_track' => 80, 'at_risk' => 60, 'off_track' => 0, 'invert' => false],
            ],
            'attendance' => [
                'attendance_rate' => ['on_track' => 95, 'at_risk' => 90, 'off_track' => 0, 'invert' => false],
                'absences' => ['on_track' => 3, 'at_risk' => 7, 'off_track' => 100, 'invert' => true],
            ],
            'behavior' => [
                'discipline_incidents' => ['on_track' => 0, 'at_risk' => 2, 'off_track' => 100, 'invert' => true],
            ],
            'wellness' => [
                'wellness_score' => ['on_track' => 70, 'at_risk' => 50, 'off_track' => 0, 'invert' => false],
                'emotional_wellbeing' => ['on_track' => 70, 'at_risk' => 50, 'off_track' => 0, 'invert' => false],
            ],
            'engagement' => [
                'engagement_score' => ['on_track' => 70, 'at_risk' => 50, 'off_track' => 0, 'invert' => false],
            ],
            'life_skills' => [
                'life_skills_score' => ['on_track' => 70, 'at_risk' => 50, 'off_track' => 0, 'invert' => false],
            ],
        ];
    }
}
