<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\Goal;
use App\Models\StrategicPlan;
use Carbon\Carbon;

/**
 * PlanProgressDomainService
 *
 * Encapsulates progress calculation logic for strategic plans.
 * Handles both OKR-style and traditional activity-based progress calculations.
 */
class PlanProgressDomainService
{
    /**
     * Calculate overall plan progress.
     *
     * Determines plan progress based on plan type:
     * - OKR-style plans: averaged goal progress
     * - Traditional plans: activity completion rate
     *
     * @param  StrategicPlan  $plan  The strategic plan to calculate
     * @return array{progress: float, source: string, details: array}
     */
    public function calculatePlanProgress(StrategicPlan $plan): array
    {
        if ($plan->isOkrStyle()) {
            return $this->calculateOkrProgress($plan);
        }

        return $this->calculateActivityProgress($plan);
    }

    /**
     * Calculate progress for OKR-style plans.
     *
     * @param  StrategicPlan  $plan
     * @return array{progress: float, source: string, details: array}
     */
    protected function calculateOkrProgress(StrategicPlan $plan): array
    {
        $goals = $plan->goals()->with('keyResults')->get();

        $goalProgress = $goals->isEmpty() ? 0 : $goals->avg(fn ($g) => $g->calculateProgress());

        return [
            'progress' => round($goalProgress, 1),
            'source' => 'goals',
            'details' => [
                'total_goals' => $goals->count(),
                'completed' => $goals->where('status', Goal::STATUS_COMPLETED)->count(),
                'in_progress' => $goals->where('status', Goal::STATUS_IN_PROGRESS)->count(),
                'at_risk' => $goals->where('status', Goal::STATUS_AT_RISK)->count(),
                'not_started' => $goals->where('status', Goal::STATUS_NOT_STARTED)->count(),
            ],
        ];
    }

    /**
     * Calculate progress for traditional activity-based plans.
     *
     * @param  StrategicPlan  $plan
     * @return array{progress: float, source: string, details: array}
     */
    protected function calculateActivityProgress(StrategicPlan $plan): array
    {
        $focusAreas = $plan->focusAreas()->with('objectives.activities')->get();

        $totalActivities = 0;
        $completedActivities = 0;
        $statusCounts = [
            'on_track' => 0,
            'at_risk' => 0,
            'off_track' => 0,
            'not_started' => 0,
        ];

        foreach ($focusAreas as $fa) {
            foreach ($fa->objectives as $obj) {
                foreach ($obj->activities as $act) {
                    $totalActivities++;
                    $statusCounts[$act->status] = ($statusCounts[$act->status] ?? 0) + 1;
                    if (in_array($act->status, ['on_track', 'completed'])) {
                        $completedActivities++;
                    }
                }
            }
        }

        $progress = $totalActivities > 0 ? ($completedActivities / $totalActivities) * 100 : 0;

        return [
            'progress' => round($progress, 1),
            'source' => 'activities',
            'details' => [
                'total_activities' => $totalActivities,
                'completed' => $completedActivities,
                'status_breakdown' => $statusCounts,
            ],
        ];
    }

    /**
     * Get period dates based on period type.
     *
     * @param  string  $periodType  Period type constant (weekly, monthly, quarterly)
     * @return array{start: Carbon, end: Carbon}
     */
    public function getPeriodDates(string $periodType): array
    {
        return match ($periodType) {
            'weekly' => [
                'start' => Carbon::now()->startOfWeek(),
                'end' => Carbon::now()->endOfWeek(),
            ],
            'monthly' => [
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now()->endOfMonth(),
            ],
            'quarterly' => [
                'start' => Carbon::now()->firstOfQuarter(),
                'end' => Carbon::now()->lastOfQuarter(),
            ],
            default => [
                'start' => Carbon::now()->startOfWeek(),
                'end' => Carbon::now()->endOfWeek(),
            ],
        };
    }

