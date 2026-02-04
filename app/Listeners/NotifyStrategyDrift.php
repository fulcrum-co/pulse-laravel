<?php

namespace App\Listeners;

use App\Events\StrategyDriftDetected;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class NotifyStrategyDrift implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(StrategyDriftDetected $event): void
    {
        $note = $event->note;
        $score = $event->score;

        Log::info('StrategyDriftDetected: Processing notification', [
            'note_id' => $note->id,
            'org_id' => $note->org_id,
            'alignment_score' => $score->alignment_score,
            'alignment_level' => $score->alignment_level,
        ]);

        // Find org admins and managers to notify
        $recipients = User::where('org_id', $note->org_id)
            ->where('active', true)
            ->where(function ($q) {
                $q->where('primary_role', 'admin')
                    ->orWhere('primary_role', 'manager')
                    ->orWhere('primary_role', 'director');
            })
            ->get();

        if ($recipients->isEmpty()) {
            Log::info('StrategyDriftDetected: No recipients found for org', [
                'org_id' => $note->org_id,
            ]);

            return;
        }

        // Send notification (using database/email notification)
        Notification::send($recipients, new \App\Notifications\DriftAlertNotification($note, $score));

        Log::info('StrategyDriftDetected: Notifications sent', [
            'note_id' => $note->id,
            'recipient_count' => $recipients->count(),
        ]);
    }
}
