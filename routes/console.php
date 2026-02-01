<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Notification scheduled tasks
Schedule::command('notifications:unsnooze')
    ->everyFifteenMinutes()
    ->description('Unsnooze notifications whose snooze period has expired');

Schedule::command('notifications:check-survey-deadlines')
    ->dailyAt('07:00')
    ->description('Check for surveys closing soon and notify assigned users');

Schedule::command('notifications:check-strategy-deadlines')
    ->dailyAt('07:00')
    ->description('Check for strategy activities and objectives due soon');
