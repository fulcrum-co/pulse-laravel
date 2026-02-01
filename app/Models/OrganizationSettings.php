<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationSettings extends Model
{
    protected $table = 'organization_settings';

    protected $fillable = [
        'org_id',
        'status_labels',
        'risk_labels',
        'settings',
    ];

    protected $casts = [
        'status_labels' => 'array',
        'risk_labels' => 'array',
        'settings' => 'array',
    ];

    /**
     * Default status labels.
     */
    public const DEFAULT_STATUS_LABELS = [
        'on_track' => 'On Track',
        'at_risk' => 'At Risk',
        'off_track' => 'Off Track',
        'not_started' => 'Not Started',
    ];

    /**
     * Default risk labels.
     */
    public const DEFAULT_RISK_LABELS = [
        'good' => 'Good',
        'low' => 'Low Risk',
        'high' => 'High Risk',
    ];

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get a status label (custom or default).
     */
    public function getStatusLabel(string $key): string
    {
        $labels = $this->status_labels ?? [];

        return $labels[$key] ?? self::DEFAULT_STATUS_LABELS[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Get a risk label (custom or default).
     */
    public function getRiskLabel(string $key): string
    {
        $labels = $this->risk_labels ?? [];

        return $labels[$key] ?? self::DEFAULT_RISK_LABELS[$key] ?? ucfirst($key);
    }

    /**
     * Get all status labels (merged with defaults).
     */
    public function getAllStatusLabels(): array
    {
        return array_merge(self::DEFAULT_STATUS_LABELS, $this->status_labels ?? []);
    }

    /**
     * Get all risk labels (merged with defaults).
     */
    public function getAllRiskLabels(): array
    {
        return array_merge(self::DEFAULT_RISK_LABELS, $this->risk_labels ?? []);
    }

    /**
     * Get a setting value.
     */
    public function getSetting(string $key, $default = null)
    {
        $settings = $this->settings ?? [];

        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value.
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }

    /**
     * Get or create settings for an organization.
     */
    public static function forOrganization(int $orgId): self
    {
        return self::firstOrCreate(['org_id' => $orgId]);
    }
}
