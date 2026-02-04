<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Notification scheduled tasks - 15-minute cadence for time-sensitive alerts
Schedule::command('notifications:unsnooze')
    ->everyFifteenMinutes()
    ->description('Unsnooze notifications whose snooze period has expired');

Schedule::command('notifications:process-expired')
    ->everyFifteenMinutes()
    ->description('Dismiss notifications that have passed their expiration date');

Schedule::command('notifications:check-survey-deadlines')
    ->everyFifteenMinutes()
    ->description('Check for surveys closing within 4 hours');

Schedule::command('notifications:check-strategy-deadlines')
    ->everyFifteenMinutes()
    ->description('Check for plan activities due within 4 hours');

// Daily maintenance tasks
Schedule::command('notifications:cleanup')
    ->dailyAt('03:00')
    ->description('Clean up old resolved/dismissed notifications (90+ days)');

// Notification digests - runs every 15 minutes to match user-configured times
Schedule::command('notifications:send-daily-digest')
    ->everyFifteenMinutes()
    ->description('Send daily digest emails to users in current time window');

Schedule::command('notifications:send-weekly-digest')
    ->everyFifteenMinutes()
    ->description('Send weekly digest emails on user-configured days');

// Collection reminder processing
Schedule::job(new \App\Jobs\ProcessCollectionReminders)
    ->everyFiveMinutes()
    ->description('Process pending collection reminders and send notifications');

// Plan overdue alerts (15-minute cadence)
Schedule::job(new \App\Jobs\ProcessPlanOverdueNotifications)
    ->everyFifteenMinutes()
    ->description('Send plan overdue notifications');

// Collection transcription processing (15-minute cadence)
Schedule::job(new \App\Jobs\ProcessPendingTranscriptions)
    ->everyFifteenMinutes()
    ->description('Queue pending collection dictation transcriptions');
