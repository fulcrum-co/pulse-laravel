<?php

namespace Database\Seeders;

use App\Models\MiniCourse;
use App\Models\MiniCourseEnrollment;
use App\Models\Organization;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class MiniCourseEnhancedSeeder extends Seeder
{
    /**
     * Create 12-15 courses with 80-100 enrollments.
     */
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) {
            $this->command->error('No school organization found!');
            return;
        }

        $admin = User::where('primary_role', 'admin')->where('org_id', $school->id)->first();
        $students = Student::where('org_id', $school->id)->get();

        if ($students->isEmpty()) {
            $this->command->error('No students found!');
            return;
        }

        $courses = $this->createCourses($school->id, $admin->id);
        $totalEnrollments = 0;

        foreach ($courses as $course) {
            $numEnrollments = rand(5, 8);
            $selectedStudents = $students->random(min($numEnrollments, $students->count()));

            foreach ($selectedStudents as $student) {
                $status = $this->weightedRandom(['completed' => 50, 'in_progress' => 30, 'not_started' => 20]);

                MiniCourseEnrollment::create([
                    'course_id' => $course->id,
                    'student_id' => $student->id,
                    'org_id' => $school->id,
                    'status' => $status,
                    'progress_percentage' => match($status) { 'completed' => 100, 'in_progress' => rand(20, 80), default => 0 },
                    'enrolled_at' => now()->subDays(rand(10, 90)),
                    'completed_at' => $status === 'completed' ? now()->subDays(rand(0, 30)) : null,
                ]);
                $totalEnrollments++;
            }
        }

        $this->command->info("Created {$courses->count()} courses with {$totalEnrollments} enrollments");
    }

    private function createCourses(int $orgId, int $adminId): \Illuminate\Support\Collection
    {
        $courseDefs = [
            ['title' => 'Stress Management Fundamentals', 'desc' => 'Learn effective stress coping strategies', 'course_type' => 'wellness', 'duration' => 120],
            ['title' => 'Study Skills Mastery', 'desc' => 'Comprehensive guide to effective studying', 'course_type' => 'academic', 'duration' => 180],
            ['title' => 'Social Skills Development', 'desc' => 'Building healthy relationships and communication', 'course_type' => 'social', 'duration' => 150],
            ['title' => 'Goal Setting & Achievement', 'desc' => 'Set and accomplish your goals', 'course_type' => 'life_skills', 'duration' => 90],
            ['title' => 'Mindfulness & Meditation', 'desc' => 'Introduction to mindfulness practices', 'course_type' => 'wellness', 'duration' => 100],
            ['title' => 'Time Management Mastery', 'desc' => 'Master your schedule and priorities', 'course_type' => 'life_skills', 'duration' => 110],
            ['title' => 'Building Resilience', 'desc' => 'Develop mental toughness and resilience', 'course_type' => 'wellness', 'duration' => 130],
            ['title' => 'Conflict Resolution Skills', 'desc' => 'Handle conflicts effectively', 'course_type' => 'social', 'duration' => 95],
            ['title' => 'Growth Mindset Development', 'desc' => 'Cultivate a growth-oriented mindset', 'course_type' => 'wellness', 'duration' => 105],
            ['title' => 'Career Exploration', 'desc' => 'Explore potential career paths', 'course_type' => 'career', 'duration' => 200],
            ['title' => 'Financial Literacy for Teens', 'desc' => 'Money management basics', 'course_type' => 'life_skills', 'duration' => 140],
            ['title' => 'Digital Citizenship & Safety', 'desc' => 'Navigate the digital world safely', 'course_type' => 'life_skills', 'duration' => 85],
            ['title' => 'Emotional Intelligence', 'desc' => 'Understand and manage your emotions', 'course_type' => 'wellness', 'duration' => 125],
        ];

        return collect($courseDefs)->map(fn($d) => MiniCourse::create([
            'org_id' => $orgId, 'title' => $d['title'], 'description' => $d['desc'],
            'course_type' => $d['course_type'], 'estimated_duration_minutes' => $d['duration'],
            'approval_status' => 'approved', 'published_at' => now(),
            'created_by' => $adminId, 'approved_by' => $adminId, 'approved_at' => now(),
        ]));
    }

    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $random = rand(1, $total);
        $sum = 0;
        foreach ($weights as $key => $weight) {
            $sum += $weight;
            if ($random <= $sum) return $key;
        }
        return array_key_first($weights);
    }
}
