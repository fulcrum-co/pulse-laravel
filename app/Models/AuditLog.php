<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'org_id',
        'user_id',
        'auditable_type',
        'auditable_id',
        'contact_type',
        'contact_id',
        'action',
        'action_category',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
        'session_id',
        'request_method',
        'request_url',
        'involves_pii',
        'involves_education_records',
        'legal_basis',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'involves_pii' => 'boolean',
        'involves_education_records' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Action types
    public const ACTION_VIEW = 'view';

    public const ACTION_CREATE = 'create';

    public const ACTION_UPDATE = 'update';

    public const ACTION_DELETE = 'delete';

    public const ACTION_EXPORT = 'export';

    public const ACTION_SHARE = 'share';

    public const ACTION_PRINT = 'print';

    // Action categories
    public const CATEGORY_DATA_ACCESS = 'data_access';

    public const CATEGORY_DATA_MODIFICATION = 'data_modification';

    public const CATEGORY_REPORT_GENERATION = 'report_generation';

    public const CATEGORY_SHARE = 'share';

    // Legal basis options (FERPA)
    public const LEGAL_BASIS_EDUCATIONAL_INTEREST = 'legitimate_educational_interest';

    public const LEGAL_BASIS_DIRECTORY_INFO = 'directory_info';

    public const LEGAL_BASIS_CONSENT = 'consent';

    public const LEGAL_BASIS_HEALTH_SAFETY = 'health_safety_emergency';

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the auditable model.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Log an action.
     */
    public static function log(
        string $action,
        Model $auditable,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Model $contact = null
    ): self {
        $request = request();
        $user = auth()->user();

        return self::create([
            'org_id' => $user?->org_id,
            'user_id' => $user?->id,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->id,
            'contact_type' => $contact ? get_class($contact) : null,
            'contact_id' => $contact?->id,
            'action' => $action,
            'action_category' => self::categorizeAction($action),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'session_id' => session()->getId(),
            'request_method' => $request?->method(),
            'request_url' => $request?->fullUrl(),
            'involves_pii' => self::checkPii($auditable),
            'involves_education_records' => $contact instanceof Student,
            'legal_basis' => self::LEGAL_BASIS_EDUCATIONAL_INTEREST,
            'created_at' => now(),
        ]);
    }

    /**
     * Categorize action into action category.
     */
    private static function categorizeAction(string $action): string
    {
        return match ($action) {
            self::ACTION_VIEW => self::CATEGORY_DATA_ACCESS,
            self::ACTION_EXPORT, self::ACTION_PRINT => self::CATEGORY_REPORT_GENERATION,
            self::ACTION_SHARE => self::CATEGORY_SHARE,
            default => self::CATEGORY_DATA_MODIFICATION,
        };
    }

    /**
     * Check if model contains PII.
     */
    private static function checkPii(Model $model): bool
    {
        $piiModels = [
            Student::class,
            User::class,
            ContactNote::class,
            ContactMetric::class,
        ];

        return in_array(get_class($model), $piiModels);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by action.
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter PII access logs.
     */
    public function scopePiiAccess($query)
    {
        return $query->where('involves_pii', true);
    }

    /**
     * Scope to filter education record access.
     */
    public function scopeEducationRecords($query)
    {
        return $query->where('involves_education_records', true);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeInDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope to filter by contact.
     */
    public function scopeForContact($query, string $type, int $id)
    {
        return $query->where('contact_type', $type)->where('contact_id', $id);
    }
}
