<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomReport extends Model
{
    use SoftDeletes;

    protected $table = 'custom_reports';

    // Report statuses
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_ARCHIVED = 'archived';

    // Report types
    public const TYPE_STUDENT_PROGRESS = 'student_progress';

    public const TYPE_COHORT_SUMMARY = 'cohort_summary';

    public const TYPE_SCHOOL_DASHBOARD = 'school_dashboard';

    public const TYPE_CUSTOM = 'custom';

    protected $fillable = [
        'org_id',
        'source_report_id',
        'source_org_id',
        'team_id',
        'created_by',
        'report_name',
        'report_description',
        'report_type',
        'report_variables',
        'report_operations',
        'report_period',
        'generate_llm_narrative',
        'llm_narrative_prompt',
        'llm_narrative_last_generated',
        'assigned_to',
        'distribution_schedule',
        'auto_send',
        'report_layout',
        'anonymous_user_included',
        // New fields for report builder
        'page_settings',
        'status',
        'thumbnail_path',
        'version',
        'is_live',
        'snapshot_data',
        'public_token',
        'branding',
        'template_id',
        'filters',
        'last_edited_by',
    ];

    protected $casts = [
        'report_variables' => 'array',
        'report_operations' => 'array',
        'report_period' => 'array',
        'assigned_to' => 'array',
        'distribution_schedule' => 'array',
        'report_layout' => 'array',
        'generate_llm_narrative' => 'boolean',
        'auto_send' => 'boolean',
        'anonymous_user_included' => 'boolean',
        // New casts
        'page_settings' => 'array',
        'snapshot_data' => 'array',
        'branding' => 'array',
        'filters' => 'array',
        'is_live' => 'boolean',
        'version' => 'integer',
        'llm_narrative_last_generated' => 'datetime',
    ];

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the source report this was pushed from.
     */
    public function sourceReport(): BelongsTo
    {
        return $this->belongsTo(CustomReport::class, 'source_report_id');
    }

    /**
     * Get the source organization this was pushed from.
     */
    public function sourceOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'source_org_id');
    }

    /**
     * Get all reports pushed from this report.
     */
    public function pushedReports(): HasMany
    {
        return $this->hasMany(CustomReport::class, 'source_report_id');
    }

    /**
     * Push this report to another organization.
     * Creates a copy as draft for the target org to review and customize.
     */
    public function pushToOrganization(Organization $targetOrg, ?int $pushedBy = null): self
    {
        $newReport = $this->replicate([
            'org_id',
            'source_report_id',
            'source_org_id',
            'created_by',
            'status',
            'public_token',
            'version',
        ]);

        $newReport->org_id = $targetOrg->id;
        $newReport->source_report_id = $this->id;
        $newReport->source_org_id = $this->org_id;
        $newReport->created_by = $pushedBy;
        $newReport->status = self::STATUS_DRAFT;
        $newReport->public_token = null;
        $newReport->version = 1;
        $newReport->report_name = $this->report_name.' (from '.$this->organization->org_name.')';
        $newReport->save();

        return $newReport;
    }

    /**
     * Check if this report was pushed from another organization.
     */
    public function wasPushed(): bool
    {
        return $this->source_report_id !== null;
    }

    /**
     * Check if report is due for auto-send.
     */
    public function isDueForSend(): bool
    {
        if (! $this->auto_send || ! $this->distribution_schedule) {
            return false;
        }

        $schedule = $this->distribution_schedule;
        $now = now();

        switch ($schedule['frequency'] ?? null) {
            case 'daily':
                return true;

            case 'weekly':
                return $now->dayOfWeek === ($schedule['day_of_week'] ?? 1);

            case 'monthly':
                return $now->day === ($schedule['day_of_month'] ?? 1);

            case 'quarterly':
                return $now->day === ($schedule['day_of_month'] ?? 1)
                    && in_array($now->month, [1, 4, 7, 10]);

            default:
                return false;
        }
    }

    /**
     * Scope to filter by report type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    /**
     * Scope to filter auto-send reports.
     */
    public function scopeAutoSend($query)
    {
        return $query->where('auto_send', true);
    }

    /**
     * Scope to filter reports with LLM narrative.
     */
    public function scopeWithNarrative($query)
    {
        return $query->where('generate_llm_narrative', true);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter published reports.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope to filter draft reports.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Generate a unique public token for shareable links.
     */
    public function generatePublicToken(): string
    {
        $this->public_token = bin2hex(random_bytes(16));
        $this->save();

        return $this->public_token;
    }

    /**
     * Get the public URL for this report.
     */
    public function getPublicUrl(): ?string
    {
        if (! $this->public_token) {
            return null;
        }

        return route('reports.public', ['token' => $this->public_token]);
    }

    /**
     * Get embed code for this report.
     */
    public function getEmbedCode(): ?string
    {
        $url = $this->getPublicUrl();
        if (! $url) {
            return null;
        }

        return sprintf(
            '<iframe src="%s" width="100%%" height="600" frameborder="0" allowfullscreen></iframe>',
            $url
        );
    }

    /**
     * Get effective branding (report-specific or fall back to org).
     */
    public function getEffectiveBranding(): array
    {
        if ($this->branding) {
            return $this->branding;
        }

        // Fall back to organization branding
        $org = $this->organization;
        if ($org && isset($org->settings['branding'])) {
            return $org->settings['branding'];
        }

        // Default branding
        return [
            'logo_path' => null,
            'primary_color' => '#3B82F6',
            'secondary_color' => '#1E40AF',
            'font_family' => 'Inter, sans-serif',
        ];
    }

    /**
     * Get default page settings.
     */
    public function getPageSettings(): array
    {
        return array_merge([
            'size' => 'letter',
            'orientation' => 'portrait',
            'margins' => [
                'top' => 40,
                'right' => 40,
                'bottom' => 40,
                'left' => 40,
            ],
        ], $this->page_settings ?? []);
    }

    /**
     * Check if report is published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if report is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT || ! $this->status;
    }

    /**
     * Publish the report.
     */
    public function publish(): void
    {
        if (! $this->public_token) {
            $this->generatePublicToken();
        }

        $this->status = self::STATUS_PUBLISHED;
        $this->save();
    }

    /**
     * Unpublish (make draft) the report.
     */
    public function unpublish(): void
    {
        $this->status = self::STATUS_DRAFT;
        $this->save();
    }

    /**
     * Increment version number.
     */
    public function incrementVersion(): void
    {
        $this->version = ($this->version ?? 0) + 1;
        $this->save();
    }

    /**
     * Duplicate this report.
     *
     * Creates a complete deep-copy of the report including all JSON fields.
     * Resets version, clears published state, and clears snapshot data.
     */
    public function duplicate(?int $userId = null): self
    {
        $newReport = $this->replicate();

        // Update name to indicate copy
        $newReport->report_name = $this->report_name.' (Copy)';

        // Reset to draft state
        $newReport->status = self::STATUS_DRAFT;
        $newReport->is_live = false;

        // Clear published/public fields
        $newReport->public_token = null;
        $newReport->snapshot_data = null;
        $newReport->thumbnail_path = null;

        // Reset version
        $newReport->version = 1;

        // Set ownership
        $userId = $userId ?? auth()->id() ?? $this->created_by;
        $newReport->created_by = $userId;
        $newReport->last_edited_by = $userId;

        // Clear source tracking (this is a new independent report)
        $newReport->source_report_id = null;
        $newReport->source_org_id = null;

        // Ensure JSON fields are deep-copied (replicate should handle this, but explicit for safety)
        $newReport->report_layout = $this->report_layout ? json_decode(json_encode($this->report_layout), true) : null;
        $newReport->page_settings = $this->page_settings ? json_decode(json_encode($this->page_settings), true) : null;
        $newReport->branding = $this->branding ? json_decode(json_encode($this->branding), true) : null;
        $newReport->filters = $this->filters ? json_decode(json_encode($this->filters), true) : null;

        $newReport->save();

        return $newReport;
    }
}
