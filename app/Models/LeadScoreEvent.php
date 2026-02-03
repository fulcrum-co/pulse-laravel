<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LeadScoreEvent extends Model
{
    use HasFactory;

    // Event types
    public const TYPE_LOGIN = 'login';
    public const TYPE_MODULE_COMPLETED = 'module_completed';
    public const TYPE_COURSE_COMPLETED = 'course_completed';
    public const TYPE_CERTIFICATION_EARNED = 'certification_earned';
    public const TYPE_COURSE_STARTED = 'course_started';
    public const TYPE_DECAY = 'decay';
    public const TYPE_MANUAL_ADJUSTMENT = 'manual_adjustment';

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

    /**
     * Get human-readable event type label.
     */
    public function getTypeLabel(): string
    {
        return match ($this->event_type) {
            self::TYPE_LOGIN => 'Login',
            self::TYPE_MODULE_COMPLETED => 'Module Completed',
            self::TYPE_COURSE_COMPLETED => 'Course Completed',
            self::TYPE_CERTIFICATION_EARNED => 'Certification Earned',
            self::TYPE_COURSE_STARTED => 'Course Started',
            self::TYPE_DECAY => 'Inactivity Decay',
            self::TYPE_MANUAL_ADJUSTMENT => 'Manual Adjustment',
            default => ucwords(str_replace('_', ' ', $this->event_type)),
        };
    }

    /**
     * Scope to filter by event type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope to get positive point events.
     */
    public function scopePositive($query)
    {
        return $query->where('points', '>', 0);
    }

    /**
     * Scope to get negative point events (decay).
     */
    public function scopeNegative($query)
    {
        return $query->where('points', '<', 0);
    }
}
