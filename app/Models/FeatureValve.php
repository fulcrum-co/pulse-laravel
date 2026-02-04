<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureValve extends Model
{
    protected $fillable = [
        'org_id',
        'feature_key',
        'is_active',
        'daily_limit',
        'daily_usage',
        'reversion_message',
        'changed_by',
        'changed_at',
        'change_reason',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'daily_limit' => 'integer',
        'daily_usage' => 'integer',
        'changed_at' => 'datetime',
    ];

    /**
     * Feature keys.
     */
    public const FEATURE_AI_ANALYSIS = 'ai_analysis';

    public const FEATURE_VOICE_TRANSCRIPTION = 'voice_transcription';

    public const FEATURE_SMS_OUTREACH = 'sms_outreach';

    public const FEATURE_WHATSAPP = 'whatsapp';

    public const FEATURE_AI_COURSES = 'ai_courses';

    /**
     * Feature configuration with display names and categories.
     */
    public const FEATURES = [
        self::FEATURE_AI_ANALYSIS => [
            'name' => 'AI Analysis',
            'description' => 'AI-powered narrative analysis and insights',
            'category' => 'intelligence',
        ],
        self::FEATURE_VOICE_TRANSCRIPTION => [
            'name' => 'Voice Transcription',
            'description' => 'Audio/video transcription services',
            'category' => 'voice',
        ],
        self::FEATURE_SMS_OUTREACH => [
            'name' => 'SMS Outreach',
            'description' => 'Text message communications',
            'category' => 'outreach',
        ],
        self::FEATURE_WHATSAPP => [
            'name' => 'WhatsApp',
            'description' => 'WhatsApp messaging integration',
            'category' => 'outreach',
        ],
        self::FEATURE_AI_COURSES => [
            'name' => 'AI Course Generation',
            'description' => 'AI-generated training courses',
            'category' => 'intelligence',
        ],
    ];

    /**
     * Get the organization this valve belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who last changed this valve.
     */
    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Check if this feature is enabled (active and under daily limit).
     */
    public function isEnabled(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        // If there's a daily limit, check if we're under it
        if ($this->daily_limit !== null && $this->daily_usage >= $this->daily_limit) {
            return false;
        }

        return true;
    }

    /**
     * Increment daily usage counter.
     */
    public function incrementUsage(): void
    {
        $this->daily_usage++;
        $this->save();
    }

    /**
     * Reset daily usage counter.
     */
    public function resetDailyUsage(): void
    {
        $this->daily_usage = 0;
        $this->save();
    }

    /**
     * Get remaining daily quota.
     */
    public function getRemainingQuota(): ?int
    {
        if ($this->daily_limit === null) {
            return null; // Unlimited
        }

        return max(0, $this->daily_limit - $this->daily_usage);
    }

    /**
     * Get feature display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return self::FEATURES[$this->feature_key]['name'] ?? $this->feature_key;
    }

    /**
     * Get feature description.
     */
    public function getDescriptionAttribute(): string
    {
        return self::FEATURES[$this->feature_key]['description'] ?? '';
    }

    /**
     * Get feature category.
     */
    public function getCategoryAttribute(): string
    {
        return self::FEATURES[$this->feature_key]['category'] ?? 'general';
    }

    /**
     * Get status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        if (! $this->is_active) {
            return 'red';
        }

        if ($this->daily_limit !== null && $this->daily_usage >= $this->daily_limit) {
            return 'yellow'; // Quota exhausted
        }

        return 'green';
    }

    /**
     * Get status text.
     */
    public function getStatusTextAttribute(): string
    {
        if (! $this->is_active) {
            return 'Disabled';
        }

        if ($this->daily_limit !== null && $this->daily_usage >= $this->daily_limit) {
            return 'Quota Exhausted';
        }

        return 'Active';
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrg($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to filter active valves only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter disabled valves only.
     */
    public function scopeDisabled($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get or create a valve for an organization and feature.
     */
    public static function forOrgFeature(int $orgId, string $featureKey): self
    {
        return static::firstOrCreate(
            ['org_id' => $orgId, 'feature_key' => $featureKey],
            ['is_active' => true]
        );
    }

    /**
     * Get all valves for an organization, creating defaults if needed.
     */
    public static function getAllForOrg(int $orgId): array
    {
        $valves = [];

        foreach (array_keys(self::FEATURES) as $featureKey) {
            $valves[$featureKey] = self::forOrgFeature($orgId, $featureKey);
        }

        return $valves;
    }
}
