<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DistributionSchedule extends Model
{
    use HasFactory;

    const TYPE_INTERVAL = 'interval';
    const TYPE_CUSTOM = 'custom';

    const INTERVAL_DAILY = 'daily';
    const INTERVAL_WEEKLY = 'weekly';
    const INTERVAL_MONTHLY = 'monthly';

    protected $fillable = [
        'distribution_id',
        'schedule_type',
        'interval_type',
        'interval_value',
        'custom_days',
        'send_time',
        'timezone',
        'start_date',
        'end_date',
        'is_active',
        'last_sent_at',
        'next_scheduled_at',
    ];

    protected $casts = [
        'custom_days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
        'next_scheduled_at' => 'datetime',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('next_scheduled_at')
                    ->orWhere('next_scheduled_at', '<=', now());
            });
    }

    public function isDue(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->next_scheduled_at) {
            return true;
        }

        return $this->next_scheduled_at->isPast();
    }

    public function calculateNextRunTime(): ?Carbon
    {
        $now = Carbon::now($this->timezone);

        if ($this->end_date && $now->greaterThan($this->end_date)) {
            return null;
        }

        $baseTime = $this->send_time
            ? Carbon::parse($this->send_time, $this->timezone)
            : $now->copy();

        if ($this->schedule_type === self::TYPE_INTERVAL) {
            return $this->calculateIntervalNextRun($now, $baseTime);
        }

        if ($this->schedule_type === self::TYPE_CUSTOM) {
            return $this->calculateCustomNextRun($now, $baseTime);
        }

        return null;
    }

    protected function calculateIntervalNextRun(Carbon $now, Carbon $baseTime): Carbon
    {
        $next = $now->copy();

        switch ($this->interval_type) {
            case self::INTERVAL_DAILY:
                $next->addDays($this->interval_value);
                break;
            case self::INTERVAL_WEEKLY:
                $next->addWeeks($this->interval_value);
                break;
            case self::INTERVAL_MONTHLY:
                $next->addMonths($this->interval_value);
                break;
        }

        $next->setTime($baseTime->hour, $baseTime->minute, 0);

        return $next;
    }

    protected function calculateCustomNextRun(Carbon $now, Carbon $baseTime): ?Carbon
    {
        if (empty($this->custom_days)) {
            return null;
        }

        $dayMap = [
            'sunday' => 0,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
        ];

        $targetDays = array_map(fn($day) => $dayMap[strtolower($day)] ?? null, $this->custom_days);
        $targetDays = array_filter($targetDays, fn($d) => $d !== null);

        if (empty($targetDays)) {
            return null;
        }

        sort($targetDays);
        $currentDayOfWeek = $now->dayOfWeek;

        // Find next matching day
        foreach ($targetDays as $day) {
            if ($day > $currentDayOfWeek) {
                $daysToAdd = $day - $currentDayOfWeek;
                $next = $now->copy()->addDays($daysToAdd);
                $next->setTime($baseTime->hour, $baseTime->minute, 0);
                return $next;
            }
        }

        // Wrap to next week
        $daysToAdd = 7 - $currentDayOfWeek + $targetDays[0];
        $next = $now->copy()->addDays($daysToAdd);
        $next->setTime($baseTime->hour, $baseTime->minute, 0);

        return $next;
    }

    public function updateNextScheduledAt(): void
    {
        $this->update([
            'last_sent_at' => now(),
            'next_scheduled_at' => $this->calculateNextRunTime(),
        ]);
    }

    public static function getScheduleTypes(): array
    {
        return [
            self::TYPE_INTERVAL => 'Interval (Daily/Weekly/Monthly)',
            self::TYPE_CUSTOM => 'Custom Days',
        ];
    }

    public static function getIntervalTypes(): array
    {
        return [
            self::INTERVAL_DAILY => 'Daily',
            self::INTERVAL_WEEKLY => 'Weekly',
            self::INTERVAL_MONTHLY => 'Monthly',
        ];
    }

    public static function getDayOptions(): array
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
