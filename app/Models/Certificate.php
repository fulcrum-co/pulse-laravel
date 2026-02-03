<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Certificate extends Model
{
    public const TYPE_COMPLETION = 'completion';
    public const TYPE_BADGE = 'badge';
    public const TYPE_CREDENTIAL = 'credential';

    protected $fillable = [
        'uuid',
        'org_id',
        'user_id',
        'mini_course_id',
        'cohort_id',
        'cohort_member_id',
        'type',
        'title',
        'description',
        'badge_name',
        'badge_image_url',
        'certificate_url',
        'verification_url',
        'metadata',
        'issued_at',
        'expires_at',
        'is_revoked',
        'revoked_at',
        'revocation_reason',
        'shared_to_linkedin',
        'linkedin_shared_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_revoked' => 'boolean',
        'revoked_at' => 'datetime',
        'shared_to_linkedin' => 'boolean',
        'linkedin_shared_at' => 'datetime',
    ];

    protected $attributes = [
        'type' => self::TYPE_COMPLETION,
        'is_revoked' => false,
        'shared_to_linkedin' => false,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($certificate) {
            if (empty($certificate->uuid)) {
                $certificate->uuid = (string) Str::uuid();
            }
            if (empty($certificate->issued_at)) {
                $certificate->issued_at = now();
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(MiniCourse::class, 'mini_course_id');
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class);
    }

    public function cohortMember(): BelongsTo
    {
        return $this->belongsTo(CohortMember::class);
    }

    // Scopes
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    public function scopeValid(Builder $query): Builder
    {
        return $query->where('is_revoked', false)
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')
                     ->where('expires_at', '<=', now());
    }

    public function scopeRevoked(Builder $query): Builder
    {
        return $query->where('is_revoked', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeSharedToLinkedIn(Builder $query): Builder
    {
        return $query->where('shared_to_linkedin', true);
    }

    // Helper methods
    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_COMPLETION => 'Completion Certificate',
            self::TYPE_BADGE => 'Digital Badge',
            self::TYPE_CREDENTIAL => 'Professional Credential',
        ];
    }

    public function isValid(): bool
    {
        if ($this->is_revoked) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function revoke(string $reason = null): void
    {
        $this->update([
            'is_revoked' => true,
            'revoked_at' => now(),
            'revocation_reason' => $reason,
        ]);
    }

    public function markSharedToLinkedIn(): void
    {
        $this->update([
            'shared_to_linkedin' => true,
            'linkedin_shared_at' => now(),
        ]);
    }

    /**
     * Get the public verification URL.
     */
    public function getVerificationUrlAttribute(): string
    {
        return $this->attributes['verification_url']
            ?? route('certificates.verify', $this->uuid);
    }

    /**
     * Generate LinkedIn share URL.
     */
    public function getLinkedInShareUrl(): string
    {
        $params = [
            'certUrl' => $this->verification_url,
            'certId' => $this->uuid,
            'name' => $this->title,
            'organizationName' => $this->organization->name ?? '',
            'issueYear' => $this->issued_at->year,
            'issueMonth' => $this->issued_at->month,
        ];

        if ($this->expires_at) {
            $params['expirationYear'] = $this->expires_at->year;
            $params['expirationMonth'] = $this->expires_at->month;
        }

        return 'https://www.linkedin.com/profile/add?' . http_build_query($params);
    }
}
