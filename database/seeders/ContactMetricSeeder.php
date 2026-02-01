<?php

namespace Database\Seeders;

use App\Models\ContactMetric;
use App\Models\ContactNote;
use App\Models\ContactResourceSuggestion;
use App\Models\MetricThreshold;
use App\Models\Organization;
use App\Models\Resource;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ContactMetricSeeder extends Seeder
{
    private array $staffUsers = [];

    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) {
            $this->command->warn('No school organization found. Skipping ContactMetricSeeder.');

            return;
        }

        $students = Student::where('org_id', $school->id)->get();
        $this->staffUsers = User::where('org_id', $school->id)
            ->whereIn('primary_role', ['admin', 'teacher'])
            ->get()
            ->all();

        // Create default metric thresholds
        $this->createDefaultThresholds($school->id);

        // Create metrics for each student
        foreach ($students as $index => $student) {
            // Assign different trend patterns to make data interesting
            $trendPattern = $this->getTrendPattern($index, $student->risk_level);
            $this->createStudentMetrics($student, $school->id, $trendPattern);
            $this->createStudentNotes($student, $school->id);
            $this->createResourceSuggestions($student, $school->id);
        }

        $this->command->info('Created rich demo data for '.$students->count().' students.');
    }

    private function getTrendPattern(int $index, string $riskLevel): string
    {
        // Create variety in the data - some improving, some declining, some stable
        $patterns = match ($riskLevel) {
            'high' => ['declining', 'struggling', 'recovering', 'volatile'],
            'low' => ['improving', 'stable', 'slight_decline', 'recovering'],
            'good' => ['stable', 'excelling', 'slight_decline', 'improving'],
            default => ['stable', 'improving', 'declining', 'volatile'],
        };

        return $patterns[$index % count($patterns)];
    }

    private function createDefaultThresholds(int $orgId): void
    {
        $thresholds = [
            ['metric_category' => 'academics', 'metric_key' => 'gpa', 'on_track_min' => 3.0, 'at_risk_min' => 2.0, 'off_track_min' => 0],
            ['metric_category' => 'academics', 'metric_key' => 'homework_completion', 'on_track_min' => 80, 'at_risk_min' => 60, 'off_track_min' => 0],
            ['metric_category' => 'academics', 'metric_key' => 'plan_progress', 'on_track_min' => 70, 'at_risk_min' => 40, 'off_track_min' => 0],
            ['metric_category' => 'attendance', 'metric_key' => 'attendance_rate', 'on_track_min' => 95, 'at_risk_min' => 90, 'off_track_min' => 0],
            ['metric_category' => 'attendance', 'metric_key' => 'absences', 'on_track_min' => 3, 'at_risk_min' => 7, 'off_track_min' => 100, 'invert_scale' => true],
            ['metric_category' => 'behavior', 'metric_key' => 'behavior_score', 'on_track_min' => 80, 'at_risk_min' => 60, 'off_track_min' => 0],
            ['metric_category' => 'wellness', 'metric_key' => 'wellness_score', 'on_track_min' => 70, 'at_risk_min' => 50, 'off_track_min' => 0],
            ['metric_category' => 'wellness', 'metric_key' => 'emotional_wellbeing', 'on_track_min' => 70, 'at_risk_min' => 50, 'off_track_min' => 0],
            ['metric_category' => 'engagement', 'metric_key' => 'engagement_score', 'on_track_min' => 70, 'at_risk_min' => 50, 'off_track_min' => 0],
            ['metric_category' => 'life_skills', 'metric_key' => 'life_skills_score', 'on_track_min' => 70, 'at_risk_min' => 50, 'off_track_min' => 0],
        ];

        foreach ($thresholds as $threshold) {
            MetricThreshold::updateOrCreate(
                [
                    'org_id' => $orgId,
                    'metric_category' => $threshold['metric_category'],
                    'metric_key' => $threshold['metric_key'],
                ],
                array_merge($threshold, ['org_id' => $orgId, 'active' => true])
            );
        }
    }

    private function createStudentMetrics(Student $student, int $orgId, string $trendPattern): void
    {
        $userId = $this->getRandomStaffId();

        // Base values based on risk level
        $baseValues = match ($student->risk_level) {
            'good' => ['gpa' => 3.5, 'wellness' => 82, 'emotional' => 78, 'engagement' => 85, 'attendance' => 97, 'behavior' => 90, 'life_skills' => 80],
            'low' => ['gpa' => 2.8, 'wellness' => 65, 'emotional' => 60, 'engagement' => 62, 'attendance' => 92, 'behavior' => 72, 'life_skills' => 65],
            'high' => ['gpa' => 2.0, 'wellness' => 48, 'emotional' => 45, 'engagement' => 40, 'attendance' => 85, 'behavior' => 55, 'life_skills' => 50],
            default => ['gpa' => 2.5, 'wellness' => 60, 'emotional' => 58, 'engagement' => 55, 'attendance' => 90, 'behavior' => 65, 'life_skills' => 60],
        };

        // Generate 18 months of data for richer charts
        for ($monthsAgo = 18; $monthsAgo >= 0; $monthsAgo--) {
            $date = Carbon::now()->subMonths($monthsAgo);
            $schoolYear = $this->getSchoolYearFromDate($date);
            $quarter = $this->getQuarterFromDate($date);

            // Apply trend pattern
            $trendMultiplier = $this->getTrendMultiplier($trendPattern, $monthsAgo, 18);

            // Add realistic noise
            $noise = (rand(-8, 8) / 100);

            $values = [
                'gpa' => $this->clamp($baseValues['gpa'] * $trendMultiplier + $noise * 0.3, 0, 4.0),
                'wellness_score' => $this->clamp($baseValues['wellness'] * $trendMultiplier + $noise * 10, 0, 100),
                'emotional_wellbeing' => $this->clamp($baseValues['emotional'] * $trendMultiplier + $noise * 12, 0, 100),
                'engagement_score' => $this->clamp($baseValues['engagement'] * $trendMultiplier + $noise * 10, 0, 100),
                'attendance_rate' => $this->clamp($baseValues['attendance'] * $trendMultiplier + $noise * 3, 70, 100),
                'behavior_score' => $this->clamp($baseValues['behavior'] * $trendMultiplier + $noise * 8, 0, 100),
                'life_skills_score' => $this->clamp($baseValues['life_skills'] * $trendMultiplier + $noise * 8, 0, 100),
            ];

            // Plan progress should increase over time
            $planProgress = min(100, max(0, ((18 - $monthsAgo) / 18) * 100 * $trendMultiplier));

            $this->createMetric($student, $orgId, 'academics', 'gpa', $values['gpa'], $date, $schoolYear, $quarter, $userId);
            $this->createMetric($student, $orgId, 'academics', 'plan_progress', $planProgress, $date, $schoolYear, $quarter, $userId);
            $this->createMetric($student, $orgId, 'wellness', 'wellness_score', $values['wellness_score'], $date, $schoolYear, $quarter, $userId);
            $this->createMetric($student, $orgId, 'wellness', 'emotional_wellbeing', $values['emotional_wellbeing'], $date, $schoolYear, $quarter, $userId);
            $this->createMetric($student, $orgId, 'engagement', 'engagement_score', $values['engagement_score'], $date, $schoolYear, $quarter, $userId);
            $this->createMetric($student, $orgId, 'attendance', 'attendance_rate', $values['attendance_rate'], $date, $schoolYear, $quarter, $userId);
            $this->createMetric($student, $orgId, 'behavior', 'behavior_score', $values['behavior_score'], $date, $schoolYear, $quarter, $userId);
            $this->createMetric($student, $orgId, 'life_skills', 'life_skills_score', $values['life_skills_score'], $date, $schoolYear, $quarter, $userId);
        }
    }

    private function getTrendMultiplier(string $pattern, int $monthsAgo, int $totalMonths): float
    {
        $progress = 1 - ($monthsAgo / $totalMonths); // 0 at start, 1 at end

        return match ($pattern) {
            'improving' => 0.85 + ($progress * 0.20),        // Starts lower, improves over time
            'declining' => 1.05 - ($progress * 0.15),        // Starts higher, declines
            'recovering' => 0.80 + (sin($progress * pi()) * 0.15) + ($progress * 0.10), // Dips then recovers
            'excelling' => 1.0 + ($progress * 0.08),         // Consistently improving
            'stable' => 1.0 + (sin($progress * 4 * pi()) * 0.03), // Minor fluctuations
            'struggling' => 0.90 - ($progress * 0.05) + (sin($progress * 3 * pi()) * 0.08), // Trending down with volatility
            'slight_decline' => 1.02 - ($progress * 0.08),   // Gradual decline
            'volatile' => 1.0 + (sin($progress * 6 * pi()) * 0.12), // Large swings
            default => 1.0,
        };
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return max($min, min($max, $value));
    }

    private function createMetric(Student $student, int $orgId, string $category, string $key, float $value, Carbon $date, string $schoolYear, int $quarter, ?int $userId): void
    {
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
            'source_type' => $this->getRandomSource(),
            'period_start' => $date->copy()->startOfMonth(),
            'period_end' => $date->copy()->endOfMonth(),
            'period_type' => 'monthly',
            'school_year' => $schoolYear,
            'quarter' => $quarter,
            'recorded_by_user_id' => $userId,
            'recorded_at' => $date,
        ]);
    }

    private function createStudentNotes(Student $student, int $orgId): void
    {
        $studentName = $student->user?->first_name ?? 'Student';

        // Rich, realistic notes based on risk level
        $noteTemplates = [
            'general' => [
                "Had a productive check-in with {$studentName} today. Discussed upcoming assignments and goals for the quarter.",
                "Met with {$studentName} during advisory. They expressed interest in the upcoming career fair.",
                "{$studentName} asked about tutoring resources for math. Provided information about after-school help.",
                "Observed {$studentName} helping a classmate during group work. Great collaborative skills.",
                "Quick hallway chat with {$studentName} - they mentioned enjoying the new science unit.",
                "{$studentName} submitted college interest survey. Interested in engineering programs.",
                "Discussed course selection for next year with {$studentName}. Considering AP classes.",
                "{$studentName} participated actively in today's class discussion about career pathways.",
            ],
            'follow_up' => [
                "Following up on {$studentName}'s attendance last week. Family confirmed illness, all excused.",
                "Check-in with {$studentName} about missing assignments. Created plan to catch up by Friday.",
                "Follow-up meeting with {$studentName} and parent regarding grade concerns. Set bi-weekly check-ins.",
                "Checked in about the tutoring referral - {$studentName} has attended two sessions so far.",
                "Following up on {$studentName}'s interest in joining the robotics club. Connected with advisor.",
                "Post-conference follow-up: {$studentName} is using the planner strategies we discussed.",
            ],
            'concern' => [
                "{$studentName} seems more withdrawn than usual in class this week. Will continue to monitor.",
                "Noticed {$studentName} struggling to stay focused. Recommend checking in about sleep/wellness.",
                "Math teacher reported {$studentName} has missing work in their class as well. Need coordinated approach.",
                "{$studentName} mentioned stress about upcoming tests. Discussed study strategies and resources.",
                "Attendance pattern shows {$studentName} missing more Mondays. May need family outreach.",
                "Peer conflict reported involving {$studentName}. Counselor meeting scheduled.",
            ],
            'milestone' => [
                "Congratulations to {$studentName} - made the Honor Roll this quarter!",
                "{$studentName} completed their community service requirement for graduation.",
                "Excellent progress! {$studentName} improved their GPA by 0.3 points this semester.",
                "{$studentName} was selected for the district's student leadership program.",
                "Perfect attendance this month for {$studentName}. Great improvement!",
                "{$studentName} passed all state assessments with proficient or above scores.",
                "{$studentName} completed their first successful job interview as part of career readiness program.",
                "{$studentName} received recognition for outstanding improvement in behavior.",
            ],
        ];

        // More notes for higher-risk students (they need more attention)
        $numNotes = match ($student->risk_level) {
            'high' => rand(8, 12),
            'low' => rand(5, 8),
            'good' => rand(3, 6),
            default => rand(4, 7),
        };

        // Weight note types based on risk level
        $typeWeights = match ($student->risk_level) {
            'high' => ['general' => 2, 'follow_up' => 4, 'concern' => 4, 'milestone' => 1],
            'low' => ['general' => 3, 'follow_up' => 3, 'concern' => 2, 'milestone' => 2],
            'good' => ['general' => 4, 'follow_up' => 1, 'concern' => 1, 'milestone' => 4],
            default => ['general' => 3, 'follow_up' => 2, 'concern' => 2, 'milestone' => 2],
        };

        $weightedTypes = [];
        foreach ($typeWeights as $type => $weight) {
            for ($i = 0; $i < $weight; $i++) {
                $weightedTypes[] = $type;
            }
        }

        for ($i = 0; $i < $numNotes; $i++) {
            $noteType = $weightedTypes[array_rand($weightedTypes)];
            $templates = $noteTemplates[$noteType];
            $content = $templates[array_rand($templates)];
            $userId = $this->getRandomStaffId();

            ContactNote::create([
                'org_id' => $orgId,
                'contact_type' => Student::class,
                'contact_id' => $student->id,
                'note_type' => $noteType,
                'content' => $content,
                'is_private' => rand(1, 10) <= 2,
                'visibility' => rand(1, 10) <= 8 ? 'organization' : 'team',
                'created_by' => $userId,
                'created_at' => Carbon::now()->subDays(rand(1, 180)),
            ]);
        }
    }

    private function createResourceSuggestions(Student $student, int $orgId): void
    {
        $resources = Resource::where('org_id', $orgId)->where('active', true)->get();
        if ($resources->isEmpty()) {
            return;
        }

        // Higher risk = more suggestions
        $numSuggestions = match ($student->risk_level) {
            'high' => rand(3, 5),
            'low' => rand(1, 3),
            'good' => rand(0, 2),
            default => rand(1, 2),
        };

        $selectedResources = $resources->random(min($numSuggestions, $resources->count()));

        foreach ($selectedResources as $resource) {
            // Skip if suggestion already exists for this contact/resource combo
            $exists = ContactResourceSuggestion::where('contact_type', Student::class)
                ->where('contact_id', $student->id)
                ->where('resource_id', $resource->id)
                ->exists();

            if ($exists) {
                continue;
            }

            $source = ['manual', 'rule_based', 'ai_recommendation'][rand(0, 2)];
            $status = ['pending', 'pending', 'pending', 'accepted', 'declined'][rand(0, 4)];

            $suggestion = ContactResourceSuggestion::create([
                'org_id' => $orgId,
                'contact_type' => Student::class,
                'contact_id' => $student->id,
                'resource_id' => $resource->id,
                'suggestion_source' => $source,
                'relevance_score' => $source === 'ai_recommendation' ? rand(65, 98) : null,
                'ai_rationale' => $source === 'ai_recommendation' ? $this->getAiRationale($resource, $student) : null,
                'status' => $status,
                'created_at' => Carbon::now()->subDays(rand(1, 60)),
            ]);

            if ($status !== 'pending') {
                $suggestion->update([
                    'reviewed_by' => $this->getRandomStaffId(),
                    'reviewed_at' => Carbon::now()->subDays(rand(0, 30)),
                    'review_notes' => $status === 'accepted' ? 'Good fit for student needs.' : 'Not appropriate at this time.',
                ]);
            }
        }
    }

    private function getAiRationale(Resource $resource, Student $student): string
    {
        $rationales = [
            "Based on {$student->user?->first_name}'s recent academic performance trends, this resource addresses key skill gaps.",
            "Student's engagement patterns suggest this type of intervention would be beneficial.",
            "Matches student's learning style and current support needs identified in recent assessments.",
            'Recommended based on similar successful interventions with comparable student profiles.',
            'Addresses specific areas flagged in recent wellness check-in responses.',
        ];

        return $rationales[array_rand($rationales)];
    }

    private function getRandomStaffId(): ?int
    {
        if (empty($this->staffUsers)) {
            return null;
        }

        return $this->staffUsers[array_rand($this->staffUsers)]->id;
    }

    private function getRandomSource(): string
    {
        $sources = [
            ContactMetric::SOURCE_SIS_API,
            ContactMetric::SOURCE_SIS_API,
            ContactMetric::SOURCE_SURVEY,
            ContactMetric::SOURCE_CALCULATED,
            ContactMetric::SOURCE_MANUAL,
        ];

        return $sources[array_rand($sources)];
    }

    private function normalizeScore(float $value, string $key): float
    {
        return match ($key) {
            'gpa' => ($value / 4.0) * 100,
            default => min(100, max(0, $value)),
        };
    }

    private function getSchoolYearFromDate(Carbon $date): string
    {
        $year = $date->month >= 8 ? $date->year : $date->year - 1;

        return $year.'-'.($year + 1);
    }

    private function getQuarterFromDate(Carbon $date): int
    {
        $month = $date->month;

        return match (true) {
            $month >= 8 && $month <= 10 => 1,
            $month >= 11 || $month <= 1 => 2,
            $month >= 2 && $month <= 4 => 3,
            default => 4,
        };
    }
}
