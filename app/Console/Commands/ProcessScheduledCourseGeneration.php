<?php

namespace App\Console\Commands;

use App\Models\MiniCourse;
use App\Models\Organization;
use App\Models\OrganizationSettings;
use App\Models\Student;
use App\Services\CourseOrchestrator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledCourseGeneration extends Command
{
    protected $signature = 'courses:generate-scheduled
                            {--org= : Process only a specific organization ID}
                            {--dry-run : Show what would be generated without actually generating}';

    protected $description = 'Process scheduled auto-course generation for organizations';

    public function handle(CourseOrchestrator $orchestrator): int
    {
        $orgId = $this->option('org');
        $dryRun = $this->option('dry-run');

        $query = Organization::where('active', true);

        if ($orgId) {
            $query->where('id', $orgId);
        }

        $organizations = $query->get();
        $totalGenerated = 0;

        foreach ($organizations as $org) {
            $settings = OrganizationSettings::forOrganization($org->id);

            // Check if this org should run now
            if (! $settings->shouldRunAutoCourseGeneration()) {
                continue;
            }

            $this->info("Processing organization: {$org->org_name} (ID: {$org->id})");

            $generated = $this->processOrganization($org, $settings, $orchestrator, $dryRun);
            $totalGenerated += $generated;
        }

        $this->info("Total courses generated: {$totalGenerated}");

        return Command::SUCCESS;
    }

    protected function processOrganization(
        Organization $org,
        OrganizationSettings $settings,
        CourseOrchestrator $orchestrator,
        bool $dryRun
    ): int {
        $autoSettings = $settings->getAutoCourseSettings();

        // Get target criteria
        $targetRiskLevels = $autoSettings['target_criteria']['risk_levels'] ?? ['high', 'moderate'];
        $targetGrades = $autoSettings['target_criteria']['grades'] ?? [];
        $missingCoursesOnly = $autoSettings['target_criteria']['missing_courses_only'] ?? true;
        $maxCoursesPerDay = $autoSettings['max_courses_per_day'] ?? 50;

        // Count how many we've already generated today
        $generatedToday = MiniCourse::where('org_id', $org->id)
            ->where('generation_trigger', MiniCourse::TRIGGER_SCHEDULED)
            ->whereDate('auto_generated_at', today())
            ->count();

        $remaining = max(0, $maxCoursesPerDay - $generatedToday);

        if ($remaining === 0) {
            $this->warn("  Max courses per day reached for {$org->org_name}");

            return 0;
        }

        // Find eligible students
        $query = Student::where('org_id', $org->id)
            ->where('active', true)
            ->whereIn('risk_level', $targetRiskLevels);

        if (! empty($targetGrades)) {
            $query->whereIn('grade_level', $targetGrades);
        }

        // If only targeting students without recent courses
        if ($missingCoursesOnly) {
            $query->whereDoesntHave('enrollments', function ($q) {
                $q->where('enrolled_at', '>=', now()->subDays(30));
            });
        }

        $students = $query->limit($remaining)->get();

        if ($students->isEmpty()) {
            $this->info("  No eligible students found for {$org->org_name}");

            return 0;
        }

        $this->info("  Found {$students->count()} eligible students");

        $generated = 0;

        foreach ($students as $student) {
            if ($dryRun) {
                $this->line("    [DRY RUN] Would generate course for: {$student->first_name} {$student->last_name}");

                continue;
            }

            try {
                $topic = $this->inferTopicForStudent($student);

                $course = $orchestrator->generateCourse([
                    'topic' => $topic,
                    'orgId' => $org->id,
                    'targetGrades' => [$student->grade_level],
                    'targetRiskLevels' => [$student->risk_level],
                    'targetDurationMinutes' => $autoSettings['default_duration_minutes'] ?? 30,
                    'courseType' => $autoSettings['default_course_type'] ?? MiniCourse::TYPE_INTERVENTION,
                    'createdBy' => null, // System-generated
                ]);

                // Update with scheduled trigger info
                $course->update([
                    'generation_trigger' => MiniCourse::TRIGGER_SCHEDULED,
                    'approval_status' => $autoSettings['require_moderation']
                        ? MiniCourse::APPROVAL_PENDING
                        : MiniCourse::APPROVAL_APPROVED,
                    'target_entity_type' => 'student',
                    'target_entity_id' => $student->id,
                ]);

                // Auto-enroll if enabled
                if ($autoSettings['auto_enroll']) {
                    $this->enrollStudent($student, $course);
                }

                $generated++;
                $this->info("    Generated course for {$student->first_name} {$student->last_name}: {$course->title}");

                Log::info('Scheduled course generated', [
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                    'org_id' => $org->id,
                ]);
            } catch (\Exception $e) {
                $this->error("    Failed to generate for {$student->first_name}: {$e->getMessage()}");

                Log::error('Scheduled course generation failed', [
                    'student_id' => $student->id,
                    'org_id' => $org->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $generated;
    }

    protected function inferTopicForStudent(Student $student): string
    {
        $domainScores = $student->domain_risk_scores ?? [];

        // Find highest risk domain
        $highestDomain = null;
        $highestScore = 0;

        foreach ($domainScores as $domain => $score) {
            if ($score > $highestScore) {
                $highestScore = $score;
                $highestDomain = $domain;
            }
        }

        $topicMap = [
            'anxiety' => 'Managing anxiety and building coping skills',
            'depression' => 'Building emotional resilience and positive mindset',
            'stress' => 'Stress management and self-care strategies',
            'social' => 'Building healthy relationships and social skills',
            'academic' => 'Study skills and academic success strategies',
            'behavioral' => 'Self-regulation and positive behavior strategies',
            'attendance' => 'Motivation and engagement in learning',
        ];

        if ($highestDomain && isset($topicMap[$highestDomain])) {
            return $topicMap[$highestDomain];
        }

        return match ($student->risk_level ?? 'moderate') {
            'high', 'crisis' => 'Building coping skills and getting support',
            'moderate' => 'Skill-building for personal growth',
            default => 'Wellness and personal development',
        };
    }

    protected function enrollStudent(Student $student, MiniCourse $course): void
    {
        if (class_exists(\App\Models\MiniCourseEnrollment::class)) {
            \App\Models\MiniCourseEnrollment::firstOrCreate([
                'mini_course_id' => $course->id,
                'student_id' => $student->id,
            ], [
                'enrolled_at' => now(),
                'status' => 'enrolled',
            ]);
        }
    }
}
