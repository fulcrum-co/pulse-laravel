<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationSlaConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_id',
        'priority',
        'target_hours',
        'warning_hours',
        'is_active',
    ];

    protected $casts = [
        'target_hours' => 'integer',
        'warning_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    // Default SLA configurations
    public static array $defaults = [
        ModerationQueueItem::PRIORITY_URGENT => [
            'target_hours' => 4,
            'warning_hours' => 2,
        ],
        ModerationQueueItem::PRIORITY_HIGH => [
            'target_hours' => 24,
            'warning_hours' => 12,
        ],
        ModerationQueueItem::PRIORITY_NORMAL => [
            'target_hours' => 48,
            'warning_hours' => 24,
        ],
        ModerationQueueItem::PRIORITY_LOW => [
            'target_hours' => 72,
            'warning_hours' => 48,
        ],
    ];

    // Relationships

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    // Scopes

    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    // Static methods

    public static function getConfigForPriority(int $orgId, string $priority): ?self
    {
        return static::forOrganization($orgId)
            ->forPriority($priority)
            ->active()
            ->first();
    }

    public static function getTargetHours(int $orgId, string $priority): int
    {
        $config = static::getConfigForPriority($orgId, $priority);

        if ($config) {
            return $config->target_hours;
        }

        return self::$defaults[$priority]['target_hours'] ?? 48;
    }

    public static function getWarningHours(int $orgId, string $priority): int
    {
        $config = static::getConfigForPriority($orgId, $priority);

        if ($config) {
            return $config->warning_hours;
        }

        return self::$defaults[$priority]['warning_hours'] ?? 24;
    }

    public static function createDefaultsForOrganization(int $orgId): void
    {
        foreach (self::$defaults as $priority => $config) {
            static::firstOrCreate(
                ['org_id' => $orgId, 'priority' => $priority],
                [
                    'target_hours' => $config['target_hours'],
                    'warning_hours' => $config['warning_hours'],
                    'is_active' => true,
                ]
            );
        }
    }

    // Methods

    public function calculateDueDate(): \Carbon\Carbon
    {
        return now()->addHours($this->target_hours);
    }

    public function calculateWarningDate(): \Carbon\Carbon
    {
        return now()->addHours($this->target_hours - $this->warning_hours);
    }
}
