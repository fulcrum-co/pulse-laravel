<?php

namespace App\Models;

use App\Traits\HasEmbedding;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use HasEmbedding, Searchable, SoftDeletes;

    // Provider types
    public const TYPE_THERAPIST = 'therapist';

    public const TYPE_TUTOR = 'tutor';

    public const TYPE_COACH = 'coach';

    public const TYPE_MENTOR = 'mentor';

    public const TYPE_COUNSELOR = 'support_person';

    public const TYPE_SPECIALIST = 'specialist';

    protected $fillable = [
        'org_id',
        'source_provider_id',
        'source_org_id',
        'name',
        'provider_type',
        'specialty_areas',
        'credentials',
        'bio',
        'contact_email',
        'contact_phone',
        'availability_notes',
        'hourly_rate',
        'accepts_insurance',
        'insurance_types',
        'location_address',
        'serves_remote',
        'serves_in_person',
        'service_radius_miles',
        'ratings_average',
        'ratings_count',
        'external_profile_url',
        'thumbnail_url',
        'active',
        'verified_at',
        'created_by',
    ];

    protected $casts = [
        'specialty_areas' => 'array',
        'insurance_types' => 'array',
        'hourly_rate' => 'decimal:2',
        'accepts_insurance' => 'boolean',
        'serves_remote' => 'boolean',
        'serves_in_person' => 'boolean',
        'ratings_average' => 'decimal:2',
        'active' => 'boolean',
        'verified_at' => 'datetime',
    ];

    protected $attributes = [
        'active' => true,
        'serves_remote' => false,
        'serves_in_person' => true,
        'accepts_insurance' => false,
        'ratings_average' => 0,
        'ratings_count' => 0,
    ];

    /**
     * Get available provider types.
     */
    public static function getProviderTypes(): array
    {
        return [
            self::TYPE_THERAPIST => 'Therapist',
            self::TYPE_TUTOR => 'Tutor',
            self::TYPE_COACH => 'Coach',
            self::TYPE_MENTOR => 'Mentor',
            self::TYPE_COUNSELOR => 'Support Person',
            self::TYPE_SPECIALIST => 'Specialist',
        ];
    }

    /**
     * Organization relationship.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Creator relationship.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the source provider this was pushed from.
     */
    public function sourceProvider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'source_provider_id');
    }

    /**
     * Get the source organization this was pushed from.
     */
    public function sourceOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'source_org_id');
    }

    /**
     * Get all providers pushed from this provider.
     */
    public function pushedProviders(): HasMany
    {
        return $this->hasMany(Provider::class, 'source_provider_id');
    }

    /**
     * Push this provider to another organization.
     * Creates a copy for the target org.
     */
    public function pushToOrganization(Organization $targetOrg, ?int $pushedBy = null): self
    {
        $newProvider = $this->replicate([
            'org_id',
            'source_provider_id',
            'source_org_id',
            'created_by',
            'verified_at',
            'ratings_average',
            'ratings_count',
        ]);

        $newProvider->org_id = $targetOrg->id;
        $newProvider->source_provider_id = $this->id;
        $newProvider->source_org_id = $this->org_id;
        $newProvider->created_by = $pushedBy;
        $newProvider->name = $this->name.' (from '.$this->organization->org_name.')';
        $newProvider->ratings_average = 0;
        $newProvider->ratings_count = 0;
        $newProvider->verified_at = null;
        $newProvider->save();

        return $newProvider;
    }

    /**
     * Check if this provider was pushed from another organization.
     */
    public function wasPushed(): bool
    {
        return $this->source_provider_id !== null;
    }

    /**
     * Mini-course steps that reference this provider.
     */
    public function courseSteps(): HasMany
    {
        return $this->hasMany(MiniCourseStep::class);
    }

    /**
     * Provider account (for login/notifications).
     */
    public function account(): HasOne
    {
        return $this->hasOne(ProviderAccount::class);
    }

    /**
     * Conversations with this provider.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(ProviderConversation::class);
    }

    /**
     * Bookings with this provider.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(ProviderBooking::class);
    }

    /**
     * Payments to this provider.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(ProviderPayment::class);
    }

    /**
     * Participant assignments for this provider.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ProviderAssignment::class);
    }

    /**
     * Scope to active providers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope to verified providers.
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Scope by provider type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('provider_type', $type);
    }

    /**
     * Scope by specialty.
     */
    public function scopeWithSpecialty(Builder $query, string $specialty): Builder
    {
        return $query->whereJsonContains('specialty_areas', $specialty);
    }

    /**
     * Scope to providers serving remotely.
     */
    public function scopeServesRemote(Builder $query): Builder
    {
        return $query->where('serves_remote', true);
    }

    /**
     * Scope to providers serving in person.
     */
    public function scopeServesInPerson(Builder $query): Builder
    {
        return $query->where('serves_in_person', true);
    }

    /**
     * Scope by organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Check if provider is verified.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Mark provider as verified.
     */
    public function markVerified(): void
    {
        $this->update(['verified_at' => now()]);
    }

    /**
     * Update rating based on new review.
     */
    public function addRating(float $rating): void
    {
        $totalRatings = ($this->ratings_average * $this->ratings_count) + $rating;
        $newCount = $this->ratings_count + 1;

        $this->update([
            'ratings_average' => $totalRatings / $newCount,
            'ratings_count' => $newCount,
        ]);
    }

    /**
     * Get display name with credentials.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->credentials) {
            return "{$this->name}, {$this->credentials}";
        }

        return $this->name;
    }

    /**
     * Get formatted hourly rate.
     */
    public function getFormattedRateAttribute(): ?string
    {
        if ($this->hourly_rate) {
            return '$'.number_format($this->hourly_rate, 2).'/hr';
        }

        return null;
    }

    /**
     * Get the indexable data array for the model (Meilisearch).
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'org_id' => $this->org_id,
            'display_name' => $this->getDisplayNameAttribute(),
            'name' => $this->name,
            'bio' => $this->bio,
            'provider_type' => $this->provider_type,
            'specialties' => $this->specialty_areas ?? [],
            'credentials' => $this->credentials,
            'is_verified' => $this->verified_at !== null,
            'serves_remote' => (bool) $this->serves_remote,
            'serves_in_person' => (bool) $this->serves_in_person,
            'is_active' => (bool) $this->active,
            'avg_rating' => $this->ratings_average,
            'created_at' => $this->created_at?->getTimestamp(),
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return ! $this->trashed() && $this->active;
    }

    /**
     * Get the text to be embedded for semantic search.
     */
    public function getEmbeddingText(): string
    {
        $parts = [
            $this->name,
            $this->bio,
            $this->provider_type,
            $this->credentials,
        ];

        if (! empty($this->specialty_areas)) {
            $specialties = is_array($this->specialty_areas) ? $this->specialty_areas : [];
            $parts[] = 'Specialties: '.implode(', ', $specialties);
        }

        $serviceTypes = [];
        if ($this->serves_remote) {
            $serviceTypes[] = 'remote services';
        }
        if ($this->serves_in_person) {
            $serviceTypes[] = 'in-person services';
        }
        if (! empty($serviceTypes)) {
            $parts[] = 'Offers: '.implode(' and ', $serviceTypes);
        }

        return implode('. ', array_filter($parts));
    }

    /**
     * Get the fields that contribute to the embedding text.
     */
    protected function getEmbeddingTextFields(): array
    {
        return ['name', 'bio', 'provider_type', 'credentials', 'specialty_areas'];
    }
}
