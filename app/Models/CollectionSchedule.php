<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionSchedule extends Model
{
    protected $fillable = [
        'collection_id',
        'schedule_type',
        'interval_type',
        'interval_value',
        'custom_days',
        'custom_times',
        'event_trigger',
        'timezone',
        'start_date',
        'end_date',
        'is_active',
        'last_triggered_at',
        'next_scheduled_at',
    ];

    protected $casts = [
        'custom_days' => 'array',
        'custom_times' => 'array',
        'event_trigger' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'next_scheduled_at' => 'datetime',
    ];

    /**
     * Schedule type constants
     */
    public const TYPE_INTERVAL = 'interval';

    public const TYPE_CUSTOM = 'custom';

    public const TYPE_EVENT = 'event';

    /**
     * Interval type constants
     */
    public const INTERVAL_DAILY = 'daily';

    public const INTERVAL_WEEKLY = 'weekly';

    public const INTERVAL_MONTHLY = 'monthly';

    /**
     * Get the collection that owns this schedule.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get sessions created from this schedule.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(CollectionSession::class, 'schedule_id');
    }

    /**
     * Scope to filter active schedules.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter due schedules (ready to trigger).
     */
    public function scopeDue(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('next_scheduled_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Calculate the next run time for this schedule.
     */
    public function calculateNextRun(): ?Carbon
    {
        $now = Carbon::now($this->timezone);

        if ($this->end_date && $now->greaterThan($this->end_date)) {
            return null;
        }

        if ($this->schedule_type === self::TYPE_INTERVAL) {
            return $this->calculateNextIntervalRun($now);
        }

        if ($this->schedule_type === self::TYPE_CUSTOM) {
            return $this->calculateNextCustomRun($now);
        }

        // Event-triggered schedules don't have a next_scheduled_at
        return null;
    }

    /**
     * Calculate next run for interval schedules.
     */
    protected function calculateNextIntervalRun(Carbon $now): Carbon
    {
        $base = $this->last_triggered_at ?? $this->start_date ?? $now;
        $value = $this->interval_value ?? 1;

        return match ($this->interval_type) {
            self::INTERVAL_DAILY => $base->copy()->addDays($value),
            self::INTERVAL_WEEKLY => $base->copy()->addWeeks($value),
            self::INTERVAL_MONTHLY => $base->copy()->addMonths($value),
            default => $base->copy()->addDay(),
        };
    }

    /**
     * Calculate next run for custom day schedules.
     */
    protected function calculateNextCustomRun(Carbon $now): ?Carbon
    {
        $days = $this->custom_days ?? [];
        $times = $this->custom_times ?? ['09:00'];

        if (empty($days)) {
            return null;
        }

        // Find the next matching day and time
        $dayMap = [
            'sunday' => Carbon::SUNDAY,
            'monday' => Carbon::MONDAY,
            'tuesday' => Carbon::TUESDAY,
            'wednesday' => Carbon::WEDNESDAY,
            'thursday' => Carbon::THURSDAY,
            'friday' => Carbon::FRIDAY,
            'saturday' => Carbon::SATURDAY,
        ];

        $candidates = [];

        foreach ($days as $day) {
            $dayNum = $dayMap[strtolower($day)] ?? null;
            if ($dayNum === null) {
                continue;
            }

            foreach ($times as $time) {
                $parts = explode(':', $time);
                $hour = (int) ($parts[0] ?? 9);
                $minute = (int) ($parts[1] ?? 0);

                $candidate = $now->copy()->next($dayNum)->setTime($hour, $minute, 0);

                // If the day is today and time hasn't passed, use today
                if ($now->dayOfWeek === $dayNum) {
                    $todayCandidate = $now->copy()->setTime($hour, $minute, 0);
                    if ($todayCandidate->greaterThan($now)) {
                        $candidates[] = $todayCandidate;

                        continue;
                    }
                }

                $candidates[] = $candidate;
            }
        }

        if (empty($candidates)) {
            return null;
        }

        // Return the earliest candidate
        usort($candidates, fn ($a, $b) => $a->timestamp - $b->timestamp);

        return $candidates[0];
    }

    /**
     * Mark schedule as triggered.
     */
    public function markTriggered(): void
    {
        $this->last_triggered_at = now();
        $this->next_scheduled_at = $this->calculateNextRun();
        $this->save();
    }

    /**
     * Get schedule types for dropdown.
     */
    public static function getScheduleTypes(): array
    {
        return [
            self::TYPE_INTERVAL => 'Regular Interval',
            self::TYPE_CUSTOM => 'Custom Days',
            self::TYPE_EVENT => 'Event Triggered',
        ];
    }

    /**
     * Get interval types for dropdown.
     */
    public static function getIntervalTypes(): array
    {
        return [
            self::INTERVAL_DAILY => 'Daily',
            self::INTERVAL_WEEKLY => 'Weekly',
            self::INTERVAL_MONTHLY => 'Monthly',
        ];
    }

    /**
     * Get weekdays for multi-select.
     */
    public static function getWeekdays(): array
    {
        return [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];
    }
}