    /**
     * Gather and aggregate period data for summary generation.
     *
     * Collects progress updates, goal statuses, and milestones for a period.
     *
     * @param  StrategicPlan  $plan
     * @param  Carbon  $start
     * @param  Carbon  $end
     * @return array{updates: \Illuminate\Support\Collection, goals: \Illuminate\Support\Collection, milestones: \Illuminate\Support\Collection, progress: array}
     */
    public function gatherPeriodData(StrategicPlan $plan, Carbon $start, Carbon $end): array
    {
        return [
            'updates' => $plan->progressUpdates()
                ->whereBetween('created_at', [$start, $end])
                ->with('creator', 'goal', 'keyResult', 'milestone')
                ->get()
                ->map(fn ($u) => [
                    'content' => $u->content,
                    'type' => $u->update_type,
                    'context' => $u->context_label,
                    'created_by' => $u->creator?->full_name ?? 'System',
                    'created_at' => $u->created_at->format('M j, Y'),
                ]),
            'goals' => $plan->allGoals()
                ->with('keyResults')
                ->get()
                ->map(fn ($g) => [
                    'title' => $g->title,
                    'status' => $g->status,
                    'progress' => $g->calculateProgress(),
                    'key_results_count' => $g->keyResults->count(),
                ]),
            'milestones' => $plan->milestones()
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('due_date', [$start, $end])
                        ->orWhereBetween('completed_at', [$start, $end]);
                })
                ->get()
                ->map(fn ($m) => [
                    'title' => $m->title,
                    'status' => $m->status,
                    'due_date' => $m->due_date->format('M j'),
                    'completed' => $m->status === 'completed',
                ]),
            'progress' => $this->calculatePlanProgress($plan),
        ];
    }

    /**
     * Extract overdue items from a plan.
     *
     * Identifies milestones and goals with due dates in the past.
     *
     * @param  StrategicPlan  $plan
     * @return array Array of overdue items sorted by days overdue
     */
    public function getOverdueItems(StrategicPlan $plan): array
    {
        $overdueMilestones = $plan->milestones()
            ->overdue()
            ->get()
            ->map(fn ($m) => [
                'type' => 'milestone',
                'id' => $m->id,
                'title' => $m->title,
                'due_date' => $m->due_date->format('M j, Y'),
                'days_overdue' => now()->diffInDays($m->due_date),
            ]);

        $overdueGoals = $plan->allGoals()
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotIn('status', [Goal::STATUS_COMPLETED])
            ->get()
            ->map(fn ($g) => [
                'type' => 'goal',
                'id' => $g->id,
                'title' => $g->title,
                'due_date' => $g->due_date->format('M j, Y'),
                'days_overdue' => now()->diffInDays($g->due_date),
            ]);

        return $overdueMilestones->merge($overdueGoals)->sortByDesc('days_overdue')->values()->toArray();
    }

    /**
     * Get upcoming milestones for a plan.
     *
     * @param  StrategicPlan  $plan
     * @param  int  $days  Days ahead to look
     * @return array Array of upcoming milestone summaries
     */
    public function getUpcomingMilestones(StrategicPlan $plan, int $days = 14): array
    {
        return $plan->milestones()
            ->upcoming($days)
            ->with('goal')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'title' => $m->title,
                'due_date' => $m->due_date->format('M j, Y'),
                'days_until' => $m->due_date->diffInDays(now()),
                'goal' => $m->goal?->title,
                'status' => $m->status,
            ])
            ->toArray();
    }

    /**
     * Build summary prompt for Claude.
     *
     * Constructs a human-readable prompt from period data for AI analysis.
     *
     * @param  StrategicPlan  $plan
     * @param  array  $data  Gathered period data
     * @param  string  $periodType  Period type (weekly, monthly, quarterly)
     * @return string Formatted prompt for AI
     */
    public function buildSummaryPrompt(StrategicPlan $plan, array $data, string $periodType): string
    {
        $periodLabel = match ($periodType) {
            'weekly' => 'this week',
            'monthly' => 'this month',
            'quarterly' => 'this quarter',
            default => 'this period',
        };

        $updatesText = $data['updates']->isEmpty()
            ? 'No updates recorded.'
            : $data['updates']->map(fn ($u) => "- {$u['content']} ({$u['context']} - {$u['created_by']})")->join("\n");

        $goalsText = $data['goals']->isEmpty()
            ? 'No goals defined.'
            : $data['goals']->map(fn ($g) => "- {$g['title']}: {$g['status']} ({$g['progress']}% complete)")->join("\n");

        $milestonesText = $data['milestones']->isEmpty()
            ? 'No milestones this period.'
            : $data['milestones']->map(fn ($m) => "- {$m['title']}: {$m['status']} (due {$m['due_date']})")->join("\n");

        return <<<PROMPT
Generate a progress summary for {$periodLabel} for the following plan:

Plan: {$plan->title}
Type: {$plan->plan_type}
Overall Progress: {$data['progress']['progress']}%

PROGRESS UPDATES:
{$updatesText}

GOALS STATUS:
{$goalsText}

MILESTONES:
{$milestonesText}

Please analyze this data and provide a JSON summary.
PROMPT;
    }
}
