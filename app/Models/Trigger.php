<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trigger extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'triggers';

    protected $fillable = [
        'org_id',
        'created_by',
        'trigger_name',
        'trigger_description',
        'operations',
        'operand_condition',
        'actions',
        'frequency',
        'cooldown_hours',
        'active',
        'triggered_count',
        'last_triggered_at',
    ];

    protected $casts = [
        'operations' => 'array',
        'actions' => 'array',
        'active' => 'boolean',
        'triggered_count' => 'integer',
        'cooldown_hours' => 'integer',
        'last_triggered_at' => 'datetime',
    ];

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who created this trigger.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get trigger logs.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TriggerLog::class, 'trigger_id');
    }

    /**
     * Check if trigger is in cooldown period.
     */
    public function isInCooldown(): bool
    {
        if (!$this->last_triggered_at || !$this->cooldown_hours) {
            return false;
        }

        return $this->last_triggered_at->addHours($this->cooldown_hours)->isFuture();
    }

    /**
     * Record that trigger was activated.
     */
    public function recordActivation(): void
    {
        $this->increment('triggered_count');
        $this->update(['last_triggered_at' => now()]);
    }

    /**
     * Scope to filter active triggers.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrg($query, string $orgId)
    {
        return $query->where('org_id', $orgId);
    }
}
