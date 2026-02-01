<?php

namespace App\Services;

use App\Models\ContactMetric;
use App\Models\CustomReport;
use Illuminate\Support\Facades\Log;

class ReportAIService
{
    public function __construct(
        protected ClaudeService $claude,
        protected ContactMetricService $metrics
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
        $availableMetrics = [
            'gpa' => 'GPA (Grade Point Average)',
            'attendance_rate' => 'Attendance Rate',
            'wellness_score' => 'Health & Wellness Score',
            'engagement_score' => 'Engagement Score',
            'emotional_wellbeing' => 'Emotional Well-Being',
            'plan_progress' => 'Student Plan Progress',
        ];

        $prompt = $this->buildLayoutPrompt($userPrompt, $reportType, $availableMetrics);

        try {
            $response = $this->claude->sendMessage($prompt);

            if ($response['success']) {
                // Parse JSON from response
                $content = $response['message'];

                // Extract JSON from the response (handle cases where Claude adds explanation)
                if (preg_match('/\[[\s\S]*\]/', $content, $matches)) {
                    $json = $matches[0];
                    $layout = json_decode($json, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($layout)) {
                        return $this->normalizeLayout($layout);
                    }
                }

                Log::warning('Could not parse layout JSON from AI response');

                return [];
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
                $content = $response['message'];

                // Extract JSON array from response
                if (preg_match('/\[[\s\S]*\]/', $content, $matches)) {
                    $json = $matches[0];
                    $insights = json_decode($json, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($insights)) {
                        return $insights;
                    }
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
- table: {title: "string", columns: ["name", "gpa"], data_source: "students"}
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
2. significance: Why this matters for student success
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
     */
    public function getMetricsForContext(string $contactType, int $contactId, array $metricKeys): array
    {
        $metrics = ContactMetric::forContact($contactType, $contactId)
            ->whereIn('metric_key', $metricKeys)
            ->orderBy('period_start', 'desc')
            ->limit(50)
            ->get();

        $data = [];
        foreach ($metricKeys as $key) {
            $metric = $metrics->firstWhere('metric_key', $key);
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
