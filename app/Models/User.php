<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'org_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'primary_role',
        'preferred_contact_method',
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
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->primary_role === $role;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->hasRole('consultant');
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
}
