<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationTeamSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_id',
        'user_id',
        'content_specializations',
        'max_concurrent_items',
        'auto_assign_enabled',
        'schedule',
        'current_load',
    ];

    protected $casts = [
        'content_specializations' => 'array',
        'max_concurrent_items' => 'integer',
        'auto_assign_enabled' => 'boolean',
        'schedule' => 'array',
        'current_load' => 'integer',
    ];

    // Specialization constants
    public const SPEC_WELLNESS = 'wellness';
    public const SPEC_ACADEMIC = 'academic';
    public const SPEC_SOCIAL_EMOTIONAL = 'social_emotional';
    public const SPEC_CAREER = 'career';
    public const SPEC_CRISIS = 'crisis';

    public static array $specializations = [
        self::SPEC_WELLNESS => 'Wellness Content',
        self::SPEC_ACADEMIC => 'Academic Content',
        self::SPEC_SOCIAL_EMOTIONAL => 'Social-Emotional Learning',
        self::SPEC_CAREER => 'Career & College',
        self::SPEC_CRISIS => 'Crisis & Safety',
    ];

    // Relationships

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    public function scopeAutoAssignEnabled($query)
    {
        return $query->where('auto_assign_enabled', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('auto_assign_enabled', true)
                     ->whereRaw('current_load < max_concurrent_items');
    }

    public function scopeWithSpecialization($query, string $specialization)
    {
        return $query->whereJsonContains('content_specializations', $specialization);
    }

    public function scopeOrderByLoad($query, string $direction = 'asc')
    {
        return $query->orderBy('current_load', $direction);
    }

    // Accessors

    public function getIsAvailableAttribute(): bool
    {
        if (!$this->auto_assign_enabled) {
            return false;
        }

        if ($this->current_load >= $this->max_concurrent_items) {
            return false;
        }

        // Check schedule if defined
        if (!empty($this->schedule)) {
            return $this->isWithinSchedule();
        }

        return true;
    }

    public function getRemainingCapacityAttribute(): int
    {
        return max(0, $this->max_concurrent_items - $this->current_load);
    }

    public function getLoadPercentageAttribute(): float
    {
        if ($this->max_concurrent_items === 0) {
            return 100;
        }

        return round(($this->current_load / $this->max_concurrent_items) * 100, 1);
    }

    // Methods

    public function hasSpecialization(string $specialization): bool
    {
        return in_array($specialization, $this->content_specializations ?? []);
    }

    public function addSpecialization(string $specialization): void
    {
        $specs = $this->content_specializations ?? [];

        if (!in_array($specialization, $specs)) {
            $specs[] = $specialization;
            $this->update(['content_specializations' => $specs]);
        }
    }

    public function removeSpecialization(string $specialization): void
    {
        $specs = $this->content_specializations ?? [];
        $specs = array_filter($specs, fn($s) => $s !== $specialization);
        $this->update(['content_specializations' => array_values($specs)]);
    }

    public function incrementLoad(): void
    {
        $this->increment('current_load');
    }

    public function decrementLoad(): void
    {
        if ($this->current_load > 0) {
            $this->decrement('current_load');
        }
    }

    public function recalculateLoad(): void
    {
        $activeItems = ModerationQueueItem::where('assigned_to', $this->user_id)
            ->active()
            ->count();

        $this->update(['current_load' => $activeItems]);
    }

    protected function isWithinSchedule(): bool
    {
        if (empty($this->schedule)) {
            return true;
        }

        $now = now();
        $dayOfWeek = strtolower($now->format('l')); // monday, tuesday, etc.
        $currentTime = $now->format('H:i');

        // Check if current day has schedule
        if (!isset($this->schedule[$dayOfWeek])) {
            return false;
        }

        $daySchedule = $this->schedule[$dayOfWeek];

        // Check if within working hours
        $startTime = $daySchedule['start'] ?? '09:00';
        $endTime = $daySchedule['end'] ?? '17:00';

        return $currentTime >= $startTime && $currentTime <= $endTime;
    }

    public static function getOrCreateForUser(int $orgId, int $userId): self
    {
        return static::firstOrCreate(
            ['org_id' => $orgId, 'user_id' => $userId],
            [
                'content_specializations' => [],
                'max_concurrent_items' => 10,
                'auto_assign_enabled' => true,
                'schedule' => null,
                'current_load' => 0,
            ]
        );
    }
}
