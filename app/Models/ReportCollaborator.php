<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCollaborator extends Model
{
    protected $table = 'report_collaborators';

    protected $fillable = [
        'custom_report_id',
        'user_id',
        'role',
        'invited_by',
        'invited_at',
        'last_seen_at',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
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
     * Check if collaborator can edit.
     */
    public function canEdit(): bool
    {
        return in_array($this->role, ['editor', 'owner']);
    }

    /**
     * Check if collaborator can view.
     */
    public function canView(): bool
    {
        return in_array($this->role, ['viewer', 'editor', 'owner']);
    }

    /**
     * Update last seen timestamp.
     */
    public function updateLastSeen(): bool
    {
        $this->last_seen_at = now();

        return $this->save();
    }
}
