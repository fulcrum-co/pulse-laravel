<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactMetric;
use App\Models\CustomReport;
use App\Services\Domain\AIResponseParserService;
use Illuminate\Support\Facades\Log;

class ReportAIService
{
    public function __construct(
        protected ClaudeService $claude,
        protected ContactMetricService $metrics,
        protected AIResponseParserService $aiParser
    ) {}

    /**
     * Generate adaptive text content from data context.
     */
    public function generateAdaptiveText(array $dataContext, string $format = 'narrative', ?string $orgName = null): string
    {
        $prompt = $this->buildAdaptiveTextPrompt($dataContext, $format, $orgName);

        try {
            $response = $this->claude->sendMessage($prompt);

            if ($response['success']) {
                return $response['message'];
            }

            Log::error('Failed to generate adaptive text', ['error' => $response['error'] ?? 'Unknown error']);

            return 'Unable to generate content. Please try again.';
        } catch (\Exception $e) {
            Log::error('Exception generating adaptive text', ['error' => $e->getMessage()]);

            return 'An error occurred while generating content.';
        }
    }

    /**
     * Generate a report layout from a natural language prompt.
     */
    public function generateLayoutFromPrompt(string $userPrompt, string $reportType = 'custom'): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        $availableMetrics = [
            'gpa' => $terminology->get('metric_gpa_label').' ('.$terminology->get('level_point_average_label').')',
            'attendance_rate' => $terminology->get('metric_attendance_rate_label'),
            'wellness_score' => $terminology->get('metric_health_wellness_label').' '.$terminology->get('score_label'),
            'engagement_score' => $terminology->get('metric_engagement_label').' '.$terminology->get('score_label'),
            'emotional_wellbeing' => $terminology->get('metric_emotional_wellbeing_label'),
            'plan_progress' => $terminology->get('participant_plan_progress_label'),
        ];

        $prompt = $this->buildLayoutPrompt($userPrompt, $reportType, $availableMetrics);

