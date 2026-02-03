<?php

namespace Database\Seeders;

use App\Models\CustomReport;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        // Get first organization and user for seeding
        $org = Organization::first();
        $user = User::first();

        if (! $org || ! $user) {
            $this->command->warn('No organization or user found. Skipping ReportSeeder.');

            return;
        }

        $this->command->info('Creating sample reports...');

        // Document Reports
        $this->createDocumentReports($org, $user);

        // Widget Reports
        $this->createWidgetReports($org, $user);

        // Social Media Reports
        $this->createSocialReports($org, $user);

        // Custom Reports
        $this->createCustomReports($org, $user);

        $this->command->info('Sample reports created successfully!');
    }

    protected function createDocumentReports(Organization $org, User $user): void
    {
        // Student Progress Report - Full Document
        CustomReport::create([
            'org_id' => $org->id,
            'created_by' => $user->id,
            'report_name' => 'Quarterly Student Progress Report',
            'report_description' => 'Comprehensive quarterly progress report showing student academic performance, attendance trends, and wellness indicators.',
            'report_type' => 'document',
            'status' => CustomReport::STATUS_PUBLISHED,
            'version' => 1,
            'page_settings' => [
                'size' => 'letter',
                'orientation' => 'portrait',
                'margins' => ['top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40],
                'backgroundColor' => '#FFFFFF',
            ],
            'report_layout' => [
                'elements' => [
                    $this->createHeadingElement('Quarterly Student Progress Report', 40, 40, 680, 50),
                    $this->createLineElement(40, 100, 680, 2),
                    $this->createTextElement('<p>This report provides a comprehensive overview of student performance during the current quarter, including academic metrics, attendance data, and wellness indicators.</p>', 40, 120, 680, 60),
                    $this->createMetricCardRow(40, 200),
                    $this->createChartElement('Academic Performance Trend', 'line', ['gpa', 'test_scores'], 40, 340, 680, 280),
                    $this->createChartElement('Attendance Overview', 'bar', ['attendance_rate'], 40, 640, 330, 240),
                    $this->createChartElement('Wellness Distribution', 'pie', ['wellness_score'], 390, 640, 330, 240),
                    $this->createAiTextElement('Summarize the key insights from the student progress data, highlighting achievements and areas for improvement.', 40, 900, 680, 120),
                ],
            ],
            'filters' => [
                'date_range' => 'this_quarter',
                'grade_levels' => [],
                'contact_list_id' => null,
            ],
        ]);

        // Monthly Attendance Summary
        CustomReport::create([
            'org_id' => $org->id,
            'created_by' => $user->id,
            'report_name' => 'Monthly Attendance Summary',
            'report_description' => 'Monthly breakdown of attendance patterns across all grade levels.',
            'report_type' => 'document',
            'status' => CustomReport::STATUS_DRAFT,
            'version' => 1,
            'page_settings' => [
                'size' => 'letter',
                'orientation' => 'landscape',
                'margins' => ['top' => 30, 'right' => 30, 'bottom' => 30, 'left' => 30],
                'backgroundColor' => '#FFFFFF',
            ],
            'report_layout' => [
                'elements' => [
                    $this->createLogoElement(40, 30),
                    $this->createHeadingElement('Monthly Attendance Summary', 200, 40, 500, 40),
                    $this->createTableElement('Attendance by Grade', 40, 100, 900, 300),
                    $this->createChartElement('Daily Attendance Trend', 'line', ['attendance_rate'], 40, 420, 440, 240),
                    $this->createChartElement('Absence Reasons', 'pie', ['absence_types'], 500, 420, 440, 240),
                ],
            ],
            'filters' => [
                'date_range' => 'this_month',
            ],
        ]);

        // Annual Academic Review
        CustomReport::create([
            'org_id' => $org->id,
            'created_by' => $user->id,
            'report_name' => 'Annual Academic Review',
            'report_description' => 'Year-end comprehensive academic performance review.',
            'report_type' => 'document',
            'status' => CustomReport::STATUS_PUBLISHED,
            'version' => 3,
            'page_settings' => [
                'size' => 'letter',
                'orientation' => 'portrait',
                'backgroundColor' => '#FAFAFA',
            ],
            'report_layout' => [
                'elements' => [
                    $this->createRectangleElement('#F97316', 0, 0, 760, 120, 0),
                    $this->createHeadingElement('Annual Academic Review 2024', 40, 40, 600, 50, '#FFFFFF'),
                    $this->createTextElement('<p style="color: white;">School Year Summary Report</p>', 40, 80, 300, 30),
                    $this->createMetricCardRow(40, 150),
                    $this->createChartElement('Year-over-Year GPA Comparison', 'bar', ['gpa'], 40, 290, 680, 280),
                    $this->createTableElement('Top Performing Students', 40, 590, 680, 250),
                    $this->createAiTextElement('Provide an executive summary of the academic year, noting significant trends and recommendations for the upcoming year.', 40, 860, 680, 140),
                ],
            ],
        ]);
    }

    protected function createWidgetReports(Organization $org, User $user): void
    {
        // Dashboard GPA Widget
        CustomReport::create([
            'org_id' => $org->id,
            'created_by' => $user->id,
            'report_name' => 'GPA Overview Widget',
            'report_description' => 'Compact GPA display widget for embedding in dashboards.',
            'report_type' => 'widget',
            'status' => CustomReport::STATUS_PUBLISHED,
            'version' => 1,
            'page_settings' => [
                'width' => 400,
                'height' => 300,
                'backgroundColor' => '#FFFFFF',
            ],
            'report_layout' => [
                'elements' => [
                    $this->createHeadingElement('GPA Overview', 16, 16, 368, 32, '#111827', 18),
                    $this->createMetricCardElement('gpa', 'Average GPA', 16, 60, 176, 90, '#EFF6FF', '#1E40AF'),
                    $this->createMetricCardElement('gpa_trend', 'GPA Trend', 208, 60, 176, 90, '#F0FDF4', '#166534'),
                    $this->createChartElement('30-Day Trend', 'line', ['gpa'], 16, 166, 368, 118),
                ],
            ],
        ]);

        // Attendance Status Widget
        CustomReport::create([
            'org_id' => $org->id,
            'created_by' => $user->id,
            'report_name' => 'Attendance Status Widget',
            'report_description' => 'Real-time attendance status for dashboard embedding.',
            'report_type' => 'widget',
            'status' => CustomReport::STATUS_PUBLISHED,
            'version' => 2,
            'page_settings' => [
                'width' => 320,
                'height' => 200,
                'backgroundColor' => '#F8FAFC',
            ],
            'report_layout' => [
                'elements' => [
                    $this->createHeadingElement("Today's Attendance", 12, 12, 296, 28, '#374151', 16),
                    $this->createChartElement('', 'donut', ['attendance_rate'], 12, 48, 140, 140),
                    $this->createMetricCardElement('attendance_rate', 'Present', 168, 48, 140, 64, '#DCFCE7', '#166534'),
                    $this->createMetricCardElement('absent_rate', 'Absent', 168, 120, 140, 64, '#FEE2E2', '#991B1B'),
                ],
            ],
        ]);

        // Wellness Quick View Widget
        CustomReport::create([
            'org_id' => $org->id,
            'created_by' => $user->id,
            'report_name' => 'Wellness Quick View',
            'report_description' => 'Compact wellness indicator widget.',
            'report_type' => 'widget',
            'status' => CustomReport::STATUS_DRAFT,
            'version' => 1,
            'page_settings' => [
                'width' => 280,
                'height' => 180,
                'backgroundColor' => '#FFFFFF',
            ],
            'report_layout' => [
                'elements' => [
                    $this->createHeadingElement('Wellness Score', 12, 12, 256, 24, '#6B7280', 14),
                    $this->createMetricCardElement('wellness_score', 'Overall', 12, 44, 256, 80, '#FDF2F8', '#9D174D'),
                    $this->createTextElement('<p class="text-xs text-gray-500">Based on latest survey responses</p>', 12, 132, 256, 36),
                ],
            ],
        ]);
    }

    protected function createSocialReports(Organization $org, User $user): void
    {
        // Instagram Achievement Post
        CustomReport::create([
            'org_id' => $org->id,
            'created_by' => $user->id,
            'report_name' => 'Student Achievement Highlight',
            'report_description' => 'Instagram-ready post celebrating student achievements.',
            'report_type' => 'social',
            'status' => CustomReport::STATUS_PUBLISHED,
            'version' => 1,
            'page_settings' => [
                'width' => 1080,
                'height' => 1080,
                'backgroundColor' => '#1E40AF',
            ],
            'report_layout' => [
                'elements' => [
                    $this->createRectangleElement('#F97316', 0, 0, 1080, 200, 0),
                    $this->createLogoElement(440, 40, 200, 80),
                    $this->createHeadingElement('STUDENT SPOTLIGHT', 140, 240, 800, 60, '#FFFFFF', 42),
                    $this->createCircleElement('#FFFFFF', 390, 340, 300, 300),
                    $this->createHeadingElement('3.95 GPA', 340, 700, 400, 80, '#FFFFFF', 56),
                    $this->createTextElement('<p style="text-align: center; color: #93C5FD; font-size: 24px;">Congratulations to our honor roll students!</p>', 140, 800, 800, 60),
                    $this->createTextElement('<p style="text-align: center; color: #FFFFFF; font-size: 18px;">#StudentSuccess #HonorRoll #ProudSchool</p>', 140, 980, 800, 50),
                ],
            ],
        ]);

        // Twitter Stats Card
        CustomReport::create([
            'org_id' => $org->id,
            'created_by' => $user->id,
            'report_name' => 'Weekly Stats Twitter Card',
            'report_description' => 'Twitter-optimized weekly statistics card.',
            'report_type' => 'social',
            'status' => CustomReport::STATUS_DRAFT,
            'version' => 1,
            'page_settings' => [
                'width' => 1200,
                'height' => 675,
                'backgroundColor' => '#0F172A',
            ],
            'report_layout' => [
                'elements' => [
                    $this->createLogoElement(40, 40, 120, 48),
                    $this->createHeadingElement('This Week By The Numbers', 180, 44, 600, 44, '#FFFFFF', 32),
                    $this->createMetricCardElement('attendance_rate', 'Attendance', 40, 140, 280, 140, '#1E293B', '#22D3EE'),
                    $this->createMetricCardElement('gpa', 'Avg GPA', 340, 140, 280, 140, '#1E293B', '#A78BFA'),
                    $this->createMetricCardElement('assignments_completed', 'Assignments', 640, 140, 280, 140, '#1E293B', '#4ADE80'),
                    $this->createMetricCardElement('wellness_score', 'Wellness', 940, 140, 220, 140, '#1E293B', '#FB7185'),
                    $this->createChartElement('Weekly Trend', 'line', ['gpa', 'attendance_rate'], 40, 300, 1120, 280),
                    $this->createTextElement('<p style="color: #64748B; font-size: 14px;">Data updated: February 2, 2026</p>', 40, 600, 400, 40),
                ],
            ],
        ]);

        // Facebook Event Banner
        CustomReport::create([
            'org_id' => $org->id,
            'created_by' => $user->id,
            'report_name' => 'Parent Conference Announcement',
            'report_description' => 'Facebook event banner for parent-teacher conferences.',
            'report_type' => 'social',
            'status' => CustomReport::STATUS_PUBLISHED,
            'version' => 2,
            'page_settings' => [
                'width' => 1200,
                'height' => 628,
                'backgroundColor' => '#F97316',
            ],
            'report_layout' => [
                'elements' => [
                    $this->createRectangleElement('#FFFFFF', 40, 40, 1120, 548, 16),
                    $this->createLogoElement(80, 60, 160, 64),
                    $this->createHeadingElement('Parent-Teacher Conference', 80, 160, 1040, 60, '#111827', 48),
                    $this->createTextElement('<p style="font-size: 28px; color: #374151; text-align: center;">Review your child\'s progress with their teachers</p>', 80, 240, 1040, 50),
                    $this->createHeadingElement('March 15-17, 2026', 80, 340, 1040, 50, '#F97316', 40),
                    $this->createTextElement('<p style="font-size: 22px; color: #6B7280; text-align: center;">3:00 PM - 8:00 PM | Main Campus</p>', 80, 410, 1040, 40),
                    $this->createTextElement('<p style="font-size: 18px; color: #9CA3AF; text-align: center;">Schedule your appointment at school.edu/conferences</p>', 80, 500, 1040, 40),
                ],
            ],
        ]);
    }

    protected function createCustomReports(Organization $org, User $user): void
    {
        // Custom Cohort Analysis
        CustomReport::create([
            'org_id' => $org->id,
            'created_by' => $user->id,
            'report_name' => 'At-Risk Student Cohort Analysis',
            'report_description' => 'Custom analysis of students identified as at-risk based on multiple indicators.',
            'report_type' => 'custom',
            'status' => CustomReport::STATUS_PUBLISHED,
            'version' => 4,
            'page_settings' => [
                'width' => 1000,
                'height' => 1400,
                'backgroundColor' => '#FFFFFF',
            ],
            'report_layout' => [
                'elements' => [
                    $this->createHeadingElement('At-Risk Student Cohort Analysis', 40, 40, 920, 50),
                    $this->createTextElement('<p class="text-gray-600">Comprehensive review of students requiring additional support based on academic, attendance, and wellness metrics.</p>', 40, 100, 920, 60),
                    $this->createLineElement(40, 170, 920, 2),
                    $this->createHeadingElement('Risk Indicators Overview', 40, 200, 400, 36, '#111827', 20),
                    $this->createChartElement('Risk Distribution', 'pie', ['risk_level'], 40, 250, 440, 280),
                    $this->createChartElement('Risk Factors', 'bar', ['academic_risk', 'attendance_risk', 'wellness_risk'], 500, 250, 460, 280),
                    $this->createHeadingElement('Student Details', 40, 560, 400, 36, '#111827', 20),
                    $this->createTableElement('At-Risk Students', 40, 610, 920, 350),
                    $this->createHeadingElement('Intervention Recommendations', 40, 990, 400, 36, '#111827', 20),
                    $this->createAiTextElement('Based on the at-risk indicators, provide specific intervention recommendations for each risk category.', 40, 1040, 920, 160),
                    $this->createTextElement('<p class="text-xs text-gray-400">Report generated on February 2, 2026. Data reflects current semester metrics.</p>', 40, 1340, 920, 40),
                ],
            ],
            'filters' => [
                'risk_level' => ['high', 'moderate'],
                'date_range' => 'this_semester',
            ],
        ]);

        // Custom Comparison Report
        CustomReport::create([
            'org_id' => $org->id,
            'created_by' => $user->id,
            'report_name' => 'Grade Level Comparison',
            'report_description' => 'Side-by-side comparison of performance metrics across grade levels.',
            'report_type' => 'custom',
            'status' => CustomReport::STATUS_DRAFT,
            'version' => 1,
            'page_settings' => [
                'width' => 1200,
                'height' => 900,
                'backgroundColor' => '#F9FAFB',
            ],
            'report_layout' => [
                'elements' => [
                    $this->createHeadingElement('Grade Level Performance Comparison', 40, 30, 1120, 44),
                    $this->createChartElement('GPA by Grade', 'bar', ['gpa'], 40, 100, 560, 260),
                    $this->createChartElement('Attendance by Grade', 'bar', ['attendance_rate'], 620, 100, 540, 260),
                    $this->createChartElement('Wellness by Grade', 'bar', ['wellness_score'], 40, 380, 560, 260),
                    $this->createChartElement('Assignment Completion', 'bar', ['completion_rate'], 620, 380, 540, 260),
                    $this->createTableElement('Detailed Metrics by Grade', 40, 660, 1120, 220),
                ],
            ],
            'filters' => [
                'compare_by' => 'grade_level',
                'grade_levels' => ['9', '10', '11', '12'],
            ],
        ]);

        // Custom Infographic Style
        CustomReport::create([
            'org_id' => $org->id,
            'created_by' => $user->id,
            'report_name' => 'School Year Infographic',
            'report_description' => 'Visual infographic summarizing key school year statistics.',
            'report_type' => 'custom',
            'status' => CustomReport::STATUS_PUBLISHED,
            'version' => 2,
            'page_settings' => [
                'width' => 800,
                'height' => 2000,
                'backgroundColor' => '#1E3A5F',
            ],
            'report_layout' => [
                'elements' => [
                    $this->createRectangleElement('#F97316', 0, 0, 800, 180, 0),
                    $this->createHeadingElement('2025-2026', 200, 40, 400, 50, '#FFFFFF', 48),
                    $this->createHeadingElement('School Year in Review', 150, 100, 500, 40, '#FFFFFF', 28),
                    $this->createCircleElement('#FFFFFF', 300, 240, 200, 200),
                    $this->createHeadingElement('1,247', 310, 300, 180, 60, '#1E3A5F', 48),
                    $this->createTextElement('<p style="text-align: center; color: #1E3A5F; font-size: 16px;">Students Enrolled</p>', 300, 360, 200, 30),
                    $this->createMetricCardElement('gpa', 'Avg GPA', 100, 500, 250, 120, '#2563EB', '#FFFFFF'),
                    $this->createMetricCardElement('attendance_rate', 'Attendance', 450, 500, 250, 120, '#059669', '#FFFFFF'),
                    $this->createMetricCardElement('graduation_rate', 'Graduation', 100, 660, 250, 120, '#7C3AED', '#FFFFFF'),
                    $this->createMetricCardElement('college_bound', 'College Bound', 450, 660, 250, 120, '#DC2626', '#FFFFFF'),
                    $this->createHeadingElement('Academic Highlights', 100, 840, 600, 40, '#FFFFFF', 24),
                    $this->createChartElement('Monthly GPA Trend', 'line', ['gpa'], 50, 900, 700, 280),
                    $this->createHeadingElement('Attendance Patterns', 100, 1220, 600, 40, '#FFFFFF', 24),
                    $this->createChartElement('Weekly Attendance', 'bar', ['attendance_rate'], 50, 1280, 700, 280),
                    $this->createHeadingElement('Key Achievements', 100, 1600, 600, 40, '#FFFFFF', 24),
                    $this->createTextElement('<ul style="color: #93C5FD; font-size: 18px; line-height: 2;"><li>98% graduation rate - highest in district</li><li>15% increase in AP enrollment</li><li>$2.5M in scholarships awarded</li><li>State champions in robotics</li></ul>', 100, 1660, 600, 200),
                    $this->createTextElement('<p style="text-align: center; color: #64748B; font-size: 14px;">Data as of February 2026</p>', 200, 1920, 400, 40),
                ],
            ],
        ]);
    }

    // Helper methods to create element arrays

    protected function createHeadingElement(string $content, int $x, int $y, int $w, int $h, string $color = '#111827', int $fontSize = 24): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => 'heading',
            'locked' => false,
            'position' => ['x' => $x, 'y' => $y],
            'size' => ['width' => $w, 'height' => $h],
            'config' => ['content' => "<h2>{$content}</h2>", 'format' => 'html'],
            'styles' => [
                'backgroundColor' => 'transparent',
                'padding' => 8,
                'borderRadius' => 0,
                'fontSize' => $fontSize,
                'fontWeight' => 'bold',
                'textAlign' => 'left',
                'color' => $color,
            ],
        ];
    }

    protected function createTextElement(string $content, int $x, int $y, int $w, int $h): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => 'text',
            'locked' => false,
            'position' => ['x' => $x, 'y' => $y],
            'size' => ['width' => $w, 'height' => $h],
            'config' => ['content' => $content, 'format' => 'html'],
            'styles' => [
                'backgroundColor' => 'transparent',
                'padding' => 8,
                'borderRadius' => 4,
                'fontSize' => 14,
                'fontWeight' => 'normal',
                'textAlign' => 'left',
                'color' => '#374151',
            ],
        ];
    }

    protected function createChartElement(string $title, string $type, array $metrics, int $x, int $y, int $w, int $h): array
    {
        $colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];

        return [
            'id' => Str::uuid()->toString(),
            'type' => 'chart',
            'locked' => false,
            'position' => ['x' => $x, 'y' => $y],
            'size' => ['width' => $w, 'height' => $h],
            'config' => [
                'chart_type' => $type,
                'title' => $title,
                'metric_keys' => $metrics,
                'colors' => array_slice($colors, 0, count($metrics)),
            ],
            'styles' => [
                'backgroundColor' => '#FFFFFF',
                'borderRadius' => 8,
                'padding' => 16,
                'borderWidth' => 1,
                'borderColor' => '#E5E7EB',
            ],
        ];
    }

    protected function createMetricCardElement(string $key, string $label, int $x, int $y, int $w, int $h, string $bg = '#F0F9FF', string $valueColor = '#1E40AF'): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => 'metric_card',
            'locked' => false,
            'position' => ['x' => $x, 'y' => $y],
            'size' => ['width' => $w, 'height' => $h],
            'config' => [
                'metric_key' => $key,
                'label' => $label,
                'show_trend' => true,
                'comparison_period' => 'last_month',
            ],
            'styles' => [
                'backgroundColor' => $bg,
                'borderRadius' => 8,
                'padding' => 16,
                'valueColor' => $valueColor,
            ],
        ];
    }

    protected function createMetricCardRow(int $x, int $y): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => 'metric_card',
            'locked' => false,
            'position' => ['x' => $x, 'y' => $y],
            'size' => ['width' => 160, 'height' => 100],
            'config' => [
                'metric_key' => 'gpa',
                'label' => 'Avg GPA',
                'show_trend' => true,
            ],
            'styles' => [
                'backgroundColor' => '#EFF6FF',
                'borderRadius' => 8,
                'padding' => 12,
                'valueColor' => '#1E40AF',
            ],
        ];
    }

    protected function createTableElement(string $title, int $x, int $y, int $w, int $h): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => 'table',
            'locked' => false,
            'position' => ['x' => $x, 'y' => $y],
            'size' => ['width' => $w, 'height' => $h],
            'config' => [
                'title' => $title,
                'columns' => ['name', 'gpa', 'attendance', 'wellness'],
                'data_source' => 'students',
                'sortable' => true,
            ],
            'styles' => ['backgroundColor' => '#FFFFFF', 'borderRadius' => 8],
        ];
    }

    protected function createAiTextElement(string $prompt, int $x, int $y, int $w, int $h): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => 'ai_text',
            'locked' => false,
            'position' => ['x' => $x, 'y' => $y],
            'size' => ['width' => $w, 'height' => $h],
            'config' => [
                'prompt' => $prompt,
                'format' => 'narrative',
                'context_metrics' => ['gpa', 'attendance_rate', 'wellness_score'],
                'generated_content' => null,
                'generated_at' => null,
            ],
            'styles' => [
                'backgroundColor' => '#F9FAFB',
                'borderRadius' => 8,
                'padding' => 20,
                'fontSize' => 14,
                'color' => '#374151',
            ],
        ];
    }

    protected function createLogoElement(int $x, int $y, int $w = 150, int $h = 60): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => 'logo',
            'locked' => false,
            'position' => ['x' => $x, 'y' => $y],
            'size' => ['width' => $w, 'height' => $h],
            'config' => ['src' => null, 'alt' => 'Organization Logo', 'fit' => 'contain', 'use_org_logo' => true],
            'styles' => ['borderRadius' => 0],
        ];
    }

    protected function createLineElement(int $x, int $y, int $w, int $h): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => 'line',
            'locked' => false,
            'position' => ['x' => $x, 'y' => $y],
            'size' => ['width' => $w, 'height' => $h],
            'config' => [],
            'styles' => [
                'backgroundColor' => '#E5E7EB',
                'opacity' => 100,
            ],
        ];
    }

    protected function createRectangleElement(string $bg, int $x, int $y, int $w, int $h, int $radius = 8): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => 'rectangle',
            'locked' => false,
            'position' => ['x' => $x, 'y' => $y],
            'size' => ['width' => $w, 'height' => $h],
            'config' => [],
            'styles' => [
                'backgroundColor' => $bg,
                'borderRadius' => $radius,
                'opacity' => 100,
            ],
        ];
    }

    protected function createCircleElement(string $bg, int $x, int $y, int $w, int $h): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'type' => 'circle',
            'locked' => false,
            'position' => ['x' => $x, 'y' => $y],
            'size' => ['width' => $w, 'height' => $h],
            'config' => [],
            'styles' => [
                'backgroundColor' => $bg,
                'borderRadius' => 50,
                'opacity' => 100,
            ],
        ];
    }
}
