<?php

namespace App\Console\Commands;

use App\Models\Activity;
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
    protected $description = 'Check for plan activities due within 4 hours';

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
        $this->info('Checking plan activity deadlines...');

        $this->checkActivityDeadlines();

        $this->info('Plan deadline check complete.');

        return Command::SUCCESS;
    }

    /**
     * Check activities due within 4 hours.
     */
    protected function checkActivityDeadlines(): void
    {
        $fourHoursFromNow = now()->addHours(4);

        $activitiesDueSoon = Activity::whereNotNull('end_date')
            ->where('end_date', '>', now())
            ->where('end_date', '<=', $fourHoursFromNow)
            ->whereIn('status', [Activity::STATUS_NOT_STARTED, Activity::STATUS_AT_RISK])
            ->with(['objective.focusArea.strategicPlan.collaborators', 'assignee'])
            ->get();

        $this->info("Found {$activitiesDueSoon->count()} activities due within 4 hours");

        foreach ($activitiesDueSoon as $activity) {
            $this->processActivityDeadline($activity);
        }
    }

    /**
     * Process a single activity deadline.
     */
    protected function processActivityDeadline(Activity $activity): void
    {
        $plan = $activity->objective?->focusArea?->strategicPlan;

        if (! $plan) {
            return;
        }

        // Get users to notify: activity assignee + plan owner + plan collaborators
        $userIds = collect([$activity->assigned_to, $plan->created_by])
            ->merge($plan->collaborators?->pluck('user_id') ?? [])
            ->unique()
            ->filter()
            ->toArray();

        if (empty($userIds)) {
            return;
        }

        // Deduplicate: check if we already notified within last 4 hours
        $recentlyNotified = UserNotification::query()
            ->whereIn('user_id', $userIds)
            ->where('type', 'activity_due_soon')
            ->where('notifiable_type', Activity::class)
            ->where('notifiable_id', $activity->id)
            ->where('created_at', '>=', now()->subHours(4))
            ->pluck('user_id')
            ->toArray();

        $usersToNotify = array_diff($userIds, $recentlyNotified);

        if (empty($usersToNotify)) {
            $this->line("  Activity {$activity->id}: All users already notified recently");

            return;
        }

        $hoursRemaining = now()->diffInHours($activity->end_date, false);

        $this->line("  Activity {$activity->id}: Notifying ".count($usersToNotify).' users (skipped '.count($recentlyNotified).' already notified)');

        // Create high priority notifications
        $count = $this->notificationService->notifyMany(
            $usersToNotify,
            UserNotification::CATEGORY_STRATEGY,
            'activity_due_soon',
            [
                'title' => "Activity due soon: {$activity->title}",
                'body' => $this->buildActivityDueMessage($activity, $hoursRemaining),
                'action_url' => route('strategies.show', $plan->id).'#activity-'.$activity->id,
                'action_label' => 'View Activity',
                'icon' => 'clock',
                'priority' => UserNotification::PRIORITY_HIGH,
                'notifiable_type' => Activity::class,
                'notifiable_id' => $activity->id,
                'metadata' => [
                    'plan_id' => $plan->id,
                    'plan_title' => $plan->title,
                    'hours_remaining' => $hoursRemaining,
                    'end_date' => $activity->end_date->toIso8601String(),
                ],
            ]
        );

        // Dispatch multi-channel delivery
        if ($count > 0) {
            $notifications = UserNotification::where('type', 'activity_due_soon')
                ->where('notifiable_type', Activity::class)
                ->where('notifiable_id', $activity->id)
                ->where('created_at', '>=', now()->subMinute())
                ->get();

            $this->deliveryService->deliverMany($notifications);
        }

        Log::info('CheckStrategyDeadlines: Notified users of activity due soon', [
            'activity_id' => $activity->id,
            'plan_id' => $plan->id,
            'notifications_created' => $count,
            'skipped_already_notified' => count($recentlyNotified),
            'hours_remaining' => $hoursRemaining,
        ]);
    }

    /**
     * Build message for activity due notification.
     */
    protected function buildActivityDueMessage(Activity $activity, int $hoursRemaining): string
    {
        if ($hoursRemaining <= 1) {
            $message = 'This activity is due in less than an hour!';
        } else {
            $message = "This activity is due in about {$hoursRemaining} hours.";
        }

        if ($activity->status === Activity::STATUS_NOT_STARTED) {
            $message .= ' It has not been started yet.';
        } elseif ($activity->status === Activity::STATUS_AT_RISK) {
            $message .= ' It is currently at risk.';
        }

        return $message;
    }
}
