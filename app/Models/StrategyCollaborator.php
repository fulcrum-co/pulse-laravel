<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategyCollaborator extends Model
{
    protected $fillable = [
        'strategic_plan_id',
        'user_id',
        'role',
    ];

    /**
     * Role constants.
     */
    public const ROLE_OWNER = 'owner';
    public const ROLE_COLLABORATOR = 'collaborator';
    public const ROLE_VIEWER = 'viewer';

    /**
     * Get the strategic plan.
     */
    public function strategicPlan(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class);
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this collaborator is an owner.
     */
    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    /**
     * Check if this collaborator can edit.
     */
    public function canEdit(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_COLLABORATOR]);
    }

    /**
     * Scope to filter by role.
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope to filter owners.
     */
    public function scopeOwners($query)
    {
        return $query->where('role', self::ROLE_OWNER);
    }
}
