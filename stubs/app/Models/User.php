<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use MongoDB\Laravel\Auth\User as Authenticatable;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'users';

    protected $fillable = [
        'org_id',
        'accessible_org_ids',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'primary_role',
        'role_scopes',
        'sso_provider',
        'sso_id',
        'preferred_contact_method',
        'contact_preferences',
        'participant_ids',
        'department_ids',
        'learning_group_ids',
        'avatar_url',
        'bio',
        'last_login',
        'last_survey_completed',
        'email_verified',
        'phone_verified',
        'active',
        'suspended',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'accessible_org_ids' => 'array',
        'role_scopes' => 'array',
        'contact_preferences' => 'array',
        'participant_ids' => 'array',
        'department_ids' => 'array',
        'learning_group_ids' => 'array',
        'last_login' => 'datetime',
        'last_survey_completed' => 'datetime',
        'email_verified_at' => 'datetime',
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'active' => 'boolean',
        'suspended' => 'boolean',
    ];

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the user's primary organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the participants associated with this user (for instructors/direct_supervisors).
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class, 'user_id');
    }

    /**
     * Get the participant profile if this user is a participant.
     */
    public function learnerProfile(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'user_id', '_id');
    }

    /**
     * Get survey attempts made by this user.
     */
    public function surveyAttempts(): HasMany
    {
        return $this->hasMany(SurveyAttempt::class, 'surveyor_id');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->primary_role === $role;
    }

    /**
     * Check if user has permission.
     */
    public function hasPermission(string $permission): bool
    {
        $roleConfig = config("pulse.roles.{$this->primary_role}");

        if (! $roleConfig) {
            return false;
        }

        $permissions = $roleConfig['permissions'] ?? [];

        return in_array('*', $permissions) || in_array($permission, $permissions);
    }

    /**
     * Check if user can access an organization.
     */
    public function canAccessOrg(string $orgId): bool
    {
        // User's primary org
        if ($this->org_id === $orgId) {
            return true;
        }

        // Accessible orgs list
        if (in_array($orgId, $this->accessible_org_ids ?? [])) {
            return true;
        }

        // Check role scopes
        foreach ($this->role_scopes ?? [] as $scope) {
            if ($scope['org_id'] === $orgId) {
                return true;
            }
        }

        // Check if org is a descendant (for admins/consultants)
        if ($this->hasPermission('view_all_learners')) {
            $org = Organization::find($this->org_id);
            if ($org && in_array($this->org_id, Organization::find($orgId)?->ancestor_org_ids ?? [])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scope to filter by role.
     */
    public function scopeOfRole($query, string $role)
    {
        return $query->where('primary_role', $role);
    }

    /**
     * Scope to filter active users.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true)->where('suspended', false);
    }

    /**
     * Check if user is a instructor.
     */
    public function isTeacher(): bool
    {
        return $this->hasRole('instructor');
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('consultant');
    }

    /**
     * Check if user is a direct_supervisor.
     */
    public function isParent(): bool
    {
        return $this->hasRole('direct_supervisor');
    }
}
