<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class LeadScore extends Model
{
    // Score values from PRD
    public const POINTS_MODULE_COMPLETED = 20;
    public const POINTS_CERTIFICATION_EARNED = 50;
    public const DECAY_DAYS_THRESHOLD = 30;
    public const DECAY_PERCENTAGE = 10; // 10% decay per period

    protected $fillable = [
        'org_id',
        'user_id',
        'total_score',
        'modules_completed',
        'certifications_earned',
        'courses_started',
        'courses_completed',
        'last_activity_at',
        'last_decay_at',
        'score_history',
        'crm_sync_data',
        'crm_synced_at',
    ];

    protected $casts = [
        'total_score' => 'integer',
        'modules_completed' => 'integer',
        'certifications_earned' => 'integer',
        'courses_started' => 'integer',
        'courses_completed' => 'integer',
        'last_activity_at' => 'datetime',
        'last_decay_at' => 'datetime',
        'score_history' => 'array',
        'crm_sync_data' => 'array',
        'crm_synced_at' => 'datetime',
    ];

    protected $attributes = [
        'total_score' => 0,
        'modules_completed' => 0,
        'certifications_earned' => 0,
        'courses_started' => 0,
        'courses_completed' => 0,
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(LeadScoreEvent::class)->orderByDesc('created_at');
    }

    // Scopes
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    public function scopeHighValue(Builder $query, int $threshold = 100): Builder
    {
        return $query->where('total_score', '>=', $threshold);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('last_activity_at', '<', now()->subDays(self::DECAY_DAYS_THRESHOLD));
    }

    public function scopeNeedsDecay(Builder $query): Builder
    {
        return $query->where('total_score', '>', 0)
                     ->where(function ($q) {
                         $q->whereNull('last_decay_at')
                           ->orWhere('last_decay_at', '<', now()->subDays(self::DECAY_DAYS_THRESHOLD));
                     })
                     ->where('last_activity_at', '<', now()->subDays(self::DECAY_DAYS_THRESHOLD));
    }

    public function scopeNeedsCrmSync(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('crm_synced_at')
              ->orWhereColumn('updated_at', '>', 'crm_synced_at');
        });
    }

    // Score modification methods
    public function addPoints(int $points, string $eventType, string $description = null, Model $scoreable = null): void
    {
        $this->total_score += $points;
        $this->last_activity_at = now();

        // Update history
        $history = $this->score_history ?? [];
        $history[] = [
            'date' => now()->toDateTimeString(),
            'event' => $eventType,
            'points' => $points,
            'total' => $this->total_score,
        ];
        $this->score_history = array_slice($history, -100); // Keep last 100 entries

        $this->save();

        // Record event
        $this->events()->create([
            'event_type' => $eventType,
            'points' => $points,
            'description' => $description,
            'scoreable_type' => $scoreable ? get_class($scoreable) : null,
            'scoreable_id' => $scoreable?->id,
        ]);
    }

    public function moduleCompleted(Model $module = null): void
    {
        $this->modules_completed++;
        $this->addPoints(
            self::POINTS_MODULE_COMPLETED,
            'module_completed',
            'Module completed',
            $module
        );
    }

    public function certificationEarned(Certificate $certificate = null): void
    {
        $this->certifications_earned++;
        $this->addPoints(
            self::POINTS_CERTIFICATION_EARNED,
            'certification_earned',
            'Certification earned',
            $certificate
        );
    }

    public function courseStarted(): void
    {
        $this->courses_started++;
        $this->last_activity_at = now();
        $this->save();
    }

    public function courseCompleted(): void
    {
        $this->courses_completed++;
        $this->last_activity_at = now();
        $this->save();
    }

    public function applyDecay(): void
    {
        if ($this->total_score <= 0) {
            return;
        }

        $decayAmount = (int) ceil($this->total_score * (self::DECAY_PERCENTAGE / 100));
        $this->total_score = max(0, $this->total_score - $decayAmount);
        $this->last_decay_at = now();
        $this->save();

        $this->events()->create([
            'event_type' => 'decay',
            'points' => -$decayAmount,
            'description' => 'Score decay due to inactivity',
        ]);
    }

    public function recordActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Get or create a lead score for a user.
     */
    public static function getOrCreate(int $orgId, int $userId): self
    {
        return self::firstOrCreate(
            ['org_id' => $orgId, 'user_id' => $userId],
            ['total_score' => 0]
        );
    }

    /**
     * Get score tier label.
     */
    public function getTierAttribute(): string
    {
        if ($this->total_score >= 200) {
            return 'hot';
        }
        if ($this->total_score >= 100) {
            return 'warm';
        }
        if ($this->total_score >= 50) {
            return 'engaged';
        }
        return 'cold';
    }

    /**
     * Prepare data for CRM sync.
     */
    public function getCrmPayload(): array
    {
        return [
            'email' => $this->user->email,
            'lead_score' => $this->total_score,
            'tier' => $this->tier,
            'modules_completed' => $this->modules_completed,
            'certifications_earned' => $this->certifications_earned,
            'courses_started' => $this->courses_started,
            'courses_completed' => $this->courses_completed,
            'last_activity' => $this->last_activity_at?->toIso8601String(),
        ];
    }
}
