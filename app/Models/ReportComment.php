<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportComment extends Model
{
    use SoftDeletes;

    protected $table = 'report_comments';

    protected $fillable = [
        'custom_report_id',
        'user_id',
        'parent_id',
        'element_id',
        'content',
        'position_x',
        'position_y',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'position_x' => 'float',
        'position_y' => 'float',
        'resolved_at' => 'datetime',
    ];

    /**
     * Get the report this comment belongs to.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(CustomReport::class, 'custom_report_id');
    }

    /**
     * Get the user who created this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent comment (for replies).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ReportComment::class, 'parent_id');
    }

    /**
     * Get replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(ReportComment::class, 'parent_id');
    }

    /**
     * Get the user who resolved this comment.
     */
    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope to get top-level comments (not replies).
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get unresolved comments.
     */
    public function scopeUnresolved($query)
    {
        return $query->whereNull('resolved_at');
    }

    /**
     * Scope to get resolved comments.
     */
    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    /**
     * Mark comment as resolved.
     */
    public function resolve(?int $userId = null): void
    {
        $this->resolved_at = now();
        $this->resolved_by = $userId ?? auth()->id();
        $this->save();
    }

    /**
     * Unresolve the comment.
     */
    public function unresolve(): void
    {
        $this->resolved_at = null;
        $this->resolved_by = null;
        $this->save();
    }

    /**
     * Check if comment is resolved.
     */
    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }
}
