<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class UserNotification extends Model
{
    // Status constants
    public const STATUS_UNREAD = 'unread';
    public const STATUS_READ = 'read';
    public const STATUS_SNOOZED = 'snoozed';
    public const STATUS_DISMISSED = 'dismissed';
    public const STATUS_RESOLVED = 'resolved';

    // Category constants
    public const CATEGORY_SURVEY = 'survey';
    public const CATEGORY_REPORT = 'report';
    public const CATEGORY_STRATEGY = 'strategy';
    public const CATEGORY_WORKFLOW_ALERT = 'workflow_alert';
    public const CATEGORY_COURSE = 'course';
    public const CATEGORY_SYSTEM = 'system';

    // Priority constants
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'user_id',
        'org_id',
        'category',
        'type',
        'title',
        'body',
        'icon',
        'priority',
        'status',
        'action_url',
        'action_label',
        'notifiable_type',
        'notifiable_id',
        'metadata',
        'snoozed_until',
        'read_at',
        'resolved_at',
        'dismissed_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'snoozed_until' => 'datetime',
        'read_at' => 'datetime',
        'resolved_at' => 'datetime',
        'dismissed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_UNREAD,
        'priority' => self::PRIORITY_NORMAL,
    ];

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_UNREAD => 'Unread',
            self::STATUS_READ => 'Read',
            self::STATUS_SNOOZED => 'Snoozed',
            self::STATUS_DISMISSED => 'Dismissed',
            self::STATUS_RESOLVED => 'Resolved',
        ];
    }

    /**
     * Get all available categories.
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_SURVEY => [
                'label' => 'Surveys',
                'icon' => 'clipboard-document-list',
                'color' => 'blue',
            ],
            self::CATEGORY_REPORT => [
                'label' => 'Reports',
                'icon' => 'chart-bar',
                'color' => 'purple',
            ],
            self::CATEGORY_STRATEGY => [
                'label' => 'Strategy',
                'icon' => 'flag',
                'color' => 'green',
            ],
            self::CATEGORY_WORKFLOW_ALERT => [
                'label' => 'Alerts',
                'icon' => 'bell-alert',
                'color' => 'orange',
            ],
            self::CATEGORY_COURSE => [
                'label' => 'Courses',
                'icon' => 'academic-cap',
                'color' => 'teal',
            ],
            self::CATEGORY_SYSTEM => [
                'label' => 'System',
                'icon' => 'cog-6-tooth',
                'color' => 'gray',
            ],
        ];
    }

    /**
     * Get all available priorities.
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    // ==================== Relationships ====================

    /**
     * The user this notification belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The organization this notification belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * The user who created this notification.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * The source model that generated this notification.
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    // ==================== Scopes ====================

    /**
     * Scope to a specific user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to a specific organization.
     */
    public function scopeForOrg(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to active notifications (unread or read, not snoozed/dismissed/resolved).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_UNREAD, self::STATUS_READ]);
    }

    /**
     * Scope to unread notifications only.
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_UNREAD);
    }

    /**
     * Scope to snoozed notifications.
     */
    public function scopeSnoozed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SNOOZED);
    }

    /**
     * Scope to resolved notifications.
     */
    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    /**
     * Scope to dismissed notifications.
     */
    public function scopeDismissed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DISMISSED);
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to non-expired notifications.
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to snoozed notifications ready to resurface.
     */
    public function scopeReadyToUnsnooze(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SNOOZED)
                     ->where('snoozed_until', '<=', now());
    }

    /**
     * Scope to expired notifications.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now())
                     ->whereIn('status', [self::STATUS_UNREAD, self::STATUS_READ]);
    }

    /**
     * Order by priority then date.
     */
    public function scopeOrderByPriorityAndDate(Builder $query): Builder
    {
        return $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
                     ->orderByDesc('created_at');
    }

    // ==================== Status Methods ====================

    /**
     * Mark notification as read.
     */
    public function markAsRead(): bool
    {
        if ($this->status === self::STATUS_UNREAD) {
            $updated = $this->update([
                'status' => self::STATUS_READ,
                'read_at' => now(),
            ]);

            if ($updated) {
                $this->invalidateUnreadCount();
            }

            return $updated;
        }

        return false;
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(): bool
    {
        if (in_array($this->status, [self::STATUS_READ, self::STATUS_DISMISSED])) {
            $updated = $this->update([
                'status' => self::STATUS_UNREAD,
                'read_at' => null,
                'dismissed_at' => null,
            ]);

            if ($updated) {
                $this->invalidateUnreadCount();
            }

            return $updated;
        }

        return false;
    }

    /**
     * Snooze notification until a specific time.
     */
    public function snooze(\DateTimeInterface $until): bool
    {
        if (in_array($this->status, [self::STATUS_UNREAD, self::STATUS_READ])) {
            $updated = $this->update([
                'status' => self::STATUS_SNOOZED,
                'snoozed_until' => $until,
            ]);

            if ($updated) {
                $this->invalidateUnreadCount();
            }

            return $updated;
        }

        return false;
    }

    /**
     * Unsnooze notification (set back to unread).
     */
    public function unsnooze(): bool
    {
        if ($this->status === self::STATUS_SNOOZED) {
            $updated = $this->update([
                'status' => self::STATUS_UNREAD,
                'snoozed_until' => null,
            ]);

            if ($updated) {
                $this->invalidateUnreadCount();
            }

            return $updated;
        }

        return false;
    }

    /**
     * Mark notification as resolved.
     */
    public function resolve(): bool
    {
        if (in_array($this->status, [self::STATUS_UNREAD, self::STATUS_READ])) {
            $updated = $this->update([
                'status' => self::STATUS_RESOLVED,
                'resolved_at' => now(),
            ]);

            if ($updated) {
                $this->invalidateUnreadCount();
            }

            return $updated;
        }

        return false;
    }

    /**
     * Dismiss notification.
     */
    public function dismiss(): bool
    {
        if (in_array($this->status, [self::STATUS_UNREAD, self::STATUS_READ])) {
            $updated = $this->update([
                'status' => self::STATUS_DISMISSED,
                'dismissed_at' => now(),
            ]);

            if ($updated) {
                $this->invalidateUnreadCount();
            }

            return $updated;
        }

        return false;
    }

    // ==================== Helpers ====================

    /**
     * Check if notification is unread.
     */
    public function isUnread(): bool
    {
        return $this->status === self::STATUS_UNREAD;
    }

    /**
     * Check if notification is active (unread or read).
     */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_UNREAD, self::STATUS_READ]);
    }

    /**
     * Check if notification has high priority.
     */
    public function isHighPriority(): bool
    {
        return in_array($this->priority, [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    /**
     * Get category info (label, icon, color).
     */
    public function getCategoryInfoAttribute(): array
    {
        return self::getCategories()[$this->category] ?? [
            'label' => ucfirst($this->category),
            'icon' => 'bell',
            'color' => 'gray',
        ];
    }

    /**
     * Get the icon for this notification.
     */
    public function getDisplayIconAttribute(): string
    {
        return $this->icon ?? $this->category_info['icon'];
    }

    // ==================== Static Methods ====================

    /**
     * Get unread count for a user with caching.
     */
    public static function getUnreadCountForUser(int $userId): int
    {
        $cacheKey = "user_notifications_unread_count:{$userId}";

        return Cache::remember($cacheKey, 60, function () use ($userId) {
            return static::forUser($userId)
                ->unread()
                ->notExpired()
                ->count();
        });
    }

    /**
     * Invalidate the unread count cache for this notification's user.
     */
    protected function invalidateUnreadCount(): void
    {
        Cache::forget("user_notifications_unread_count:{$this->user_id}");
    }

    /**
     * Invalidate unread count cache for a specific user.
     */
    public static function invalidateUnreadCountForUser(int $userId): void
    {
        Cache::forget("user_notifications_unread_count:{$userId}");
    }
}
