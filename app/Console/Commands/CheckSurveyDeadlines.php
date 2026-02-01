<?php

namespace App\Console\Commands;

use App\Models\Survey;
use App\Models\SurveyDelivery;
use App\Models\UserNotification;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckSurveyDeadlines extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:check-survey-deadlines';

    /**
     * The console command description.
     */
    protected $description = 'Check for surveys closing soon and notify assigned users';

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
        $this->info('Checking survey deadlines...');

        // Find active surveys closing within 4 hours
        $closingSoon = Survey::active()
            ->whereNotNull('end_date')
            ->where('end_date', '>', now())
            ->where('end_date', '<=', now()->addHours(4))
            ->get();

        $this->info("Found {$closingSoon->count()} surveys closing within 4 hours");

        foreach ($closingSoon as $survey) {
            $this->processSurveyDeadline($survey);
        }

        $this->info('Survey deadline check complete.');

        return Command::SUCCESS;
    }

    /**
     * Process deadline notifications for a single survey.
     */
    protected function processSurveyDeadline(Survey $survey): void
    {
        // Find users with pending (incomplete) deliveries
        $pendingDeliveries = SurveyDelivery::where('survey_id', $survey->id)
            ->where('status', 'pending')
            ->whereNotNull('user_id')
            ->get();

        if ($pendingDeliveries->isEmpty()) {
            return;
        }

        $hoursRemaining = now()->diffInHours($survey->end_date, false);

        $userIds = $pendingDeliveries->pluck('user_id')->unique()->toArray();

        // Deduplicate: check if we already notified within last 4 hours
        $recentlyNotified = UserNotification::query()
            ->whereIn('user_id', $userIds)
            ->where('type', 'survey_closing_soon')
            ->where('notifiable_type', Survey::class)
            ->where('notifiable_id', $survey->id)
            ->where('created_at', '>=', now()->subHours(4))
            ->pluck('user_id')
            ->toArray();

        $usersToNotify = array_diff($userIds, $recentlyNotified);

        if (empty($usersToNotify)) {
            $this->line("  Survey {$survey->id}: All users already notified recently");

            return;
        }

        $this->line("  Survey {$survey->id}: Notifying ".count($usersToNotify).' users (skipped '.count($recentlyNotified).' already notified)');

        // Create urgent notifications
        $count = $this->notificationService->notifyMany(
            $usersToNotify,
            UserNotification::CATEGORY_SURVEY,
            'survey_closing_soon',
            [
                'title' => "Survey closing soon: {$survey->title}",
                'body' => $this->buildClosingMessage($hoursRemaining),
                'action_url' => route('surveys.take', $survey->id),
                'action_label' => 'Complete Survey',
                'icon' => 'clock',
                'priority' => UserNotification::PRIORITY_HIGH,
                'notifiable_type' => Survey::class,
                'notifiable_id' => $survey->id,
                'expires_at' => $survey->end_date,
                'metadata' => [
                    'hours_remaining' => $hoursRemaining,
                    'end_date' => $survey->end_date->toIso8601String(),
                ],
            ]
        );

        // Dispatch multi-channel delivery for urgent notifications
        if ($count > 0) {
            $notifications = UserNotification::where('type', 'survey_closing_soon')
                ->where('notifiable_type', Survey::class)
                ->where('notifiable_id', $survey->id)
                ->where('created_at', '>=', now()->subMinute())
                ->get();

            $this->deliveryService->deliverMany($notifications);
        }

        Log::info('CheckSurveyDeadlines: Notified users of closing survey', [
            'survey_id' => $survey->id,
            'notifications_created' => $count,
            'skipped_already_notified' => count($recentlyNotified),
            'hours_remaining' => $hoursRemaining,
        ]);
    }

    /**
     * Build closing message based on time remaining.
     */
    protected function buildClosingMessage(int $hoursRemaining): string
    {
        if ($hoursRemaining <= 1) {
            return 'This survey closes in less than an hour! Please complete it now.';
        } elseif ($hoursRemaining <= 2) {
            return "This survey closes in about {$hoursRemaining} hours. Please complete it soon.";
        } else {
            return 'This survey closes within 4 hours. Please complete it before then.';
        }
    }
}