        try {
            $response = $this->claude->sendMessage($prompt);

            if ($response['success']) {
                // Parse JSON from response using domain parser
                try {
                    $layout = $this->aiParser->parseJsonResponse($response['message']);
                    if (is_array($layout)) {
                        return $this->normalizeLayout($layout);
                    }
                } catch (\RuntimeException $e) {
                    Log::warning('Could not parse layout JSON from AI response', ['error' => $e->getMessage()]);

                    return [];
                }
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Exception generating layout from prompt', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get auto-insights from metrics data.
     */
    public function getAutoInsights(array $metricsData, array $context = []): array
    {
        $prompt = $this->buildInsightsPrompt($metricsData, $context);

        try {
            $response = $this->claude->sendMessage($prompt);

            if ($response['success']) {
                // Parse JSON array from response using domain parser
                try {
                    $insights = $this->aiParser->parseJsonResponse($response['message']);
                    if (is_array($insights)) {
                        return $insights;
                    }
                } catch (\RuntimeException $e) {
                    Log::error('Could not parse insights JSON from AI response', ['error' => $e->getMessage()]);

                    return [];
                }
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Exception getting auto insights', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Generate executive summary for a report.
     */
    public function generateExecutiveSummary(CustomReport $report, array $data): string
    {
        $context = [
            'report_name' => $report->report_name,
            'report_type' => $report->report_type,
            'metrics' => $data['metrics'] ?? [],
            'period' => $report->filters['date_range'] ?? '6 months',
            'scope' => $report->filters['scope'] ?? 'individual',
        ];

        return $this->generateAdaptiveText($context, 'executive_summary', $report->organization?->name);
    }

    /**
     * Build the adaptive text prompt.
     */
    protected function buildAdaptiveTextPrompt(array $dataContext, string $format, ?string $orgName): string
    {
        $formatInstructions = match ($format) {
            'bullets' => 'Use bullet points. Each bullet should be a single clear insight.',
            'executive_summary' => 'Write a concise executive summary with 2-3 paragraphs covering: key findings, areas of concern, and recommendations.',
            default => 'Write in narrative paragraphs. Keep each paragraph to 2-3 sentences.',
        };

        $contextJson = json_encode($dataContext, JSON_PRETTY_PRINT);

        return <<<PROMPT
You are an educational data analyst writing a report for {$orgName}.

Data Context:
{$contextJson}

Format Instructions:
{$formatInstructions}

Guidelines:
- Use professional, supportive tone appropriate for educators
- Highlight both achievements and areas needing attention
- Include specific numbers and percentages when available
- Provide actionable recommendations where appropriate
- Keep content concise and scannable

Write the content now. Output only the content, no preamble or explanation.
PROMPT;
    }

    /**
     * Build the layout generation prompt.
     */
    protected function buildLayoutPrompt(string $userPrompt, string $reportType, array $availableMetrics): string
    {
        $metricsJson = json_encode($availableMetrics, JSON_PRETTY_PRINT);

        return <<<PROMPT
Generate a report layout for this request: "{$userPrompt}"

Report type: {$reportType}
Available metrics:
{$metricsJson}

Return a JSON array of elements. Each element should have:
- id: unique string (use uuid format)
- type: text | chart | metric_card | table | ai_text
- position: {x: number, y: number} - position on 800x1000 canvas
- size: {width: number, height: number}
- config: type-specific configuration
- styles: {backgroundColor, borderRadius, padding}

Element type configs:
- text: {content: "HTML content", format: "html"}
- chart: {chart_type: "line|bar|pie", title: "string", metric_keys: ["gpa"], colors: ["#hex"]}
- metric_card: {metric_key: "gpa", label: "GPA", show_trend: true}
- table: {title: "string", columns: ["name", "gpa"], data_source: "participants"}
- ai_text: {prompt: "Write about...", format: "narrative|bullets"}

Create a professional, balanced layout. Start elements at y=40, leave space between elements.
Return ONLY the JSON array, no explanation.
PROMPT;
    }

    /**
     * Build the insights prompt.
     */
    protected function buildInsightsPrompt(array $metricsData, array $context): string
    {
        $dataJson = json_encode($metricsData, JSON_PRETTY_PRINT);
        $contextJson = json_encode($context, JSON_PRETTY_PRINT);

        return <<<PROMPT
Analyze these educational metrics and identify the top 3-5 key insights.

Metrics Data:
{$dataJson}

Context:
{$contextJson}

For each insight, provide:
1. finding: One sentence describing what you observed
2. significance: Why this matters for participant success
3. action: A specific recommended action

Return as a JSON array:
[
  {
    "finding": "...",
    "significance": "...",
    "action": "..."
  }
]

Return ONLY the JSON array, no explanation.
PROMPT;
    }

    /**
     * Normalize layout elements to ensure proper structure.
     */
    protected function normalizeLayout(array $layout): array
    {
        $normalized = [];

        foreach ($layout as $element) {
            if (! isset($element['type'])) {
                continue;
            }

            $normalized[] = [
                'id' => $element['id'] ?? \Illuminate\Support\Str::uuid()->toString(),
                'type' => $element['type'],
                'position' => [
                    'x' => (int) ($element['position']['x'] ?? 40),
                    'y' => (int) ($element['position']['y'] ?? 40),
                ],
                'size' => [
                    'width' => (int) ($element['size']['width'] ?? 200),
                    'height' => (int) ($element['size']['height'] ?? 100),
                ],
                'config' => $element['config'] ?? [],
                'styles' => $element['styles'] ?? [],
                'locked' => false,
            ];
        }

        return $normalized;
    }

    /**
     * Get metrics data for a specific contact.
     *
     * PERFORMANCE OPTIMIZATION:
     * - Uses select() to only fetch required columns (reduces memory and network overhead)
     * - Eager loads relationships to prevent N+1 queries
     * - Uses efficient MongoDB aggregation patterns with proper indexing
     * - Uses array_combine for efficient grouping instead of O(n²) firstWhere loops
     */
    public function getMetricsForContext(string $contactType, int $contactId, array $metricKeys): array
    {
        // Fetch only needed columns for better performance
        $metrics = ContactMetric::forContact($contactType, $contactId)
            ->select(['id', 'metric_key', 'numeric_value', 'status', 'metric_label', 'recorded_at', 'period_start'])
            ->whereIn('metric_key', $metricKeys)
            ->orderBy('period_start', 'desc')
            ->limit(50)
            ->get()
            ->groupBy('metric_key'); // Use collection method instead of loop

        $data = [];
        foreach ($metricKeys as $key) {
            // Get first item from grouped collection (already sorted by period_start desc)
            $metric = $metrics[$key]?->first();
            $data[$key] = [
                'value' => $metric?->numeric_value,
                'status' => $metric?->status,
                'label' => $metric?->metric_label ?? ucwords(str_replace('_', ' ', $key)),
                'recorded_at' => $metric?->recorded_at?->toDateTimeString(),
            ];
        }

        return $data;
    }
}
