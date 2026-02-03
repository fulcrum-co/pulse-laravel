<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProgressSummary;
use App\Models\StrategicPlan;
use App\Services\Domain\PlanProgressDomainService;
use Illuminate\Support\Facades\Log;

class PlanProgressService
{
    public function __construct(
        protected ClaudeService $claude,
        protected PlanProgressDomainService $domainService
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
        return $this->domainService->calculatePlanProgress($plan);
    }

    /**
     * Get upcoming milestones for a plan.
     */
    public function getUpcomingMilestones(StrategicPlan $plan, int $days = 14): array
    {
        return $this->domainService->getUpcomingMilestones($plan, $days);
    }

    /**
     * Get overdue items for a plan.
     */
    public function getOverdueItems(StrategicPlan $plan): array
    {
        return $this->domainService->getOverdueItems($plan);
    }

    /**
     * Get period dates based on period type.
     */
    protected function getPeriodDates(string $periodType): array
    {
        return $this->domainService->getPeriodDates($periodType);
    }

    /**
     * Gather data for a specific period.
     */
    protected function gatherPeriodData(StrategicPlan $plan, \Carbon\Carbon $start, \Carbon\Carbon $end): array
    {
        return $this->domainService->gatherPeriodData($plan, $start, $end);
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
        return $this->domainService->buildSummaryPrompt($plan, $data, $periodType);
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
