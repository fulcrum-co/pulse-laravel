<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    /**
     * Default notification preferences schema.
     * Each category has channel preferences (in_app, email, sms).
     */
    public const DEFAULT_NOTIFICATION_PREFERENCES = [
        'workflow' => ['in_app' => true, 'email' => false, 'sms' => false],
        'workflow_custom' => ['in_app' => true, 'email' => false, 'sms' => false],
        'survey' => ['in_app' => true, 'email' => true, 'sms' => false],
        'report' => ['in_app' => true, 'email' => true, 'sms' => false],
        'strategy' => ['in_app' => true, 'email' => true, 'sms' => false],
        'course' => ['in_app' => true, 'email' => true, 'sms' => false],
        'collection' => ['in_app' => true, 'email' => false, 'sms' => false],
        'system' => ['in_app' => true, 'email' => true, 'sms' => false],
        'quiet_hours' => ['enabled' => false, 'start' => '21:00', 'end' => '07:00'],
    ];

    protected $fillable = [
        'org_id',
        'current_org_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'primary_role',
        'preferred_contact_method',
        'notification_preferences',
        'avatar_url',
        'bio',
        'last_login',
        'email_verified_at',
        'active',
        'suspended',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
        'suspended' => 'boolean',
        'notification_preferences' => 'array',
    ];

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the user's primary organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user's currently active organization.
     * Falls back to primary organization if not set.
     */
    public function currentOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'current_org_id');
    }

    /**
     * Get all organizations this user has access to.
     */
    public function organizations(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'user_organizations')
            ->withPivot(['role', 'is_primary', 'can_manage'])
            ->withTimestamps();
    }

    /**
     * Get the effective current organization (current_org_id or fallback to org_id).
     */
    public function getEffectiveOrganization(): ?Organization
    {
        return $this->currentOrganization ?? $this->organization;
    }

    /**
     * Get the effective organization ID.
     */
    public function getEffectiveOrgIdAttribute(): ?int
    {
        return $this->current_org_id ?? $this->org_id;
    }

    /**
     * Get all organizations this user can access (including via hierarchy).
     */
    public function getAccessibleOrganizations(): \Illuminate\Support\Collection
    {
        $accessible = collect();

        // Add primary organization
        if ($this->organization) {
            $accessible->push($this->organization);
        }

        // Add explicitly assigned organizations
        foreach ($this->organizations as $org) {
            if (!$accessible->contains('id', $org->id)) {
                $accessible->push($org);
            }
        }

        // For consultants/admins, add child organizations of their primary org
        if ($this->isAdmin() && $this->organization) {
            $children = $this->organization->getDownstreamOrganizations();
            foreach ($children as $child) {
                if (!$accessible->contains('id', $child->id)) {
                    $accessible->push($child);
                }
            }
        }

        return $accessible->sortBy('org_name');
    }

    /**
     * Get child organizations the user can manage (for consultants/superintendents).
     */
    public function getManagedChildOrganizations(): \Illuminate\Support\Collection
    {
        if (!$this->organization) {
            return collect();
        }

        // Direct children of the user's primary organization
        return $this->organization->children()->active()->orderBy('org_name')->get();
    }

    /**
     * Check if user can access a specific organization.
     */
    public function canAccessOrganization(int $orgId): bool
    {
        // Can access primary organization
        if ($this->org_id === $orgId) {
            return true;
        }

        // Can access explicitly assigned organizations
        if ($this->organizations()->where('organizations.id', $orgId)->exists()) {
            return true;
        }

        // Consultants/admins can access child organizations
        if ($this->isAdmin() && $this->organization) {
            $descendants = $this->organization->getDownstreamOrganizations();
            if ($descendants->contains('id', $orgId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Switch to a different organization.
     */
    public function switchOrganization(int $orgId): bool
    {
        if (!$this->canAccessOrganization($orgId)) {
            return false;
        }

        $this->update(['current_org_id' => $orgId]);
        return true;
    }

    /**
     * Reset to primary organization.
     */
    public function resetToHomeOrganization(): void
    {
        $this->update(['current_org_id' => null]);
    }

    /**
     * Get the effective role, respecting demo mode override.
     * Any authenticated user can use demo mode for testing different views.
     */
    public function getEffectiveRoleAttribute(): string
    {
        // Check if we're in demo mode
        $demoRole = session('demo_role_override');

        if ($demoRole && $demoRole !== 'actual') {
            return $demoRole;
        }

        return $this->primary_role;
    }

    /**
     * Check if user is currently in demo mode.
     */
    public function isInDemoMode(): bool
    {
        $demoRole = session('demo_role_override');
        return $demoRole && $demoRole !== 'actual';
    }

    /**
     * Get the demo role label for display.
     */
    public function getDemoRoleLabelAttribute(): ?string
    {
        if (!$this->isInDemoMode()) {
            return null;
        }

        $labels = [
            'consultant' => 'District Consultant',
            'superintendent' => 'Superintendent',
            'school_admin' => 'School Administrator',
            'counselor' => 'School Counselor',
            'teacher' => 'Teacher',
            'student' => 'Student',
            'parent' => 'Parent/Guardian',
        ];

        return $labels[session('demo_role_override')] ?? null;
    }

    /**
     * Check if user has a specific role (respects demo mode).
     */
    public function hasRole(string $role): bool
    {
        return $this->effective_role === $role;
    }

    /**
     * Check if user's actual (non-demo) role matches.
     */
    public function hasActualRole(string $role): bool
    {
        return $this->primary_role === $role;
    }

    /**
     * Check if user is an admin (respects demo mode).
     */
    public function isAdmin(): bool
    {
        $role = $this->effective_role;
        return in_array($role, ['admin', 'consultant', 'superintendent']);
    }

    /**
     * Check if user is actually an admin (ignores demo mode).
     * Use this for security-critical checks.
     */
    public function isActualAdmin(): bool
    {
        return $this->primary_role === 'admin' || $this->primary_role === 'consultant';
    }

    /**
     * Get metrics for this user (contact view for teachers/staff).
     */
    public function metrics(): MorphMany
    {
        return $this->morphMany(ContactMetric::class, 'contact');
    }

    /**
     * Get notes for this user (contact view).
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(ContactNote::class, 'contact');
    }

    /**
     * Get notes authored by this user.
     */
    public function authoredNotes(): HasMany
    {
        return $this->hasMany(ContactNote::class, 'created_by');
    }

    /**
     * Get classroom metrics for teachers.
     */
    public function classroomMetrics(): MorphMany
    {
        return $this->metrics()->where('metric_category', ContactMetric::CATEGORY_CLASSROOM);
    }

    /**
     * Get professional development metrics for teachers.
     */
    public function pdMetrics(): MorphMany
    {
        return $this->metrics()->where('metric_category', ContactMetric::CATEGORY_PD);
    }

    /**
     * Scope to filter active users.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true)->where('suspended', false);
    }

    /**
     * Get user's notifications.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    /**
     * Get merged notification preferences with defaults.
     */
    public function getNotificationPreferencesAttribute($value): array
    {
        $stored = $value ? json_decode($value, true) : [];
        return array_replace_recursive(self::DEFAULT_NOTIFICATION_PREFERENCES, $stored);
    }

    /**
     * Get a specific notification preference.
     *
     * @param string $category e.g., 'survey', 'workflow', 'strategy'
     * @param string $channel e.g., 'in_app', 'email', 'sms'
     * @return bool
     */
    public function getNotificationPreference(string $category, string $channel): bool
    {
        $prefs = $this->notification_preferences;
        return $prefs[$category][$channel] ?? false;
    }

    /**
     * Update notification preferences (merges with existing).
     *
     * @param array $preferences Partial preferences to merge
     * @return bool
     */
    public function updateNotificationPreferences(array $preferences): bool
    {
        $current = $this->getRawOriginal('notification_preferences');
        $existing = $current ? json_decode($current, true) : [];
        $merged = array_replace_recursive($existing, $preferences);

        return $this->update(['notification_preferences' => $merged]);
    }

    /**
     * Check if user wants to receive notifications via a specific channel.
     *
     * @param string $category Notification category
     * @param string $channel Delivery channel (in_app, email, sms)
     * @return bool
     */
    public function wantsNotificationVia(string $category, string $channel): bool
    {
        // in_app is always true for all categories
        if ($channel === 'in_app') {
            return true;
        }

        return $this->getNotificationPreference($category, $channel);
    }

    /**
     * Check if user is currently in quiet hours.
     * During quiet hours, email and SMS notifications are suppressed.
     *
     * @return bool
     */
    public function isInQuietHours(): bool
    {
        $prefs = $this->notification_preferences;
        $quietHours = $prefs['quiet_hours'] ?? [];

        if (!($quietHours['enabled'] ?? false)) {
            return false;
        }

        $start = $quietHours['start'] ?? '21:00';
        $end = $quietHours['end'] ?? '07:00';

        $now = Carbon::now();
        $startTime = Carbon::parse($start);
        $endTime = Carbon::parse($end);

        // Handle overnight quiet hours (e.g., 21:00 to 07:00)
        if ($startTime->gt($endTime)) {
            // Quiet hours span midnight
            return $now->gte($startTime) || $now->lt($endTime);
        }

        // Normal range within same day
        return $now->gte($startTime) && $now->lt($endTime);
    }

    /**
     * Get quiet hours settings.
     *
     * @return array
     */
    public function getQuietHoursSettings(): array
    {
        $prefs = $this->notification_preferences;
        return $prefs['quiet_hours'] ?? [
            'enabled' => false,
            'start' => '21:00',
            'end' => '07:00',
        ];
    }

    /**
     * Set quiet hours.
     *
     * @param bool $enabled
     * @param string|null $start Time in HH:MM format
     * @param string|null $end Time in HH:MM format
     * @return bool
     */
    public function setQuietHours(bool $enabled, ?string $start = null, ?string $end = null): bool
    {
        $quietHours = [
            'enabled' => $enabled,
        ];

        if ($start !== null) {
            $quietHours['start'] = $start;
        }
        if ($end !== null) {
            $quietHours['end'] = $end;
        }

        return $this->updateNotificationPreferences(['quiet_hours' => $quietHours]);
    }
}
