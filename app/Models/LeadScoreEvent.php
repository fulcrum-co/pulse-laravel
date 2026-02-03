<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class LeadScoreEvent extends Model
{
    public const EVENT_MODULE_COMPLETED = 'module_completed';
    public const EVENT_CERTIFICATION_EARNED = 'certification_earned';
    public const EVENT_LOGIN = 'login';
    public const EVENT_DECAY = 'decay';
    public const EVENT_COURSE_STARTED = 'course_started';
    public const EVENT_COURSE_COMPLETED = 'course_completed';
    public const EVENT_MANUAL_ADJUSTMENT = 'manual_adjustment';

    protected $fillable = [
        'lead_score_id',
        'event_type',
        'points',
        'description',
        'scoreable_type',
        'scoreable_id',
    ];

    protected $casts = [
        'points' => 'integer',
    ];

    public function leadScore(): BelongsTo
    {
        return $this->belongsTo(LeadScore::class);
    }

    public function scoreable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('event_type', $type);
    }

    public function scopePositive(Builder $query): Builder
    {
        return $query->where('points', '>', 0);
    }

    public function scopeNegative(Builder $query): Builder
    {
        return $query->where('points', '<', 0);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper methods
    public static function getEventTypes(): array
    {
        return [
            self::EVENT_MODULE_COMPLETED => 'Module Completed',
            self::EVENT_CERTIFICATION_EARNED => 'Certification Earned',
            self::EVENT_LOGIN => 'Login',
            self::EVENT_DECAY => 'Score Decay',
            self::EVENT_COURSE_STARTED => 'Course Started',
            self::EVENT_COURSE_COMPLETED => 'Course Completed',
            self::EVENT_MANUAL_ADJUSTMENT => 'Manual Adjustment',
        ];
    }

    public function isPositive(): bool
    {
        return $this->points > 0;
    }

    public function isNegative(): bool
    {
        return $this->points < 0;
    }
}
