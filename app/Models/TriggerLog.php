<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

class TriggerLog extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'trigger_logs';

    protected $fillable = [
        'trigger_id',
        'participant_id',
        'org_id',
        'triggering_event',
        'actions_executed',
    ];

    protected $casts = [
        'triggering_event' => 'array',
        'actions_executed' => 'array',
    ];

    /**
     * Get the trigger.
     */
    public function trigger(): BelongsTo
    {
        return $this->belongsTo(Trigger::class, 'trigger_id');
    }

    /**
     * Get the participant.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Check if all actions succeeded.
     */
    public function allActionsSucceeded(): bool
    {
        foreach ($this->actions_executed ?? [] as $action) {
            if ($action['status'] !== 'success') {
                return false;
            }
        }

        return true;
    }

    /**
     * Get failed actions.
     */
    public function getFailedActionsAttribute(): array
    {
        return array_filter($this->actions_executed ?? [], function ($action) {
            return $action['status'] === 'failed';
        });
    }
}
