<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\ProgressSummary;
use App\Models\StrategicPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PlanProgressService
{
    public function __construct(
        protected ClaudeService $claude
    ) {}

    /**
     * Generate AI progress summary for a plan.
     */
    public function generateProgressSummary(
        StrategicPlan $plan,
        string $periodType = ProgressSummary::PERIOD_WEEKLY
    ): ?ProgressSummary {
        $periodDates = $this->getPeriodDates($periodType);

        // Check if summary already exists for this period
        $existing = ProgressSummary::where('strategic_plan_id', $plan->id)
            ->where('period_type', $periodType)
            ->where('period_start', $periodDates['start'])
            ->first();

        if ($existing) {
            return $existing;
        }

        // Gather data for the period
        $data = $this->gatherPeriodData($plan, $periodDates['start'], $periodDates['end']);

        // Generate summary via Claude
        $prompt = $this->buildSummaryPrompt($plan, $data, $periodType);
        $response = $this->claude->sendMessage($prompt, $this->getSummarySystemPrompt());

        if (! $response['success']) {
            Log::error('Failed to generate progress summary', [
                'plan_id' => $plan->id,
                'error' => $response['error'] ?? 'Unknown',
            ]);

            return null;
        }

        // Parse and save summary
        return $this->parseSummaryResponse(
            $plan,
            $response['content'],
            $periodDates,
            $periodType,
            $data
        );
    }

    /**
     * Calculate overall plan progress.
     */
    public function calculatePlanProgress(StrategicPlan $plan): array
    {
        // For OKR-style plans, use goals
        if ($plan->isOkrStyle()) {
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

        // For traditional plans, use focus areas/objectives/activities
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
     * Get upcoming milestones for a plan.
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
     * Get overdue items for a plan.
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
     * Get period dates based on period type.
     */
    protected function getPeriodDates(string $periodType): array
    {
        return match ($periodType) {
            ProgressSummary::PERIOD_WEEKLY => [
                'start' => Carbon::now()->startOfWeek(),
                'end' => Carbon::now()->endOfWeek(),
            ],
            ProgressSummary::PERIOD_MONTHLY => [
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now()->endOfMonth(),
            ],
            ProgressSummary::PERIOD_QUARTERLY => [
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
     * Gather data for a specific period.
     */
    protected function gatherPeriodData(StrategicPlan $plan, Carbon $start, Carbon $end): array
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
     * Get system prompt for summary generation.
     */
    protected function getSummarySystemPrompt(): string
    {
        return <<<'PROMPT'
You are an educational plan progress analyst. Generate clear, actionable summaries of plan progress.

You will receive data about a strategic plan including:
- Recent progress updates
- Goal status and progress
- Milestone completions and upcoming deadlines

Generate a JSON response with the following structure:
{
    "summary": "2-3 sentence overview of progress this period",
    "highlights": ["achievement 1", "achievement 2", ...],
    "concerns": ["concern 1", "concern 2", ...],
    "recommendations": ["next step 1", "next step 2", ...]
}

Guidelines:
- Be specific and actionable
- Focus on measurable progress
- Identify risks early
- Suggest concrete next steps
- Keep language professional but accessible
- Limit each array to 3-5 items max
PROMPT;
    }

    /**
     * Build the prompt for summary generation.
     */
    protected function buildSummaryPrompt(StrategicPlan $plan, array $data, string $periodType): string
    {
        $periodLabel = match ($periodType) {
            ProgressSummary::PERIOD_WEEKLY => 'this week',
            ProgressSummary::PERIOD_MONTHLY => 'this month',
            ProgressSummary::PERIOD_QUARTERLY => 'this quarter',
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

    /**
     * Parse Claude's response and save the summary.
     */
    protected function parseSummaryResponse(
        StrategicPlan $plan,
        string $content,
        array $periodDates,
        string $periodType,
        array $data
    ): ?ProgressSummary {
        try {
            // Extract JSON from response
            $json = $content;
            if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                $json = $matches[1];
            } elseif (preg_match('/\{.*\}/s', $content, $matches)) {
                $json = $matches[0];
            }

            $parsed = json_decode($json, true);

            if (! $parsed || ! isset($parsed['summary'])) {
                Log::warning('Failed to parse summary response', ['content' => $content]);
                // Create a basic summary if parsing fails
                $parsed = [
                    'summary' => 'Progress summary could not be generated automatically.',
                    'highlights' => [],
                    'concerns' => [],
                    'recommendations' => ['Review plan progress manually.'],
                ];
            }

            return ProgressSummary::create([
                'strategic_plan_id' => $plan->id,
                'period_type' => $periodType,
                'period_start' => $periodDates['start'],
                'period_end' => $periodDates['end'],
                'summary' => $parsed['summary'],
                'highlights' => $parsed['highlights'] ?? [],
                'concerns' => $parsed['concerns'] ?? [],
                'recommendations' => $parsed['recommendations'] ?? [],
                'metrics_snapshot' => $data['progress'],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save progress summary', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
