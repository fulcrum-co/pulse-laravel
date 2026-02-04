<?php

namespace App\Jobs;

use App\Models\Plan;
use App\Services\NotificationService;
use App\Services\SinchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class ProcessPlanOverdueNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(NotificationService $notificationService, SinchService $sinchService): void
    {
        $plans = Plan::query()
            ->whereNotNull('review_at')
            ->where('review_at', '<', now())
            ->where('status', 'active')
            ->get();

        foreach ($plans as $plan) {
            $plannable = $plan->plannable;
            if (! $plannable) {
                continue;
            }

            $user = $plannable instanceof \App\Models\User
                ? $plannable
                : $plannable->user;

            if (! $user) {
                continue;
            }

            $signedUrl = URL::temporarySignedRoute('plans.signed', now()->addHours(48), ['plan' => $plan->id]);

            $notificationService->notify(
                $user->id,
                'plan',
                'plan_overdue',
                [
                    'title' => 'Plan review overdue',
                    'body' => 'A plan review is overdue. Tap to view updates.',
                    'action_url' => $signedUrl,
                    'action_label' => 'View plan',
                    'notifiable_type' => Plan::class,
                    'notifiable_id' => $plan->id,
                ]
            );

            $phone = $user->phone ?? $user->mobile_phone ?? null;
            if ($phone) {
                try {
                    $sinchService->sendSms($phone, "A plan review is overdue. View: {$signedUrl}");
                } catch (\Throwable $e) {
                    Log::warning('ProcessPlanOverdueNotifications: SMS failed', [
                        'plan_id' => $plan->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
