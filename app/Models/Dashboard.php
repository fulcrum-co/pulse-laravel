<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dashboard extends Model
{
    protected $fillable = [
        'org_id',
        'user_id',
        'name',
        'description',
        'is_default',
        'is_shared',
        'layout',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_shared' => 'boolean',
        'layout' => 'array',
    ];

    /**
     * Widget type constants
     */
    public const WIDGET_METRIC_CARD = 'metric_card';

    public const WIDGET_BAR_CHART = 'bar_chart';

    public const WIDGET_LINE_CHART = 'line_chart';

    public const WIDGET_STUDENT_LIST = 'learner_list';

    public const WIDGET_SURVEY_SUMMARY = 'survey_summary';

    public const WIDGET_ALERT_FEED = 'alert_feed';

    public const WIDGET_NOTIFICATION_FEED = 'notification_feed';

    /**
     * Get available widget types with labels.
     */
    public static function getWidgetTypes(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            self::WIDGET_METRIC_CARD => [
                'label' => $terminology->get('widget_metric_card_label'),
                'description' => $terminology->get('widget_metric_card_description'),
                'icon' => 'chart-bar',
            ],
            self::WIDGET_BAR_CHART => [
                'label' => $terminology->get('widget_bar_chart_label'),
                'description' => $terminology->get('widget_bar_chart_description'),
                'icon' => 'chart-bar-square',
            ],
            self::WIDGET_LINE_CHART => [
                'label' => $terminology->get('widget_line_chart_label'),
                'description' => $terminology->get('widget_line_chart_description'),
                'icon' => 'arrow-trending-up',
            ],
            self::WIDGET_STUDENT_LIST => [
                'label' => $terminology->get('widget_participant_list_label'),
                'description' => $terminology->get('widget_participant_list_description'),
                'icon' => 'users',
            ],
            self::WIDGET_SURVEY_SUMMARY => [
                'label' => $terminology->get('widget_survey_summary_label'),
                'description' => $terminology->get('widget_survey_summary_description'),
                'icon' => 'clipboard-document-list',
            ],
            self::WIDGET_ALERT_FEED => [
                'label' => $terminology->get('widget_alert_feed_label'),
                'description' => $terminology->get('widget_alert_feed_description'),
                'icon' => 'bell-alert',
            ],
            self::WIDGET_NOTIFICATION_FEED => [
                'label' => $terminology->get('widget_notification_feed_label'),
                'description' => $terminology->get('widget_notification_feed_description'),
                'icon' => 'bell',
            ],
        ];
    }

    /**
     * Get the organization that owns this dashboard.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who owns this dashboard.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get widgets for this dashboard.
     */
    public function widgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class)->orderBy('order');
    }

    /**
     * Scope to dashboards accessible by a user.
     */
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere(function ($q2) use ($user) {
                    $q2->where('org_id', $user->org_id)
                        ->where('is_shared', true);
                });
        });
    }

    /**
     * Scope to user's own dashboards.
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope to shared dashboards in an org.
     */
    public function scopeSharedInOrg(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId)->where('is_shared', true);
    }

    /**
     * Get the default dashboard for a user.
     */
    public static function getDefaultForUser(User $user): ?self
    {
        // First check user's own default
        $dashboard = self::where('user_id', $user->id)
            ->where('is_default', true)
            ->first();

        if ($dashboard) {
            return $dashboard;
        }

        // Fall back to any shared default in org
        return self::where('org_id', $user->org_id)
            ->where('is_shared', true)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Set this dashboard as default for the user.
     */
    public function setAsDefault(): void
    {
        // Unset other defaults for this user
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }

    /**
     * Add a widget to this dashboard.
     */
    public function addWidget(string $type, string $title, array $config = [], ?array $position = null): DashboardWidget
    {
        $maxOrder = $this->widgets()->max('order') ?? -1;

        return $this->widgets()->create([
            'widget_type' => $type,
            'title' => $title,
            'config' => $config,
            'position' => $position ?? ['x' => 0, 'y' => 0, 'w' => 4, 'h' => 2],
            'order' => $maxOrder + 1,
        ]);
    }

    /**
     * Create a default dashboard with common widgets.
     */
    public static function createDefault(User $user, string $name = 'My Dashboard'): self
    {
        $dashboard = self::create([
            'org_id' => $user->org_id,
            'user_id' => $user->id,
            'name' => $name,
            'is_default' => true,
        ]);

        // Row 1: 4 metric cards across
        $dashboard->addWidget(
            self::WIDGET_METRIC_CARD,
            'Total Participants',
            ['data_source' => 'learners_total', 'color' => 'blue'],
            ['x' => 0, 'y' => 0, 'w' => 3, 'h' => 2]
        );

        $dashboard->addWidget(
            self::WIDGET_METRIC_CARD,
            'At-Risk Participants',
            ['data_source' => 'learners_at_risk', 'color' => 'red'],
            ['x' => 3, 'y' => 0, 'w' => 3, 'h' => 2]
        );

        $dashboard->addWidget(
            self::WIDGET_METRIC_CARD,
            'Active Surveys',
            ['data_source' => 'surveys_active', 'color' => 'green'],
            ['x' => 6, 'y' => 0, 'w' => 3, 'h' => 2]
        );

        $dashboard->addWidget(
            self::WIDGET_METRIC_CARD,
            'Need Attention',
            ['data_source' => 'learners_need_attention', 'color' => 'orange'],
            ['x' => 9, 'y' => 0, 'w' => 3, 'h' => 2]
        );

        // Row 2: Bar chart + Notification feed
        $dashboard->addWidget(
            self::WIDGET_BAR_CHART,
            'Survey Responses',
            ['data_source' => 'survey_responses_weekly', 'compare' => true],
            ['x' => 0, 'y' => 2, 'w' => 6, 'h' => 4]
        );

        $dashboard->addWidget(
            self::WIDGET_NOTIFICATION_FEED,
            'Notifications',
            ['limit' => 10],
            ['x' => 6, 'y' => 2, 'w' => 6, 'h' => 4]
        );

        return $dashboard;
    }
}
