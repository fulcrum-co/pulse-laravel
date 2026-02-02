<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageHelpHint extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_id',
        'page_context',
        'section',
        'selector',
        'title',
        'description',
        'position',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Available page contexts.
     */
    public const CONTEXTS = [
        'dashboard' => 'Dashboard',
        'reports' => 'Reports',
        'collect' => 'Data Collection',
        'distribute' => 'Distributions',
        'resources' => 'Resources',
        'contacts' => 'Contacts',
        'plans' => 'Plans',
        'surveys' => 'Surveys',
        'alerts' => 'Alerts',
    ];

    /**
     * URL mapping for each page context (used by Visual Editor).
     */
    public const CONTEXT_URLS = [
        'dashboard' => '/dashboard',
        'reports' => '/reports',
        'collect' => '/collect',
        'distribute' => '/distribute',
        'resources' => '/resources',
        'contacts' => '/contacts',
        'plans' => '/plans',
        'surveys' => '/surveys',
        'alerts' => '/alerts',
    ];

    /**
     * Available tooltip positions.
     */
    public const POSITIONS = [
        'top' => 'Above element',
        'bottom' => 'Below element',
        'left' => 'Left of element',
        'right' => 'Right of element',
    ];

    /**
     * Get the organization that owns this hint.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Scope to filter by organization (includes system-wide hints).
     */
    public function scopeForOrganization($query, ?int $orgId)
    {
        return $query->where(function ($q) use ($orgId) {
            $q->whereNull('org_id'); // System-wide hints
            if ($orgId) {
                $q->orWhere('org_id', $orgId);
            }
        });
    }

    /**
     * Scope to get active hints only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by page context.
     */
    public function scopeForPage($query, string $context)
    {
        return $query->where('page_context', $context);
    }

    /**
     * Get hints for a specific page, formatted for JavaScript.
     */
    public static function getHintsForPage(string $context, ?int $orgId = null): array
    {
        return static::forOrganization($orgId)
            ->active()
            ->forPage($context)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($hint) => [
                'section' => $hint->section,
                'selector' => $hint->selector,
                'title' => $hint->title,
                'description' => $hint->description,
                'position' => $hint->position,
            ])
            ->toArray();
    }

    /**
     * Get all hints grouped by page context.
     */
    public static function getAllHintsGrouped(?int $orgId = null): array
    {
        $hints = static::forOrganization($orgId)
            ->active()
            ->orderBy('page_context')
            ->orderBy('sort_order')
            ->get();

        $grouped = [];
        foreach ($hints as $hint) {
            if (! isset($grouped[$hint->page_context])) {
                $grouped[$hint->page_context] = [];
            }
            $grouped[$hint->page_context][] = [
                'section' => $hint->section,
                'selector' => $hint->selector,
                'title' => $hint->title,
                'description' => $hint->description,
                'position' => $hint->position,
            ];
        }

        return $grouped;
    }
}
