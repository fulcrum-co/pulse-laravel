<?php

namespace Database\Seeders;

use App\Models\CustomReport;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportEnhancedSeeder extends Seeder
{
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) { $this->command->error('No school organization found!'); return; }

        $admin = User::where('primary_role', 'admin')->where('org_id', $school->id)->first();

        $reportDefs = [
            ['title' => 'Monthly Wellness Summary', 'desc' => 'Student wellness metrics overview', 'type' => 'dashboard'],
            ['title' => 'Academic Performance Report', 'desc' => 'Academic progress across all students', 'type' => 'analytics'],
            ['title' => 'Attendance Trends Analysis', 'desc' => 'Attendance patterns and insights', 'type' => 'analytics'],
            ['title' => 'Intervention Effectiveness', 'desc' => 'Impact of intervention programs', 'type' => 'analytics'],
            ['title' => 'Student Risk Dashboard', 'desc' => 'At-risk student identification', 'type' => 'dashboard'],
            ['title' => 'SEL Progress Tracking', 'desc' => 'Social-emotional learning outcomes', 'type' => 'analytics'],
            ['title' => 'Behavior Incident Summary', 'desc' => 'Behavioral trends and patterns', 'type' => 'summary'],
            ['title' => 'College Readiness Report', 'desc' => 'Post-secondary preparation metrics', 'type' => 'analytics'],
            ['title' => 'Counseling Services Report', 'desc' => 'Counseling utilization and outcomes', 'type' => 'summary'],
            ['title' => 'Parent Engagement Metrics', 'desc' => 'Family involvement tracking', 'type' => 'analytics'],
            ['title' => 'Resource Utilization Report', 'desc' => 'Student resource engagement', 'type' => 'summary'],
            ['title' => 'Course Completion Dashboard', 'desc' => 'Mini course completion rates', 'type' => 'dashboard'],
            ['title' => 'Survey Response Analysis', 'desc' => 'Survey participation and insights', 'type' => 'analytics'],
            ['title' => 'Strategic Plan Progress', 'desc' => 'Individual plan tracking', 'type' => 'summary'],
            ['title' => 'Grade Distribution Report', 'desc' => 'Academic grade trends', 'type' => 'analytics'],
            ['title' => 'Equity Metrics Dashboard', 'desc' => 'Equity and access analysis', 'type' => 'dashboard'],
            ['title' => 'Graduation Pathway Tracker', 'desc' => 'Credit and graduation progress', 'type' => 'analytics'],
            ['title' => 'Support Services Summary', 'desc' => 'Comprehensive support utilization', 'type' => 'summary'],
            ['title' => 'Mental Health Screening', 'desc' => 'Wellness screening results', 'type' => 'analytics'],
            ['title' => 'Quarterly Executive Summary', 'desc' => 'Leadership overview report', 'type' => 'summary'],
        ];

        $reports = collect($reportDefs)->map(fn($d) => CustomReport::create([
            'org_id' => $school->id,
            'report_name' => $d['title'],
            'report_description' => $d['desc'],
            'report_type' => $d['type'],
            'status' => 'published',
            'created_by' => $admin->id,
            'created_at' => now()->subDays(rand(1, 90)),
        ]));

        $this->command->info("Created {$reports->count()} reports");
    }
}
