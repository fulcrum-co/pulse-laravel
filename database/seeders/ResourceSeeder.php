<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        $admin = User::where('primary_role', 'admin')->where('org_id', $school->id)->first();

        $resources = [
            [
                'title' => 'Managing Test Anxiety',
                'description' => 'Learn effective strategies to manage anxiety before and during tests.',
                'resource_type' => 'article',
                'category' => 'anxiety',
                'tags' => ['stress', 'academics', 'self-help'],
                'estimated_duration_minutes' => 10,
                'target_risk_levels' => ['low', 'high'],
            ],
            [
                'title' => 'Mindfulness for Students',
                'description' => 'A guided introduction to mindfulness practices for teens.',
                'resource_type' => 'video',
                'category' => 'stress',
                'tags' => ['mindfulness', 'meditation', 'wellness'],
                'estimated_duration_minutes' => 15,
                'target_risk_levels' => ['good', 'low', 'high'],
            ],
            [
                'title' => 'Building Healthy Sleep Habits',
                'description' => 'Tips and strategies for improving sleep quality.',
                'resource_type' => 'article',
                'category' => 'wellness',
                'tags' => ['sleep', 'health', 'habits'],
                'estimated_duration_minutes' => 8,
                'target_risk_levels' => ['good', 'low'],
            ],
            [
                'title' => 'Dealing with Social Pressure',
                'description' => 'Understanding and navigating peer pressure in high school.',
                'resource_type' => 'worksheet',
                'category' => 'social',
                'tags' => ['peer pressure', 'social skills', 'decision making'],
                'estimated_duration_minutes' => 20,
                'target_risk_levels' => ['low', 'high'],
            ],
            [
                'title' => 'Study Skills Workshop',
                'description' => 'Interactive workshop on effective study techniques.',
                'resource_type' => 'activity',
                'category' => 'academic',
                'tags' => ['studying', 'organization', 'time management'],
                'estimated_duration_minutes' => 45,
                'target_risk_levels' => ['good', 'low'],
            ],
            [
                'title' => 'Coping with Change',
                'description' => 'Resources for dealing with major life transitions.',
                'resource_type' => 'article',
                'category' => 'stress',
                'tags' => ['transitions', 'coping', 'resilience'],
                'estimated_duration_minutes' => 12,
                'target_risk_levels' => ['low', 'high'],
            ],
            [
                'title' => 'Crisis Support Hotlines',
                'description' => 'Important contact information for immediate support.',
                'resource_type' => 'document',
                'category' => 'crisis',
                'tags' => ['emergency', 'support', 'hotlines'],
                'estimated_duration_minutes' => 5,
                'target_risk_levels' => ['high'],
            ],
            [
                'title' => 'Building Self-Esteem',
                'description' => 'Exercises and reflections to build confidence.',
                'resource_type' => 'worksheet',
                'category' => 'wellness',
                'tags' => ['self-esteem', 'confidence', 'growth'],
                'estimated_duration_minutes' => 25,
                'target_risk_levels' => ['low', 'high'],
            ],
        ];

        foreach ($resources as $resourceData) {
            Resource::create([
                'org_id' => $school->id,
                'title' => $resourceData['title'],
                'description' => $resourceData['description'],
                'resource_type' => $resourceData['resource_type'],
                'category' => $resourceData['category'],
                'tags' => $resourceData['tags'],
                'estimated_duration_minutes' => $resourceData['estimated_duration_minutes'],
                'target_risk_levels' => $resourceData['target_risk_levels'],
                'is_public' => false,
                'active' => true,
                'created_by' => $admin->id,
            ]);
        }

        // Assign some resources to high-risk students
        $highRiskStudents = Student::where('org_id', $school->id)->where('risk_level', 'high')->get();
        $supportResources = Resource::where('org_id', $school->id)
            ->whereJsonContains('target_risk_levels', 'high')
            ->get();

        foreach ($highRiskStudents as $student) {
            // Assign 2-3 random resources to each high-risk student
            $assignedResources = $supportResources->random(min(rand(2, 3), $supportResources->count()));

            foreach ($assignedResources as $resource) {
                ResourceAssignment::create([
                    'resource_id' => $resource->id,
                    'student_id' => $student->id,
                    'assigned_by' => $admin->id,
                    'status' => collect(['assigned', 'in_progress', 'completed'])->random(),
                    'assigned_at' => now()->subDays(rand(1, 14)),
                    'progress_percent' => rand(0, 100),
                ]);
            }
        }
    }
}
