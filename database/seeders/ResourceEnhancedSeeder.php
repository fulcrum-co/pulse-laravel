<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResourceEnhancedSeeder extends Seeder
{
    /**
     * Create 25-30 resources with 75-100 assignments.
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

        $resources = $this->createResources($school->id, $admin->id);
        $totalAssignments = 0;
        $startDate = now()->subDays(90);

        foreach ($resources as $resource) {
            $targetStudents = $students->filter(fn($s) => in_array($s->risk_level, $resource->target_risk_levels ?? ['good', 'low', 'high']));
            $numAssignments = rand(2, 5);
            $selectedStudents = $targetStudents->random(min($numAssignments, $targetStudents->count()));

            foreach ($selectedStudents as $student) {
                $assignedAt = $startDate->copy()->addDays(rand(0, 90));
                $status = $this->weightedRandom(['completed' => 70, 'in_progress' => 20, 'assigned' => 10]);

                ResourceAssignment::create([
                    'resource_id' => $resource->id,
                    'org_id' => $school->id,
                    'student_id' => $student->id,
                    'assigned_by' => $admin->id,
                    'status' => $status,
                    'assigned_at' => $assignedAt,
                    'started_at' => $status !== 'assigned' ? $assignedAt->copy()->addHours(rand(1, 72)) : null,
                    'completed_at' => $status === 'completed' ? $assignedAt->copy()->addDays(rand(1, 14)) : null,
                    'progress_percentage' => match($status) { 'completed' => 100, 'in_progress' => rand(20, 80), default => 0 },
                    'created_at' => $assignedAt,
                    'updated_at' => $assignedAt->copy()->addDays(rand(0, 14)),
                ]);
                $totalAssignments++;
            }
        }

        $this->command->info("Created {$resources->count()} resources with {$totalAssignments} assignments");
    }

    private function createResources(int $orgId, int $adminId): \Illuminate\Support\Collection
    {
        $resourceDefs = [
            ['title' => 'Managing Test Anxiety', 'desc' => 'Effective strategies to manage anxiety before and during tests', 'type' => 'article', 'category' => 'anxiety', 'tags' => ['stress', 'academics'], 'duration' => 10, 'risk' => ['low', 'high']],
            ['title' => 'Study Skills Workshop', 'desc' => 'Interactive workshop on effective study techniques', 'type' => 'activity', 'category' => 'academic', 'tags' => ['studying', 'organization'], 'duration' => 45, 'risk' => ['good', 'low']],
            ['title' => 'Note-Taking Strategies', 'desc' => 'Different note-taking methods for better retention', 'type' => 'video', 'category' => 'academic', 'tags' => ['study skills'], 'duration' => 20, 'risk' => ['good', 'low', 'high']],
            ['title' => 'Math Tutoring Resources', 'desc' => 'Online resources and practice materials', 'type' => 'link', 'category' => 'academic', 'tags' => ['math', 'tutoring'], 'duration' => 30, 'risk' => ['low', 'high']],
            ['title' => 'Mindfulness for Students', 'desc' => 'Guided introduction to mindfulness practices', 'type' => 'video', 'category' => 'stress', 'tags' => ['mindfulness', 'wellness'], 'duration' => 15, 'risk' => ['good', 'low', 'high']],
            ['title' => 'Building Healthy Sleep Habits', 'desc' => 'Tips for improving sleep quality', 'type' => 'article', 'category' => 'wellness', 'tags' => ['sleep', 'health'], 'duration' => 8, 'risk' => ['good', 'low']],
            ['title' => 'Stress Management Toolkit', 'desc' => 'Practical tools for managing daily stress', 'type' => 'worksheet', 'category' => 'stress', 'tags' => ['coping', 'self-care'], 'duration' => 25, 'risk' => ['low', 'high']],
            ['title' => 'Building Self-Esteem', 'desc' => 'Exercises to build confidence', 'type' => 'worksheet', 'category' => 'wellness', 'tags' => ['confidence', 'growth'], 'duration' => 25, 'risk' => ['low', 'high']],
            ['title' => 'Dealing with Social Pressure', 'desc' => 'Navigating peer pressure', 'type' => 'worksheet', 'category' => 'social', 'tags' => ['peer pressure'], 'duration' => 20, 'risk' => ['low', 'high']],
            ['title' => 'Communication Skills for Teens', 'desc' => 'Express yourself effectively', 'type' => 'video', 'category' => 'social', 'tags' => ['communication'], 'duration' => 30, 'risk' => ['good', 'low', 'high']],
            ['title' => 'Conflict Resolution Guide', 'desc' => 'Resolve conflicts peacefully', 'type' => 'article', 'category' => 'social', 'tags' => ['conflict'], 'duration' => 15, 'risk' => ['low', 'high']],
            ['title' => 'Time Management Essentials', 'desc' => 'Manage your time effectively', 'type' => 'activity', 'category' => 'life_skills', 'tags' => ['time management'], 'duration' => 35, 'risk' => ['good', 'low']],
            ['title' => 'Goal Setting Workshop', 'desc' => 'Set and achieve meaningful goals', 'type' => 'activity', 'category' => 'life_skills', 'tags' => ['goals'], 'duration' => 40, 'risk' => ['good', 'low', 'high']],
            ['title' => 'Financial Literacy Basics', 'desc' => 'Budgeting and financial planning', 'type' => 'video', 'category' => 'life_skills', 'tags' => ['finance'], 'duration' => 25, 'risk' => ['good', 'low']],
            ['title' => 'Crisis Support Hotlines', 'desc' => 'Contact information for immediate support', 'type' => 'document', 'category' => 'crisis', 'tags' => ['emergency'], 'duration' => 5, 'risk' => ['high']],
            ['title' => 'When to Ask for Help', 'desc' => 'Recognizing when you need support', 'type' => 'article', 'category' => 'crisis', 'tags' => ['help', 'support'], 'duration' => 12, 'risk' => ['low', 'high']],
            ['title' => 'Coping with Grief and Loss', 'desc' => 'Navigating difficult emotions', 'type' => 'article', 'category' => 'crisis', 'tags' => ['grief'], 'duration' => 18, 'risk' => ['low', 'high']],
            ['title' => 'College Application Checklist', 'desc' => 'Guide to college application process', 'type' => 'document', 'category' => 'career', 'tags' => ['college'], 'duration' => 20, 'risk' => ['good', 'low']],
            ['title' => 'Career Exploration Activities', 'desc' => 'Discover careers matching your interests', 'type' => 'activity', 'category' => 'career', 'tags' => ['career'], 'duration' => 45, 'risk' => ['good', 'low']],
            ['title' => 'Resume Building Workshop', 'desc' => 'Create a professional resume', 'type' => 'activity', 'category' => 'career', 'tags' => ['resume'], 'duration' => 50, 'risk' => ['good', 'low']],
            ['title' => 'Why Attendance Matters', 'desc' => 'Impact of attendance on success', 'type' => 'article', 'category' => 'attendance', 'tags' => ['attendance'], 'duration' => 10, 'risk' => ['low', 'high']],
            ['title' => 'Overcoming School Avoidance', 'desc' => 'Strategies for attendance struggles', 'type' => 'worksheet', 'category' => 'attendance', 'tags' => ['engagement'], 'duration' => 30, 'risk' => ['high']],
            ['title' => 'Anger Management Techniques', 'desc' => 'Healthy ways to manage anger', 'type' => 'video', 'category' => 'behavior', 'tags' => ['anger'], 'duration' => 22, 'risk' => ['low', 'high']],
            ['title' => 'Building Positive Habits', 'desc' => 'Create and maintain healthy routines', 'type' => 'article', 'category' => 'behavior', 'tags' => ['habits'], 'duration' => 15, 'risk' => ['good', 'low', 'high']],
            ['title' => 'Coping with Change', 'desc' => 'Dealing with life transitions', 'type' => 'article', 'category' => 'stress', 'tags' => ['transitions'], 'duration' => 12, 'risk' => ['low', 'high']],
            ['title' => 'New School Transition Guide', 'desc' => 'Adjusting to new school environment', 'type' => 'document', 'category' => 'transition', 'tags' => ['transition'], 'duration' => 18, 'risk' => ['low', 'high']],
            ['title' => 'Healthy Relationships Guide', 'desc' => 'What makes a healthy friendship', 'type' => 'article', 'category' => 'social', 'tags' => ['relationships'], 'duration' => 20, 'risk' => ['good', 'low', 'high']],
            ['title' => 'Digital Citizenship', 'desc' => 'Being responsible and safe online', 'type' => 'video', 'category' => 'life_skills', 'tags' => ['technology'], 'duration' => 25, 'risk' => ['good', 'low']],
            ['title' => 'Growth Mindset Workshop', 'desc' => 'Embrace challenges and learning', 'type' => 'activity', 'category' => 'wellness', 'tags' => ['mindset'], 'duration' => 40, 'risk' => ['good', 'low', 'high']],
        ];

        return collect($resourceDefs)->map(fn($d) => Resource::create([
            'org_id' => $orgId, 'title' => $d['title'], 'description' => $d['desc'],
            'resource_type' => $d['type'], 'category' => $d['category'], 'tags' => $d['tags'],
            'estimated_duration_minutes' => $d['duration'], 'target_risk_levels' => $d['risk'],
            'active' => true, 'created_by' => $adminId,
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
