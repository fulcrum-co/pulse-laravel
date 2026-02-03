<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCollaborator extends Model
{
    protected $fillable = [
        'custom_report_id',
        'user_id',
        'role',
        'invited_by',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get the report this collaborator belongs to.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(CustomReport::class, 'custom_report_id');
    }

    /**
     * Get the user who is collaborating.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who invited this collaborator.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Scope to get active collaborators (seen in last 5 minutes).
     */
    public function scopeActive($query)
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes(5));
    }

    /**
     * Check if this collaborator can edit.
     */
    public function canEdit(): bool
    {
        return in_array($this->role, ['owner', 'editor']);
    }

    /**
     * Check if this collaborator can view.
     */
    public function canView(): bool
    {
        return in_array($this->role, ['owner', 'editor', 'viewer']);
    }

    /**
     * Update last seen timestamp.
     */
    public function touchLastSeen(): void
    {
        $this->update(['last_seen_at' => now()]);
    }
}
