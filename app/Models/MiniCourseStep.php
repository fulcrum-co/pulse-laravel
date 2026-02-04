<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MiniCourseStep extends Model
{
    use SoftDeletes;

    // Step types
    public const TYPE_CONTENT = 'content';

    public const TYPE_REFLECTION = 'reflection';

    public const TYPE_ACTION = 'action';

    public const TYPE_PRACTICE = 'practice';

    public const TYPE_HUMAN_CONNECTION = 'human_connection';

    public const TYPE_ASSESSMENT = 'assessment';

    public const TYPE_CHECKPOINT = 'checkpoint';

    // Content types
    public const CONTENT_TEXT = 'text';

    public const CONTENT_VIDEO = 'video';

    public const CONTENT_DOCUMENT = 'document';

    public const CONTENT_LINK = 'link';

    public const CONTENT_EMBEDDED = 'embedded';

    public const CONTENT_INTERACTIVE = 'interactive';

    protected $fillable = [
        'mini_course_id',
        'sort_order',
        'step_type',
        'title',
        'description',
        'instructions',
        'content_type',
        'content_data',
        'resource_id',
        'provider_id',
        'program_id',
        'estimated_duration_minutes',
        'is_required',
        'completion_criteria',
        'branching_logic',
        'feedback_prompt',
    ];

    protected $casts = [
        'content_data' => 'array',
        'completion_criteria' => 'array',
        'branching_logic' => 'array',
        'is_required' => 'boolean',
    ];

    protected $attributes = [
        'step_type' => self::TYPE_CONTENT,
        'content_type' => self::CONTENT_TEXT,
        'is_required' => true,
        'sort_order' => 0,
    ];

    /**
     * Get available step types.
     */
    public static function getStepTypes(): array
    {
        return [
            self::TYPE_CONTENT => [
                'label' => 'Content',
                'description' => 'Educational content to consume',
                'icon' => 'document-text',
            ],
            self::TYPE_REFLECTION => [
                'label' => 'Reflection',
                'description' => 'Prompt for student reflection',
                'icon' => 'chat-bubble-left-ellipsis',
            ],
            self::TYPE_ACTION => [
                'label' => 'Action',
                'description' => 'Task or activity to complete',
                'icon' => 'check-circle',
            ],
            self::TYPE_PRACTICE => [
                'label' => 'Practice',
                'description' => 'Practice exercises',
                'icon' => 'academic-cap',
            ],
            self::TYPE_HUMAN_CONNECTION => [
                'label' => 'Human Connection',
                'description' => 'Connect with a provider or mentor',
                'icon' => 'user-group',
            ],
            self::TYPE_ASSESSMENT => [
                'label' => 'Assessment',
                'description' => 'Quiz or assessment',
                'icon' => 'clipboard-document-check',
            ],
            self::TYPE_CHECKPOINT => [
                'label' => 'Checkpoint',
                'description' => 'Progress checkpoint',
                'icon' => 'flag',
            ],
        ];
    }

    /**
     * Get available content types.
     */
    public static function getContentTypes(): array
    {
        return [
            self::CONTENT_TEXT => 'Text',
            self::CONTENT_VIDEO => 'Video',
            self::CONTENT_DOCUMENT => 'Document',
            self::CONTENT_LINK => 'External Link',
            self::CONTENT_EMBEDDED => 'Embedded Content',
            self::CONTENT_INTERACTIVE => 'Interactive',
        ];
    }

    /**
     * The mini-course this step belongs to.
     */
    public function miniCourse(): BelongsTo
    {
        return $this->belongsTo(MiniCourse::class);
    }

    /**
     * Linked resource (optional).
     */
    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * Suggested provider for human connection steps.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Suggested program for this step.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Progress records for this step.
     */
    public function progress(): HasMany
    {
        return $this->hasMany(MiniCourseStepProgress::class, 'step_id');
    }

    /**
     * Scope to required steps.
     */
    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope to optional steps.
     */
    public function scopeOptional(Builder $query): Builder
    {
        return $query->where('is_required', false);
    }

    /**
     * Scope by step type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('step_type', $type);
    }

    /**
     * Get the previous step.
     */
    public function getPreviousStepAttribute(): ?self
    {
        return $this->miniCourse->steps()
            ->where('sort_order', '<', $this->sort_order)
            ->orderByDesc('sort_order')
            ->first();
    }

    /**
     * Get the next step.
     */
    public function getNextStepAttribute(): ?self
    {
        return $this->miniCourse->steps()
            ->where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Check if this is the first step.
     */
    public function isFirstStep(): bool
    {
        return $this->previous_step === null;
    }

    /**
     * Check if this is the last step.
     */
    public function isLastStep(): bool
    {
        return $this->next_step === null;
    }

    /**
     * Get step number (1-indexed).
     */
    public function getStepNumberAttribute(): int
    {
        return $this->miniCourse->steps()
            ->where('sort_order', '<=', $this->sort_order)
            ->count();
    }

    /**
     * Get step type info.
     */
    public function getStepTypeInfoAttribute(): array
    {
        return self::getStepTypes()[$this->step_type] ?? [
            'label' => ucfirst($this->step_type),
            'description' => '',
            'icon' => 'document',
        ];
    }

    /**
     * Check if step is a human connection type.
     */
    public function isHumanConnection(): bool
    {
        return $this->step_type === self::TYPE_HUMAN_CONNECTION;
    }

    /**
     * Check if step requires reflection.
     */
    public function requiresReflection(): bool
    {
        return in_array($this->step_type, [self::TYPE_REFLECTION, self::TYPE_CHECKPOINT]);
    }

    /**
     * Check if step is an assessment.
     */
    public function isAssessment(): bool
    {
        return $this->step_type === self::TYPE_ASSESSMENT;
    }

    /**
     * Move step up in order.
     */
    public function moveUp(): void
    {
        $previous = $this->previous_step;
        if ($previous) {
            $currentOrder = $this->sort_order;
            $this->update(['sort_order' => $previous->sort_order]);
            $previous->update(['sort_order' => $currentOrder]);
        }
    }

    /**
     * Move step down in order.
     */
    public function moveDown(): void
    {
        $next = $this->next_step;
        if ($next) {
            $currentOrder = $this->sort_order;
            $this->update(['sort_order' => $next->sort_order]);
            $next->update(['sort_order' => $currentOrder]);
        }
    }

    /**
     * Get content data value.
     */
    public function getContentValue(string $key, $default = null)
    {
        return $this->content_data[$key] ?? $default;
    }

    /**
     * Set content data value.
     */
    public function setContentValue(string $key, $value): void
    {
        $data = $this->content_data ?? [];
        $data[$key] = $value;
        $this->update(['content_data' => $data]);
    }

    // ============================================
    // VISIBILITY RULES
    // ============================================

    /**
     * Check if this step can be publicly visible.
     * A step can't be public if it's linked to an unapproved resource.
     */
    public function canBePublic(): bool
    {
        // If step has a linked resource, check if it's approved
        if ($this->resource_id && $this->resource) {
            return $this->resource->active;
        }

        // Steps without linked resources can be public
        return true;
    }

    /**
     * Check if this step uses an unapproved resource.
     */
    public function usesUnapprovedResource(): bool
    {
        if (! $this->resource_id) {
            return false;
        }

        $resource = $this->resource;

        return $resource && ! $resource->active;
    }

    /**
     * Get the visibility status for display.
     */
    public function getVisibilityStatusAttribute(): string
    {
        if ($this->usesUnapprovedResource()) {
            return 'private_resource';
        }

        // Inherit from course visibility
        if ($this->miniCourse) {
            return $this->miniCourse->visibility ?? 'private';
        }

        return 'private';
    }

    /**
     * Get a human-readable visibility message.
     */
    public function getVisibilityMessageAttribute(): ?string
    {
        if ($this->usesUnapprovedResource()) {
            return 'This step uses an unapproved resource and won\'t be visible publicly until the resource is approved.';
        }

        return null;
    }

    /**
     * Check if this step has any visibility restrictions.
     */
    public function hasVisibilityRestrictions(): bool
    {
        return $this->usesUnapprovedResource();
    }

    /**
     * Scope to steps that can be publicly visible.
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where(function ($q) {
            // Steps without linked resources are visible
            $q->whereNull('resource_id')
                // OR steps with approved resources
                ->orWhereHas('resource', fn ($r) => $r->where('active', true));
        });
    }

    /**
     * Scope to steps with unapproved resources.
     */
    public function scopeWithUnapprovedResources(Builder $query): Builder
    {
        return $query->whereHas('resource', fn ($r) => $r->where('active', false));
    }
}
