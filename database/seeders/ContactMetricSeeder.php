<?php

namespace Database\Seeders;

use App\Models\ContactMetric;
use App\Models\ContactNote;
use App\Models\MetricThreshold;
use App\Models\Organization;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ContactMetricSeeder extends Seeder
{
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (!$school) {
            $this->command->warn('No school organization found. Skipping ContactMetricSeeder.');
            return;
        }

        $students = Student::where('org_id', $school->id)->get();
        $admin = User::where('org_id', $school->id)->where('primary_role', 'admin')->first();

        // Create default metric thresholds
        $this->createDefaultThresholds($school->id);

        // Create metrics for each student
        foreach ($students as $student) {
            $this->createStudentMetrics($student, $school->id, $admin?->id);
            $this->createStudentNotes($student, $school->id, $admin?->id);
        }

        $this->command->info('Created contact metrics and notes for ' . $students->count() . ' students.');
    }

    private function createDefaultThresholds(int $orgId): void
    {
        $thresholds = [
            // Academics
            ['metric_category' => 'academics', 'metric_key' => 'gpa', 'on_track_min' => 3.0, 'at_risk_min' => 2.0, 'off_track_min' => 0],
            ['metric_category' => 'academics', 'metric_key' => 'homework_completion', 'on_track_min' => 80, 'at_risk_min' => 60, 'off_track_min' => 0],

            // Attendance
            ['metric_category' => 'attendance', 'metric_key' => 'attendance_rate', 'on_track_min' => 95, 'at_risk_min' => 90, 'off_track_min' => 0],
            ['metric_category' => 'attendance', 'metric_key' => 'absences', 'on_track_min' => 3, 'at_risk_min' => 7, 'off_track_min' => 100, 'invert_scale' => true],

            // Behavior
            ['metric_category' => 'behavior', 'metric_key' => 'behavior_score', 'on_track_min' => 80, 'at_risk_min' => 60, 'off_track_min' => 0],
            ['metric_category' => 'behavior', 'metric_key' => 'discipline_incidents', 'on_track_min' => 0, 'at_risk_min' => 2, 'off_track_min' => 100, 'invert_scale' => true],

            // Wellness
            ['metric_category' => 'wellness', 'metric_key' => 'wellness_score', 'on_track_min' => 70, 'at_risk_min' => 50, 'off_track_min' => 0],
            ['metric_category' => 'wellness', 'metric_key' => 'emotional_wellbeing', 'on_track_min' => 70, 'at_risk_min' => 50, 'off_track_min' => 0],

            // Engagement
            ['metric_category' => 'engagement', 'metric_key' => 'engagement_score', 'on_track_min' => 70, 'at_risk_min' => 50, 'off_track_min' => 0],

            // Life Skills
            ['metric_category' => 'life_skills', 'metric_key' => 'life_skills_score', 'on_track_min' => 70, 'at_risk_min' => 50, 'off_track_min' => 0],
        ];

        foreach ($thresholds as $threshold) {
            MetricThreshold::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'metric_category' => $threshold['metric_category'],
                    'metric_key' => $threshold['metric_key'],
                ],
                array_merge($threshold, [
                    'org_id' => $orgId,
                    'active' => true,
                ])
            );
        }
    }

    private function createStudentMetrics(Student $student, int $orgId, ?int $userId): void
    {
        $schoolYear = $this->getCurrentSchoolYear();
        $baseRiskMultiplier = match ($student->risk_level) {
            'good' => 1.0,
            'low' => 0.8,
            'high' => 0.6,
            default => 0.7,
        };

        // Generate 12 months of historical data
        for ($monthsAgo = 12; $monthsAgo >= 0; $monthsAgo--) {
            $date = Carbon::now()->subMonths($monthsAgo);
            $quarter = $this->getQuarterFromDate($date);

            // Add some variance per month
            $monthVariance = (rand(-10, 10) / 100);
            $multiplier = $baseRiskMultiplier + $monthVariance;

            // GPA (0-4.0)
            $gpa = min(4.0, max(0, 4.0 * $multiplier + (rand(-20, 20) / 100)));
            $this->createMetric($student, $orgId, 'academics', 'gpa', $gpa, $date, $schoolYear, $quarter, $userId);

            // Wellness Score (0-100)
            $wellness = min(100, max(0, 100 * $multiplier + rand(-10, 10)));
            $this->createMetric($student, $orgId, 'wellness', 'wellness_score', $wellness, $date, $schoolYear, $quarter, $userId);

            // Emotional Well-Being (0-100)
            $emotional = min(100, max(0, 100 * $multiplier + rand(-15, 15)));
            $this->createMetric($student, $orgId, 'wellness', 'emotional_wellbeing', $emotional, $date, $schoolYear, $quarter, $userId);

            // Engagement Score (0-100)
            $engagement = min(100, max(0, 100 * $multiplier + rand(-10, 10)));
            $this->createMetric($student, $orgId, 'engagement', 'engagement_score', $engagement, $date, $schoolYear, $quarter, $userId);

            // Plan Progress (0-100)
            $progress = min(100, max(0, (12 - $monthsAgo) * 8 * $multiplier));
            $this->createMetric($student, $orgId, 'academics', 'plan_progress', $progress, $date, $schoolYear, $quarter, $userId);

            // Attendance Rate (0-100)
            $attendance = min(100, max(70, 100 * $multiplier + rand(-5, 5)));
            $this->createMetric($student, $orgId, 'attendance', 'attendance_rate', $attendance, $date, $schoolYear, $quarter, $userId);

            // Behavior Score (0-100)
            $behavior = min(100, max(0, 100 * $multiplier + rand(-10, 10)));
            $this->createMetric($student, $orgId, 'behavior', 'behavior_score', $behavior, $date, $schoolYear, $quarter, $userId);

            // Life Skills Score (0-100)
            $lifeSkills = min(100, max(0, 100 * $multiplier + rand(-10, 10)));
            $this->createMetric($student, $orgId, 'life_skills', 'life_skills_score', $lifeSkills, $date, $schoolYear, $quarter, $userId);
        }
    }

    private function createMetric(
        Student $student,
        int $orgId,
        string $category,
        string $key,
        float $value,
        Carbon $date,
        string $schoolYear,
        int $quarter,
        ?int $userId
    ): void {
        // Get threshold to calculate status
        $threshold = MetricThreshold::where('org_id', $orgId)
            ->where('metric_category', $category)
            ->where('metric_key', $key)
            ->first();

        $status = $threshold ? $threshold->calculateStatus($value) : null;

        ContactMetric::create([
            'org_id' => $orgId,
            'contact_type' => Student::class,
            'contact_id' => $student->id,
            'metric_category' => $category,
            'metric_key' => $key,
            'numeric_value' => round($value, 2),
            'normalized_score' => $this->normalizeScore($value, $key),
            'status' => $status,
            'source_type' => ContactMetric::SOURCE_CALCULATED,
            'period_start' => $date->copy()->startOfMonth(),
            'period_end' => $date->copy()->endOfMonth(),
            'period_type' => 'monthly',
            'school_year' => $schoolYear,
            'quarter' => $quarter,
            'recorded_by_user_id' => $userId,
            'recorded_at' => $date,
        ]);
    }

    private function createStudentNotes(Student $student, int $orgId, ?int $userId): void
    {
        if (!$userId) {
            return;
        }

        $noteTypes = ['general', 'follow_up', 'concern', 'milestone'];
        $sampleNotes = [
            'general' => [
                'Checked in with student today. They seem to be adjusting well to the new semester.',
                'Student expressed interest in joining the debate team.',
                'Parent-teacher conference scheduled for next week.',
                'Student completed extra credit assignment on time.',
            ],
            'follow_up' => [
                'Following up on attendance issues from last month. Student reports transportation problems.',
                'Need to follow up on tutoring recommendation.',
                'Check back on college application progress.',
            ],
            'concern' => [
                'Student seems withdrawn lately. Will monitor and follow up.',
                'Grades have dropped in math class. Recommended tutoring.',
                'Attendance has been inconsistent this quarter.',
            ],
            'milestone' => [
                'Student achieved Honor Roll status this quarter!',
                'Completed all required community service hours.',
                'Successfully presented at the science fair.',
            ],
        ];

        // Create 2-5 random notes per student
        $numNotes = rand(2, 5);
        for ($i = 0; $i < $numNotes; $i++) {
            $noteType = $noteTypes[array_rand($noteTypes)];
            $content = $sampleNotes[$noteType][array_rand($sampleNotes[$noteType])];

            ContactNote::create([
                'org_id' => $orgId,
                'contact_type' => Student::class,
                'contact_id' => $student->id,
                'note_type' => $noteType,
                'content' => $content,
                'is_private' => rand(0, 10) < 2, // 20% private
                'visibility' => 'organization',
                'created_by' => $userId,
                'created_at' => Carbon::now()->subDays(rand(1, 90)),
            ]);
        }
    }

    private function normalizeScore(float $value, string $key): float
    {
        // Normalize to 0-100 scale based on metric type
        return match ($key) {
            'gpa' => ($value / 4.0) * 100,
            default => min(100, max(0, $value)),
        };
    }

    private function getCurrentSchoolYear(): string
    {
        $now = Carbon::now();
        $year = $now->month >= 8 ? $now->year : $now->year - 1;
        return $year . '-' . ($year + 1);
    }

    private function getQuarterFromDate(Carbon $date): int
    {
        $month = $date->month;
        // School year quarters: Q1 (Aug-Oct), Q2 (Nov-Jan), Q3 (Feb-Apr), Q4 (May-Jul)
        return match (true) {
            $month >= 8 && $month <= 10 => 1,
            $month >= 11 || $month <= 1 => 2,
            $month >= 2 && $month <= 4 => 3,
            default => 4,
        };
    }
}
