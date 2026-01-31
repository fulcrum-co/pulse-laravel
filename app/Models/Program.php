<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Program extends Model
{
    use SoftDeletes;

    // Program types
    public const TYPE_THERAPY = 'therapy';
    public const TYPE_TUTORING = 'tutoring';
    public const TYPE_MENTORSHIP = 'mentorship';
    public const TYPE_ENRICHMENT = 'enrichment';
    public const TYPE_INTERVENTION = 'intervention';
    public const TYPE_SUPPORT_GROUP = 'support_group';
    public const TYPE_EXTERNAL_SERVICE = 'external_service';

    // Cost structures
    public const COST_FREE = 'free';
    public const COST_SLIDING_SCALE = 'sliding_scale';
    public const COST_FIXED = 'fixed';
    public const COST_INSURANCE = 'insurance';

    // Location types
    public const LOCATION_IN_PERSON = 'in_person';
    public const LOCATION_VIRTUAL = 'virtual';
    public const LOCATION_HYBRID = 'hybrid';

    protected $fillable = [
        'org_id',
        'source_program_id',
        'source_org_id',
        'name',
        'description',
        'program_type',
        'provider_org_name',
        'target_needs',
        'eligibility_criteria',
        'cost_structure',
        'cost_details',
        'duration_weeks',
        'frequency_per_week',
        'location_type',
        'location_address',
        'contact_info',
        'enrollment_url',
        'capacity',
        'current_enrollment',
        'start_date',
        'end_date',
        'is_rolling_enrollment',
        'active',
        'created_by',
    ];

    protected $casts = [
        'target_needs' => 'array',
        'eligibility_criteria' => 'array',
        'contact_info' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_rolling_enrollment' => 'boolean',
        'active' => 'boolean',
    ];

    protected $attributes = [
        'active' => true,
        'is_rolling_enrollment' => false,
        'current_enrollment' => 0,
    ];

    /**
     * Get available program types.
     */
    public static function getProgramTypes(): array
    {
        return [
            self::TYPE_THERAPY => 'Therapy',
            self::TYPE_TUTORING => 'Tutoring',
            self::TYPE_MENTORSHIP => 'Mentorship',
            self::TYPE_ENRICHMENT => 'Enrichment',
            self::TYPE_INTERVENTION => 'Intervention',
            self::TYPE_SUPPORT_GROUP => 'Support Group',
            self::TYPE_EXTERNAL_SERVICE => 'External Service',
        ];
    }

    /**
     * Get available cost structures.
     */
    public static function getCostStructures(): array
    {
        return [
            self::COST_FREE => 'Free',
            self::COST_SLIDING_SCALE => 'Sliding Scale',
            self::COST_FIXED => 'Fixed Cost',
            self::COST_INSURANCE => 'Insurance',
        ];
    }

    /**
     * Get available location types.
     */
    public static function getLocationTypes(): array
    {
        return [
            self::LOCATION_IN_PERSON => 'In Person',
            self::LOCATION_VIRTUAL => 'Virtual',
            self::LOCATION_HYBRID => 'Hybrid',
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
     * Get the source program this was pushed from.
     */
    public function sourceProgram(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'source_program_id');
    }

    /**
     * Get the source organization this was pushed from.
     */
    public function sourceOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'source_org_id');
    }

    /**
     * Get all programs pushed from this program.
     */
    public function pushedPrograms(): HasMany
    {
        return $this->hasMany(Program::class, 'source_program_id');
    }

    /**
     * Push this program to another organization.
     * Creates a copy for the target org.
     */
    public function pushToOrganization(Organization $targetOrg, ?int $pushedBy = null): self
    {
        $newProgram = $this->replicate([
            'org_id',
            'source_program_id',
            'source_org_id',
            'created_by',
            'current_enrollment',
        ]);

        $newProgram->org_id = $targetOrg->id;
        $newProgram->source_program_id = $this->id;
        $newProgram->source_org_id = $this->org_id;
        $newProgram->created_by = $pushedBy;
        $newProgram->name = $this->name . ' (from ' . $this->organization->org_name . ')';
        $newProgram->current_enrollment = 0;
        $newProgram->save();

        return $newProgram;
    }

    /**
     * Check if this program was pushed from another organization.
     */
    public function wasPushed(): bool
    {
        return $this->source_program_id !== null;
    }

    /**
     * Mini-course steps that reference this program.
     */
    public function courseSteps(): HasMany
    {
        return $this->hasMany(MiniCourseStep::class);
    }

    /**
     * Student enrollments for this program.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(ProgramEnrollment::class);
    }

    /**
     * Scope to active programs.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope by program type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('program_type', $type);
    }

    /**
     * Scope by cost structure.
     */
    public function scopeWithCostStructure(Builder $query, string $structure): Builder
    {
        return $query->where('cost_structure', $structure);
    }

    /**
     * Scope to free programs.
     */
    public function scopeFree(Builder $query): Builder
    {
        return $query->where('cost_structure', self::COST_FREE);
    }

    /**
     * Scope by location type.
     */
    public function scopeByLocation(Builder $query, string $locationType): Builder
    {
        return $query->where('location_type', $locationType);
    }

    /**
     * Scope to programs with availability.
     */
    public function scopeHasAvailability(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('capacity')
              ->orWhereRaw('current_enrollment < capacity');
        });
    }

    /**
     * Scope to currently running programs.
     */
    public function scopeCurrentlyRunning(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('is_rolling_enrollment', true)
              ->orWhere(function ($q2) {
                  $q2->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
              });
        });
    }

    /**
     * Scope by target need.
     */
    public function scopeForNeed(Builder $query, string $need): Builder
    {
        return $query->whereJsonContains('target_needs', $need);
    }

    /**
     * Scope by organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Check if program has available spots.
     */
    public function hasAvailability(): bool
    {
        if ($this->capacity === null) {
            return true;
        }
        return $this->current_enrollment < $this->capacity;
    }

    /**
     * Get spots remaining.
     */
    public function getSpotsRemainingAttribute(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }
        return max(0, $this->capacity - $this->current_enrollment);
    }

    /**
     * Increment enrollment count.
     */
    public function incrementEnrollment(): void
    {
        $this->increment('current_enrollment');
    }

    /**
     * Decrement enrollment count.
     */
    public function decrementEnrollment(): void
    {
        if ($this->current_enrollment > 0) {
            $this->decrement('current_enrollment');
        }
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration_weeks) {
            return null;
        }

        $weeks = $this->duration_weeks;
        $frequency = $this->frequency_per_week;

        $duration = $weeks . ' ' . ($weeks === 1 ? 'week' : 'weeks');

        if ($frequency) {
            $duration .= ', ' . $frequency . 'x/week';
        }

        return $duration;
    }

    /**
     * Check if program is currently running.
     */
    public function isCurrentlyRunning(): bool
    {
        if ($this->is_rolling_enrollment) {
            return true;
        }

        if ($this->start_date && $this->end_date) {
            return now()->between($this->start_date, $this->end_date);
        }

        return true;
    }
}
