<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ResourceHubLead extends Model
{
    // Lead sources
    public const SOURCE_RESOURCE_HUB = 'resource_hub';
    public const SOURCE_COURSE_PREVIEW = 'course_preview';
    public const SOURCE_EMBED = 'embed';
    public const SOURCE_DIRECT = 'direct';

    protected $fillable = [
        'org_id',
        'email',
        'name',
        'organization_name',
        'role',
        'phone',
        'source',
        'source_url',
        'utm_params',
        'interests',
        'metadata',
        'resource_views',
        'course_views',
        'email_verified_at',
        'verification_token',
        'last_activity_at',
    ];

    protected $casts = [
        'utm_params' => 'array',
        'interests' => 'array',
        'metadata' => 'array',
        'email_verified_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'resource_views' => 'integer',
        'course_views' => 'integer',
    ];

    /**
     * Organization this lead came from.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Scope by organization.
     */
    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to verified leads.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope to unverified leads.
     */
    public function scopeUnverified($query)
    {
        return $query->whereNull('email_verified_at');
    }

    /**
     * Scope by source.
     */
    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope to recent leads.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Check if email is verified.
     */
    public function isVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Generate verification token.
     */
    public function generateVerificationToken(): string
    {
        $this->verification_token = Str::random(64);
        $this->save();

        return $this->verification_token;
    }

    /**
     * Verify email with token.
     */
    public function verifyEmail(string $token): bool
    {
        if ($this->verification_token === $token) {
            $this->email_verified_at = now();
            $this->verification_token = null;
            $this->save();

            return true;
        }

        return false;
    }

    /**
     * Record a resource view.
     */
    public function recordResourceView(int $resourceId): void
    {
        $interests = $this->interests ?? [];
        $interests['resources'] = array_unique(array_merge(
            $interests['resources'] ?? [],
            [$resourceId]
        ));

        $this->update([
            'interests' => $interests,
            'resource_views' => $this->resource_views + 1,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Record a course view.
     */
    public function recordCourseView(int $courseId): void
    {
        $interests = $this->interests ?? [];
        $interests['courses'] = array_unique(array_merge(
            $interests['courses'] ?? [],
            [$courseId]
        ));

        $this->update([
            'interests' => $interests,
            'course_views' => $this->course_views + 1,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Get viewed resource IDs.
     */
    public function getViewedResourceIds(): array
    {
        return $this->interests['resources'] ?? [];
    }

    /**
     * Get viewed course IDs.
     */
    public function getViewedCourseIds(): array
    {
        return $this->interests['courses'] ?? [];
    }

    /**
     * Find or create lead by email for an organization.
     */
    public static function findOrCreateForOrg(int $orgId, string $email, array $attributes = []): self
    {
        return static::firstOrCreate(
            ['org_id' => $orgId, 'email' => strtolower(trim($email))],
            array_merge([
                'source' => self::SOURCE_RESOURCE_HUB,
                'interests' => [],
            ], $attributes)
        );
    }

    /**
     * Update lead with additional info.
     */
    public function updateInfo(array $attributes): void
    {
        $fillable = ['name', 'organization_name', 'role', 'phone'];

        foreach ($fillable as $field) {
            if (isset($attributes[$field]) && ! empty($attributes[$field])) {
                $this->{$field} = $attributes[$field];
            }
        }

        // Merge UTM params
        if (isset($attributes['utm_params'])) {
            $this->utm_params = array_merge(
                $this->utm_params ?? [],
                $attributes['utm_params']
            );
        }

        // Merge metadata
        if (isset($attributes['metadata'])) {
            $this->metadata = array_merge(
                $this->metadata ?? [],
                $attributes['metadata']
            );
        }

        $this->last_activity_at = now();
        $this->save();
    }

    /**
     * Get source options.
     */
    public static function getSourceOptions(): array
    {
        return [
            self::SOURCE_RESOURCE_HUB => 'Resource Hub',
            self::SOURCE_COURSE_PREVIEW => 'Course Preview',
            self::SOURCE_EMBED => 'Embedded Widget',
            self::SOURCE_DIRECT => 'Direct Link',
        ];
    }
}
