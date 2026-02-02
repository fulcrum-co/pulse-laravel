<?php

namespace App\Livewire\Reports\Concerns;

use Illuminate\Support\Str;

trait WithSmartBlocks
{
    /**
     * Smart Block definitions - pre-built composite components
     */
    protected array $smartBlockDefinitions = [
        'student_header' => [
            'name' => 'Student Profile Header',
            'description' => 'Name, photo, grade, and key identifiers',
            'category' => 'student',
            'size' => ['width' => 720, 'height' => 120],
        ],
        'metrics_row' => [
            'name' => 'Metrics Row',
            'description' => 'Four metric cards in a horizontal layout',
            'category' => 'data',
            'default_metrics' => ['gpa', 'attendance_rate', 'wellness_score', 'engagement_score'],
            'size' => ['width' => 720, 'height' => 100],
        ],
        'trend_section' => [
            'name' => 'Performance Trend Section',
            'description' => 'Chart with title and optional AI insights',
            'category' => 'analysis',
            'size' => ['width' => 720, 'height' => 400],
        ],
        'risk_banner' => [
            'name' => 'Risk Indicator Banner',
            'description' => 'Color-coded risk status with explanation',
            'category' => 'student',
            'size' => ['width' => 720, 'height' => 80],
        ],
        'comparison_chart' => [
            'name' => 'Comparison Chart with Title',
            'description' => 'Side-by-side or grouped comparison',
            'category' => 'analysis',
            'size' => ['width' => 720, 'height' => 320],
        ],
        'executive_summary' => [
            'name' => 'Executive Summary Block',
            'description' => 'AI-generated summary with key metrics callout',
            'category' => 'analysis',
            'size' => ['width' => 720, 'height' => 280],
        ],
    ];

    /**
     * Add a smart block to the canvas
     */
    public function addSmartBlock(string $blockType): void
    {
        if (! isset($this->smartBlockDefinitions[$blockType])) {
            return;
        }

        $definition = $this->smartBlockDefinitions[$blockType];
        $baseY = $this->getNextY();

        // Generate elements based on block type
        $elements = match ($blockType) {
            'student_header' => $this->generateStudentHeaderBlock($baseY),
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
     * Generate Student Header block elements
     * Note: Uses getNextY() from WithElementManagement trait
     */
    protected function generateStudentHeaderBlock(int $baseY): array
    {
        $elements = [];

        // Title text
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 40, 'y' => $baseY],
            'size' => ['width' => 500, 'height' => 50],
            'config' => [
                'content' => '<h2 style="margin: 0; font-size: 24px; font-weight: 600; color: #111827;">Student Progress Report</h2>',
            ],
            'styles' => [
                'backgroundColor' => 'transparent',
                'borderRadius' => 0,
                'padding' => 8,
            ],
        ];

        // Subtitle with student placeholder
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 40, 'y' => $baseY + 50],
            'size' => ['width' => 400, 'height' => 30],
            'config' => [
                'content' => '<p style="margin: 0; font-size: 14px; color: #6B7280;">Select a student from the filter bar above to populate this report</p>',
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
        $elements = [];

        // Section title
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 40, 'y' => $baseY],
            'size' => ['width' => 300, 'height' => 40],
            'config' => [
                'content' => '<h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">Performance Over Time</h3>',
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
                'prompt' => 'Analyze the student performance trends and provide 3-4 key insights about their progress, areas of improvement, and any concerns.',
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
        $elements = [];

        // Risk banner - defaults to "needs data" state
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 40, 'y' => $baseY],
            'size' => ['width' => 720, 'height' => 70],
            'config' => [
                'content' => '<div style="display: flex; align-items: center; gap: 12px;"><div style="font-size: 24px;">📊</div><div><p style="margin: 0; font-size: 14px; font-weight: 600; color: #1F2937;">Risk Status</p><p style="margin: 4px 0 0 0; font-size: 13px; color: #6B7280;">Select a student to view their risk assessment</p></div></div>',
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
        $elements = [];

        // Section title
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 40, 'y' => $baseY],
            'size' => ['width' => 400, 'height' => 40],
            'config' => [
                'content' => '<h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">Comparison: Student vs. Cohort Average</h3>',
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
        $elements = [];

        // Section title
        $elements[] = [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'position' => ['x' => 40, 'y' => $baseY],
            'size' => ['width' => 300, 'height' => 40],
            'config' => [
                'content' => '<h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #111827;">Executive Summary</h3>',
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
                'prompt' => 'Write a brief executive summary of this student\'s overall performance. Include their strengths, areas for growth, and recommended next steps for parents and teachers.',
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
        $highlightLabels = ['GPA', 'Attendance', 'Wellness'];
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
