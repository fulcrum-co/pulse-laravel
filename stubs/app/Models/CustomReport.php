<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomReport extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'custom_reports';

    protected $fillable = [
        'org_id',
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
     * Check if report is due for auto-send.
     */
    public function isDueForSend(): bool
    {
        if (!$this->auto_send || !$this->distribution_schedule) {
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
}
