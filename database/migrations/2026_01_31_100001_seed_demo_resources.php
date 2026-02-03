<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Find the organization and admin
        $organization = DB::table('organizations')->where('org_type', 'organization')->first();
        $admin = DB::table('users')
            ->where('primary_role', 'admin')
            ->where('org_id', $organization?->id)
            ->first();

        if (! $organization || ! $admin) {
            return;
        }

        // Check if resources already exist
        $existingCount = DB::table('resources')->where('org_id', $organization->id)->count();
        if ($existingCount > 0) {
            return; // Already seeded
        }

        $resources = [
            [
                'title' => 'Managing Test Anxiety',
                'description' => 'Learn effective strategies to manage anxiety before and during tests.',
                'resource_type' => 'article',
                'category' => 'anxiety',
                'tags' => json_encode(['stress', 'academics', 'self-help']),
                'estimated_duration_minutes' => 10,
                'target_risk_levels' => json_encode(['low', 'high']),
            ],
            [
                'title' => 'Mindfulness for Learners',
                'description' => 'A guided introduction to mindfulness practices for teens.',
                'resource_type' => 'video',
                'category' => 'stress',
                'tags' => json_encode(['mindfulness', 'meditation', 'wellness']),
                'estimated_duration_minutes' => 15,
                'target_risk_levels' => json_encode(['good', 'low', 'high']),
            ],
            [
                'title' => 'Building Healthy Sleep Habits',
                'description' => 'Tips and strategies for improving sleep quality.',
                'resource_type' => 'article',
                'category' => 'wellness',
                'tags' => json_encode(['sleep', 'health', 'habits']),
                'estimated_duration_minutes' => 8,
                'target_risk_levels' => json_encode(['good', 'low']),
            ],
            [
                'title' => 'Dealing with Social Pressure',
                'description' => 'Understanding and navigating peer pressure in high organization.',
                'resource_type' => 'worksheet',
                'category' => 'social',
                'tags' => json_encode(['peer pressure', 'social skills', 'decision making']),
                'estimated_duration_minutes' => 20,
                'target_risk_levels' => json_encode(['low', 'high']),
            ],
            [
                'title' => 'Study Skills Workshop',
                'description' => 'Interactive workshop on effective study techniques.',
                'resource_type' => 'activity',
                'category' => 'academic',
                'tags' => json_encode(['studying', 'organization', 'time management']),
                'estimated_duration_minutes' => 45,
                'target_risk_levels' => json_encode(['good', 'low']),
            ],
            [
                'title' => 'Coping with Change',
                'description' => 'Resources for dealing with major life transitions.',
                'resource_type' => 'article',
                'category' => 'stress',
                'tags' => json_encode(['transitions', 'coping', 'resilience']),
                'estimated_duration_minutes' => 12,
                'target_risk_levels' => json_encode(['low', 'high']),
            ],
            [
                'title' => 'Crisis Support Hotlines',
                'description' => 'Important contact information for immediate support.',
                'resource_type' => 'document',
                'category' => 'crisis',
                'tags' => json_encode(['emergency', 'support', 'hotlines']),
                'estimated_duration_minutes' => 5,
                'target_risk_levels' => json_encode(['high']),
            ],
            [
                'title' => 'Building Self-Esteem',
                'description' => 'Exercises and reflections to build confidence.',
                'resource_type' => 'worksheet',
                'category' => 'wellness',
                'tags' => json_encode(['self-esteem', 'confidence', 'growth']),
                'estimated_duration_minutes' => 25,
                'target_risk_levels' => json_encode(['low', 'high']),
            ],
        ];

        foreach ($resources as $resourceData) {
            DB::table('resources')->insert([
                'org_id' => $organization->id,
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
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        $organization = DB::table('organizations')->where('org_type', 'organization')->first();
        if ($organization) {
            DB::table('resources')->where('org_id', $organization->id)->delete();
        }
    }
};
