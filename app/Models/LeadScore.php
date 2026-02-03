<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadScore extends Model
{
    use HasFactory;

    // Point values for different actions
    public const POINTS_LOGIN = 5;
    public const POINTS_MODULE_COMPLETED = 20;
    public const POINTS_COURSE_COMPLETED = 50;
    public const POINTS_CERTIFICATION_EARNED = 100;
    public const POINTS_COURSE_STARTED = 10;

    // Decay settings
    public const DECAY_DAYS = 30;
    public const DECAY_PERCENT = 10;

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
        'crm_synced_at' => 'datetime',
        'score_history' => 'array',
        'crm_sync_data' => 'array',
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
        return $this->hasMany(LeadScoreEvent::class);
    }

    /**
     * Add points to the lead score.
     */
    public function addPoints(
        int $points,
        string $eventType,
        ?string $description = null,
        ?Model $scoreable = null
    ): LeadScoreEvent {
        $this->total_score += $points;
        $this->last_activity_at = now();

        // Add to score history
        $history = $this->score_history ?? [];
        $history[] = [
            'date' => now()->toDateString(),
            'points' => $points,
            'type' => $eventType,
            'total' => $this->total_score,
        ];

        // Keep last 100 history entries
        if (count($history) > 100) {
            $history = array_slice($history, -100);
        }

        $this->score_history = $history;
        $this->save();

        // Create event record
        return $this->events()->create([
            'event_type' => $eventType,
            'points' => $points,
            'description' => $description,
            'scoreable_type' => $scoreable ? get_class($scoreable) : null,
            'scoreable_id' => $scoreable?->id,
        ]);
    }

    /**
     * Apply decay to score based on inactivity.
     */
    public function applyDecay(): void
    {
        if (!$this->last_activity_at) {
            return;
        }

        $daysSinceActivity = $this->last_activity_at->diffInDays(now());

        if ($daysSinceActivity < self::DECAY_DAYS) {
            return;
        }

        // Calculate decay periods
        $decayPeriods = floor($daysSinceActivity / self::DECAY_DAYS);
        $decayMultiplier = pow(1 - (self::DECAY_PERCENT / 100), $decayPeriods);

        $oldScore = $this->total_score;
        $newScore = (int) round($this->total_score * $decayMultiplier);
        $pointsLost = $oldScore - $newScore;

        if ($pointsLost > 0) {
            $this->total_score = $newScore;
            $this->last_decay_at = now();
            $this->save();

            // Record decay event
            $this->events()->create([
                'event_type' => LeadScoreEvent::TYPE_DECAY,
                'points' => -$pointsLost,
                'description' => "Score decay after {$daysSinceActivity} days of inactivity",
            ]);
        }
    }

    /**
     * Get score tier/level.
     */
    public function getTier(): string
    {
        if ($this->total_score >= 500) {
            return 'hot';
        }
        if ($this->total_score >= 200) {
            return 'warm';
        }
        if ($this->total_score >= 50) {
            return 'engaged';
        }

        return 'cold';
    }

    /**
     * Scope to get high-scoring leads.
     */
    public function scopeHot($query)
    {
        return $query->where('total_score', '>=', 500);
    }

    /**
     * Scope to get warm leads.
     */
    public function scopeWarm($query)
    {
        return $query->whereBetween('total_score', [200, 499]);
    }

    /**
     * Scope to get leads needing sync.
     */
    public function scopeNeedsCrmSync($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('crm_synced_at')
                ->orWhereColumn('updated_at', '>', 'crm_synced_at');
        });
    }
}
