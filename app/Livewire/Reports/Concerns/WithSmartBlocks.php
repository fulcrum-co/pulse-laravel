<?php

namespace App\Livewire\Reports\Concerns;

use Illuminate\Support\Str;

trait WithSmartBlocks
{
    /**
     * Smart Block definitions - pre-built composite components
     */
    protected function getSmartBlockDefinitions(): array
    {
        $terminology = app(\App\Services\TerminologyService::class);

        return [
            'learner_header' => [
                'name' => $terminology->get('smart_block_learner_header_name'),
                'description' => $terminology->get('smart_block_learner_header_description'),
                'category' => $terminology->get('smart_block_category_participant_label'),
                'size' => ['width' => 720, 'height' => 120],
            ],
            'metrics_row' => [
                'name' => $terminology->get('smart_block_metrics_row_name'),
                'description' => $terminology->get('smart_block_metrics_row_description'),
                'category' => $terminology->get('smart_block_category_data_label'),
                'default_metrics' => ['gpa', 'attendance_rate', 'wellness_score', 'engagement_score'],
                'size' => ['width' => 720, 'height' => 100],
            ],
            'trend_section' => [
                'name' => $terminology->get('smart_block_trend_section_name'),
                'description' => $terminology->get('smart_block_trend_section_description'),
                'category' => $terminology->get('smart_block_category_analysis_label'),
                'size' => ['width' => 720, 'height' => 400],
            ],
            'risk_banner' => [
                'name' => $terminology->get('smart_block_risk_banner_name'),
                'description' => $terminology->get('smart_block_risk_banner_description'),
                'category' => $terminology->get('smart_block_category_participant_label'),
                'size' => ['width' => 720, 'height' => 80],
            ],
            'comparison_chart' => [
                'name' => $terminology->get('smart_block_comparison_chart_name'),
                'description' => $terminology->get('smart_block_comparison_chart_description'),
                'category' => $terminology->get('smart_block_category_analysis_label'),
                'size' => ['width' => 720, 'height' => 320],
            ],
            'executive_summary' => [
                'name' => $terminology->get('smart_block_executive_summary_name'),
                'description' => $terminology->get('smart_block_executive_summary_description'),
                'category' => $terminology->get('smart_block_category_analysis_label'),
                'size' => ['width' => 720, 'height' => 280],
            ],
        ];
    }

    /**
     * Add a smart block to the canvas
     */
    public function addSmartBlock(string $blockType): void
    {
        $definitions = $this->getSmartBlockDefinitions();

        if (! isset($definitions[$blockType])) {
            return;
        }

        $definition = $definitions[$blockType];
        $baseY = $this->getNextY();

        // Generate elements based on block type
        $elements = match ($blockType) {
            'learner_header' => $this->generateLearnerHeaderBlock($baseY),
            'metrics_row' => $this->generateMetricsRowBlock($baseY),
            'trend_section' => $this->generateTrendSectionBlock($baseY),
            'risk_banner' => $this->generateRiskBannerBlock($baseY),
            'comparison_chart' => $this->generateComparisonChartBlock($baseY),
            'executive_summary' => $this->generateExecutiveSummaryBlock($baseY),
            default => [],
        };

        // Add all elements to canvas
        foreach ($elements as $element) {
            $this->elements[] = $element;
        }

        // Select the first element of the block
        if (! empty($elements)) {
            $this->selectedElementId = $elements[0]['id'];
        }

        $this->pushHistory();
    }

