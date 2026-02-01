<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Objective;
use App\Models\StrategicPlan;
use App\Models\UserNotification;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckStrategyDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:check-strategy-deadlines';

    /**
     * The console command description.
     */
    protected $description = 'Check for strategy activities and objectives due soon';

    public function __construct(
        protected NotificationService $notificationService,
        protected NotificationDeliveryService $deliveryService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking strategy deadlines...');

        $this->checkActivityDeadlines();
        $this->checkObjectiveDeadlines();

        $this->info('Strategy deadline check complete.');

        return Command::SUCCESS;
    }

    /**
     * Check activities due within 7 days.
     */
    protected function checkActivityDeadlines(): void
    {
        $activitiesDueSoon = Activity::whereNotNull('end_date')
            ->where('end_date', '>', now())
            ->where('end_date', '<=', now()->addDays(7))
            ->whereNotIn('status', [Activity::STATUS_OFF_TRACK]) // Already at risk
            ->with(['objective.focusArea.strategicPlan'])
            ->get();

        $this->info("Found {$activitiesDueSoon->count()} activities due within 7 days");

        foreach ($activitiesDueSoon as $activity) {
            $plan = $activity->objective?->focusArea?->strategicPlan;

            if (!$plan || !$plan->created_by) {
                continue;
            }

            $daysRemaining = now()->diffInDays($activity->end_date, false);

            // Only notify at key intervals: 7 days, 3 days, 1 day
            if (!in_array($daysRemaining, [7, 3, 1])) {
                continue;
            }

            $this->notifyActivityDue($activity, $plan, $daysRemaining);
        }
    }

    /**
     * Check objectives due within 14 days.
     */
    protected function checkObjectiveDeadlines(): void
    {
        $objectivesDueSoon = Objective::whereNotNull('end_date')
            ->where('end_date', '>', now())
            ->where('end_date', '<=', now()->addDays(14))
            ->whereNotIn('status', [Objective::STATUS_OFF_TRACK])
            ->with(['focusArea.strategicPlan'])
            ->get();

        $this->info("Found {$objectivesDueSoon->count()} objectives due within 14 days");

        foreach ($objectivesDueSoon as $objective) {
            $plan = $objective->focusArea?->strategicPlan;

            if (!$plan || !$plan->created_by) {
                continue;
            }

            $daysRemaining = now()->diffInDays($objective->end_date, false);

            // Only notify at key intervals: 14 days, 7 days, 3 days
            if (!in_array($daysRemaining, [14, 7, 3])) {
                continue;
            }

            $this->notifyObjectiveDue($objective, $plan, $daysRemaining);
        }
    }

    /**
     * Notify plan owner of activity due soon.
     */
    protected function notifyActivityDue(Activity $activity, StrategicPlan $plan, int $daysRemaining): void
    {
        $notification = $this->notificationService->notify(
            $plan->created_by,
            UserNotification::CATEGORY_STRATEGY,
            'strategy_action_due',
            [
                'title' => "Activity Due: {$activity->title}",
                'body' => $this->buildActivityDueMessage($activity, $daysRemaining),
                'action_url' => route('strategies.show', $plan->id) . '#activity-' . $activity->id,
                'action_label' => 'View Activity',
                'icon' => 'calendar',
                'priority' => $daysRemaining <= 3
                    ? UserNotification::PRIORITY_HIGH
                    : UserNotification::PRIORITY_NORMAL,
                'notifiable_type' => Activity::class,
                'notifiable_id' => $activity->id,
                'metadata' => [
                    'plan_id' => $plan->id,
                    'plan_title' => $plan->title,
                    'days_remaining' => $daysRemaining,
                    'end_date' => $activity->end_date->toIso8601String(),
                ],
            ]
        );

        if ($notification) {
            $this->deliveryService->deliver($notification);
        }

        $this->line("  Notified: Activity {$activity->id} due in {$daysRemaining} days");
    }

    /**
     * Notify plan owner of objective due soon.
     */
    protected function notifyObjectiveDue(Objective $objective, StrategicPlan $plan, int $daysRemaining): void
    {
        $notification = $this->notificationService->notify(
            $plan->created_by,
            UserNotification::CATEGORY_STRATEGY,
            'strategy_objective_due',
            [
                'title' => "Objective Due: {$objective->title}",
                'body' => $this->buildObjectiveDueMessage($objective, $daysRemaining),
                'action_url' => route('strategies.show', $plan->id) . '#objective-' . $objective->id,
                'action_label' => 'View Objective',
                'icon' => 'flag',
                'priority' => $daysRemaining <= 7
                    ? UserNotification::PRIORITY_HIGH
                    : UserNotification::PRIORITY_NORMAL,
                'notifiable_type' => Objective::class,
                'notifiable_id' => $objective->id,
                'metadata' => [
                    'plan_id' => $plan->id,
                    'plan_title' => $plan->title,
                    'days_remaining' => $daysRemaining,
                    'end_date' => $objective->end_date->toIso8601String(),
                    'activity_count' => $objective->activities()->count(),
                ],
            ]
        );

        if ($notification) {
            $this->deliveryService->deliver($notification);
        }

        $this->line("  Notified: Objective {$objective->id} due in {$daysRemaining} days");
    }

    /**
     * Build message for activity due notification.
     */
    protected function buildActivityDueMessage(Activity $activity, int $daysRemaining): string
    {
        $timeText = $daysRemaining === 1 ? 'tomorrow' : "in {$daysRemaining} days";

        $message = "This activity is due {$timeText}.";

        if ($activity->status === Activity::STATUS_NOT_STARTED) {
            $message .= ' It has not been started yet.';
        }

        return $message;
    }

    /**
     * Build message for objective due notification.
     */
    protected function buildObjectiveDueMessage(Objective $objective, int $daysRemaining): string
    {
        $timeText = $daysRemaining <= 3 ? "in {$daysRemaining} days" : "in about {$daysRemaining} days";

        $message = "This objective is due {$timeText}.";

        $incompleteActivities = $objective->activities()
            ->whereIn('status', [Activity::STATUS_NOT_STARTED, Activity::STATUS_AT_RISK, Activity::STATUS_OFF_TRACK])
            ->count();

        if ($incompleteActivities > 0) {
            $message .= " {$incompleteActivities} activities still require attention.";
        }

        return $message;
    }
}