    /**
     * Generate Participant Header block elements
     * Note: Uses getNextY() from WithElementManagement trait
     */
    protected function generateLearnerHeaderBlock(int $baseY): array
    {
        $terminology = app(\App\Services\TerminologyService::class);
        $elements = [];

        // Title text
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 40, 'y' => $baseY],
            'size' => ['width' => 500, 'height' => 50],
            'config' => [
                'content' => '<h2 style="margin: 0; font-size: 24px; font-weight: 600; color: #111827;">'.$terminology->get('participant_progress_report_label').'</h2>',
            ],
            'styles' => [
                'backgroundColor' => 'transparent',
                'borderRadius' => 0,
                'padding' => 8,
            ],
        ];

        // Subtitle with participant placeholder
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 40, 'y' => $baseY + 50],
            'size' => ['width' => 400, 'height' => 30],
            'config' => [
                'content' => '<p style="margin: 0; font-size: 14px; color: #6B7280;">'.$terminology->get('report_select_participant_help_label').'</p>',
            ],
            'styles' => [
                'backgroundColor' => 'transparent',
                'borderRadius' => 0,
                'padding' => 8,
            ],
        ];

        // Date indicator
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 560, 'y' => $baseY],
            'size' => ['width' => 180, 'height' => 30],
            'config' => [
                'content' => '<p style="margin: 0; font-size: 12px; color: #9CA3AF; text-align: right;">'.now()->format('F j, Y').'</p>',
            ],
            'styles' => [
                'backgroundColor' => 'transparent',
                'borderRadius' => 0,
                'padding' => 8,
            ],
        ];

        return $elements;
    }

    /**
     * Generate Metrics Row block elements
     */
    protected function generateMetricsRowBlock(int $baseY): array
    {
        $elements = [];
        $metrics = ['gpa', 'attendance_rate', 'wellness_score', 'engagement_score'];
        $labels = ['GPA', 'Attendance', 'Wellness', 'Engagement'];
        $cardWidth = 170;
        $gap = 10;
        $startX = 40;

        foreach ($metrics as $index => $metric) {
            $elements[] = [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => $startX + ($cardWidth + $gap) * $index, 'y' => $baseY],
                'size' => ['width' => $cardWidth, 'height' => 90],
                'config' => [
                    'metric_key' => $metric,
                    'label' => $labels[$index],
                    'show_trend' => true,
                ],
                'styles' => [
                    'backgroundColor' => '#F9FAFB',
                    'borderRadius' => 12,
                    'padding' => 16,
                    'borderWidth' => 1,
                    'borderColor' => '#E5E7EB',
                ],
            ];
        }

        return $elements;
    }

    /**
     * Generate Trend Section block elements
     */
    protected function generateTrendSectionBlock(int $baseY): array
    {
        $terminology = app(\App\Services\TerminologyService::class);
        $elements = [];

        // Section title
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 40, 'y' => $baseY],
            'size' => ['width' => 300, 'height' => 40],
            'config' => [
                'content' => '<h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">'.$terminology->get('performance_over_time_label').'</h3>',
            ],
            'styles' => [
                'backgroundColor' => 'transparent',
                'borderRadius' => 0,
                'padding' => 8,
            ],
        ];

        // Line chart
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'chart',
            'position' => ['x' => 40, 'y' => $baseY + 50],
            'size' => ['width' => 440, 'height' => 280],
            'config' => [
                'chart_type' => 'line',
                'title' => '',
                'metric_keys' => ['gpa', 'attendance_rate', 'wellness_score'],
            ],
            'styles' => [
                'backgroundColor' => '#FFFFFF',
                'borderRadius' => 12,
                'padding' => 16,
                'borderWidth' => 1,
                'borderColor' => '#E5E7EB',
            ],
        ];

        // AI insights
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'ai_text',
            'position' => ['x' => 500, 'y' => $baseY + 50],
            'size' => ['width' => 240, 'height' => 280],
            'config' => [
                'prompt' => $terminology->get('ai_trend_insights_prompt'),
                'format' => 'bullets',
                'context_metrics' => ['gpa', 'attendance_rate', 'wellness_score', 'engagement_score'],
                'generated_content' => null,
            ],
            'styles' => [
                'backgroundColor' => '#F5F3FF',
                'borderRadius' => 12,
                'padding' => 16,
                'borderWidth' => 1,
                'borderColor' => '#DDD6FE',
            ],
        ];

        return $elements;
    }

    /**
     * Generate Risk Banner block elements
     */
    protected function generateRiskBannerBlock(int $baseY): array
    {
        $terminology = app(\App\Services\TerminologyService::class);
        $elements = [];

        // Risk banner - defaults to "needs data" state
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 40, 'y' => $baseY],
            'size' => ['width' => 720, 'height' => 70],
            'config' => [
                'content' => '<div style="display: flex; align-items: center; gap: 12px;"><div style="font-size: 24px;">📊</div><div><p style="margin: 0; font-size: 14px; font-weight: 600; color: #1F2937;">'.$terminology->get('risk_status_label').'</p><p style="margin: 4px 0 0 0; font-size: 13px; color: #6B7280;">'.$terminology->get('risk_status_select_participant_help_label').'</p></div></div>',
            ],
            'styles' => [
                'backgroundColor' => '#F3F4F6',
                'borderRadius' => 12,
                'padding' => 16,
                'borderWidth' => 1,
                'borderColor' => '#E5E7EB',
            ],
        ];

        return $elements;
    }

    /**
     * Generate Comparison Chart block elements
     */
    protected function generateComparisonChartBlock(int $baseY): array
    {
        $terminology = app(\App\Services\TerminologyService::class);
        $elements = [];

        // Section title
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 40, 'y' => $baseY],
            'size' => ['width' => 400, 'height' => 40],
            'config' => [
                'content' => '<h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">'.$terminology->get('comparison_participant_vs_cohort_label').'</h3>',
            ],
            'styles' => [
                'backgroundColor' => 'transparent',
                'borderRadius' => 0,
                'padding' => 8,
            ],
        ];

        // Bar chart for comparison
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'chart',
            'position' => ['x' => 40, 'y' => $baseY + 50],
            'size' => ['width' => 720, 'height' => 260],
            'config' => [
                'chart_type' => 'bar',
                'title' => '',
                'metric_keys' => ['gpa', 'attendance_rate', 'wellness_score', 'engagement_score'],
            ],
            'styles' => [
                'backgroundColor' => '#FFFFFF',
                'borderRadius' => 12,
                'padding' => 16,
                'borderWidth' => 1,
                'borderColor' => '#E5E7EB',
            ],
        ];

        return $elements;
    }

    /**
     * Generate Executive Summary block elements
     */
    protected function generateExecutiveSummaryBlock(int $baseY): array
    {
        $terminology = app(\App\Services\TerminologyService::class);
        $elements = [];

        // Section title
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 40, 'y' => $baseY],
            'size' => ['width' => 300, 'height' => 40],
            'config' => [
                'content' => '<h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">'.$terminology->get('executive_summary_label').'</h3>',
            ],
            'styles' => [
                'backgroundColor' => 'transparent',
                'borderRadius' => 0,
                'padding' => 8,
            ],
        ];

        // AI executive summary
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'ai_text',
            'position' => ['x' => 40, 'y' => $baseY + 50],
            'size' => ['width' => 480, 'height' => 200],
            'config' => [
                'prompt' => $terminology->get('ai_executive_summary_prompt'),
                'format' => 'executive_summary',
                'context_metrics' => ['gpa', 'attendance_rate', 'wellness_score', 'engagement_score', 'plan_progress'],
                'generated_content' => null,
            ],
            'styles' => [
                'backgroundColor' => '#FEF3C7',
                'borderRadius' => 12,
                'padding' => 16,
                'borderWidth' => 1,
                'borderColor' => '#FDE68A',
            ],
        ];

        // Key metric highlights (3 small metric cards)
        $highlightMetrics = ['gpa', 'attendance_rate', 'wellness_score'];
        $highlightLabels = [
            $terminology->get('metric_gpa_label'),
            $terminology->get('metric_attendance_rate_label'),
            $terminology->get('metric_health_wellness_label'),
        ];
        $cardWidth = 70;
        $startX = 540;

        foreach ($highlightMetrics as $index => $metric) {
            $elements[] = [
                'id' => Str::uuid()->toString(),
                'type' => 'metric_card',
                'position' => ['x' => $startX, 'y' => $baseY + 50 + ($index * 70)],
                'size' => ['width' => 200, 'height' => 60],
                'config' => [
                    'metric_key' => $metric,
                    'label' => $highlightLabels[$index],
                    'show_trend' => false,
                ],
                'styles' => [
                    'backgroundColor' => '#FFFFFF',
                    'borderRadius' => 8,
                    'padding' => 12,
                    'borderWidth' => 1,
                    'borderColor' => '#E5E7EB',
                ],
            ];
        }

        return $elements;
    }
}
