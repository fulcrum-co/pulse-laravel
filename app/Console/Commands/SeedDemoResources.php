<?php

namespace App\Console\Commands;

use App\Models\MiniCourse;
use App\Models\MiniCourseStep;
use App\Models\Organization;
use App\Models\Program;
use App\Models\Provider;
use App\Models\Resource;
use App\Models\User;
use Illuminate\Console\Command;

class SeedDemoResources extends Command
{
    protected $signature = 'demo:seed-resources {org_id? : The organization ID to seed resources for}';
    protected $description = 'Seed demo resources, providers, programs, and courses for an organization';

    public function handle(): int
    {
        $orgId = $this->argument('org_id') ?? 1;
        $org = Organization::find($orgId);

        if (!$org) {
            $this->error("Organization with ID {$orgId} not found.");
            return 1;
        }

        $admin = User::where('org_id', $orgId)->where('primary_role', 'admin')->first();
        if (!$admin) {
            $admin = User::where('org_id', $orgId)->first();
        }

        if (!$admin) {
            $this->error("No users found for organization {$orgId}.");
            return 1;
        }

        $this->info("Seeding demo data for: {$org->org_name} (ID: {$orgId})");

        $this->seedResources($orgId, $admin->id);
        $this->seedProviders($orgId, $admin->id);
        $this->seedPrograms($orgId, $admin->id);
        $this->seedCourses($orgId, $admin->id);

        $this->info('Demo data seeded successfully!');
        return 0;
    }

    protected function seedResources(int $orgId, int $userId): void
    {
        $resources = [
            [
                'title' => 'Managing Test Anxiety',
                'description' => 'Learn effective strategies to manage anxiety before and during tests. This comprehensive guide covers breathing techniques, study habits, and mental preparation.',
                'resource_type' => 'article',
                'category' => 'anxiety',
                'tags' => ['stress', 'academics', 'self-help'],
                'estimated_duration_minutes' => 10,
                'target_risk_levels' => ['low', 'moderate', 'high'],
            ],
            [
                'title' => 'Mindfulness for Students',
                'description' => 'A guided introduction to mindfulness practices for teens. Includes 5-minute exercises that can be done anywhere.',
                'resource_type' => 'video',
                'category' => 'wellness',
                'tags' => ['mindfulness', 'meditation', 'wellness'],
                'estimated_duration_minutes' => 15,
                'url' => 'https://www.youtube.com/watch?v=example',
            ],
            [
                'title' => 'Building Healthy Sleep Habits',
                'description' => 'Tips and strategies for improving sleep quality and establishing a consistent bedtime routine.',
                'resource_type' => 'article',
                'category' => 'wellness',
                'tags' => ['sleep', 'health', 'habits'],
                'estimated_duration_minutes' => 8,
            ],
            [
                'title' => 'Dealing with Social Pressure',
                'description' => 'Understanding and navigating peer pressure in high school. Interactive exercises to build confidence.',
                'resource_type' => 'worksheet',
                'category' => 'social',
                'tags' => ['peer pressure', 'social skills', 'decision making'],
                'estimated_duration_minutes' => 20,
                'target_risk_levels' => ['moderate', 'high'],
            ],
            [
                'title' => 'Study Skills Toolkit',
                'description' => 'Interactive workshop materials on effective study techniques including note-taking, time management, and test preparation.',
                'resource_type' => 'activity',
                'category' => 'academic',
                'tags' => ['studying', 'organization', 'time management'],
                'estimated_duration_minutes' => 45,
            ],
            [
                'title' => 'Coping with Change',
                'description' => 'Resources for dealing with major life transitions like moving, family changes, or starting a new school.',
                'resource_type' => 'article',
                'category' => 'stress',
                'tags' => ['transitions', 'coping', 'resilience'],
                'estimated_duration_minutes' => 12,
                'target_risk_levels' => ['moderate', 'high'],
            ],
            [
                'title' => 'Crisis Support Resources',
                'description' => 'Important contact information and resources for immediate support during crisis situations.',
                'resource_type' => 'document',
                'category' => 'crisis',
                'tags' => ['emergency', 'support', 'hotlines'],
                'estimated_duration_minutes' => 5,
                'target_risk_levels' => ['high'],
            ],
            [
                'title' => 'Building Self-Esteem Workbook',
                'description' => 'Exercises and reflections designed to build confidence and positive self-image over 4 weeks.',
                'resource_type' => 'worksheet',
                'category' => 'wellness',
                'tags' => ['self-esteem', 'confidence', 'growth'],
                'estimated_duration_minutes' => 25,
            ],
        ];

        foreach ($resources as $data) {
            Resource::firstOrCreate(
                ['org_id' => $orgId, 'title' => $data['title']],
                array_merge($data, [
                    'org_id' => $orgId,
                    'is_public' => false,
                    'active' => true,
                    'created_by' => $userId,
                ])
            );
        }

        $this->info('  - Created ' . count($resources) . ' resources');
    }

    protected function seedProviders(int $orgId, int $userId): void
    {
        $providers = [
            [
                'name' => 'Dr. Sarah Chen',
                'provider_type' => 'therapist',
                'bio' => 'Licensed clinical psychologist specializing in adolescent anxiety and depression. 15+ years experience working with teens.',
                'contact_email' => 'sarah.chen@example.com',
                'contact_phone' => '(555) 123-4567',
                'specialties' => ['anxiety', 'depression', 'trauma'],
                'serves_remote' => true,
                'serves_in_person' => true,
                'availability_status' => 'available',
            ],
            [
                'name' => 'Marcus Williams, LCSW',
                'provider_type' => 'counselor',
                'bio' => 'School-based social worker with expertise in family counseling and crisis intervention.',
                'contact_email' => 'marcus.w@example.com',
                'specialties' => ['family counseling', 'crisis intervention', 'behavioral'],
                'serves_remote' => true,
                'serves_in_person' => true,
                'availability_status' => 'available',
            ],
            [
                'name' => 'Jennifer Park, Ed.D',
                'provider_type' => 'tutor',
                'bio' => 'Academic coach specializing in study skills, executive function, and learning differences support.',
                'contact_email' => 'jpark@example.com',
                'specialties' => ['study skills', 'ADHD support', 'learning differences'],
                'serves_remote' => true,
                'serves_in_person' => false,
                'availability_status' => 'available',
            ],
            [
                'name' => 'Coach Mike Thompson',
                'provider_type' => 'coach',
                'bio' => 'Life skills coach helping students build resilience, goal-setting abilities, and leadership skills.',
                'contact_email' => 'mike.t@example.com',
                'specialties' => ['leadership', 'goal setting', 'resilience'],
                'serves_remote' => true,
                'serves_in_person' => true,
                'availability_status' => 'waitlist',
            ],
            [
                'name' => 'Dr. Emily Rodriguez',
                'provider_type' => 'therapist',
                'bio' => 'Bilingual therapist (English/Spanish) specializing in culturally responsive mental health care for youth.',
                'contact_email' => 'e.rodriguez@example.com',
                'specialties' => ['cultural identity', 'anxiety', 'family dynamics'],
                'serves_remote' => true,
                'serves_in_person' => true,
                'availability_status' => 'available',
            ],
        ];

        foreach ($providers as $data) {
            Provider::firstOrCreate(
                ['org_id' => $orgId, 'name' => $data['name']],
                array_merge($data, [
                    'org_id' => $orgId,
                    'active' => true,
                    'created_by' => $userId,
                ])
            );
        }

        $this->info('  - Created ' . count($providers) . ' providers');
    }

    protected function seedPrograms(int $orgId, int $userId): void
    {
        $programs = [
            [
                'name' => 'Anxiety Support Group',
                'description' => 'Weekly peer support group for students experiencing anxiety. Facilitated by licensed counselors.',
                'program_type' => 'support_group',
                'duration_weeks' => 8,
                'max_participants' => 12,
                'current_participants' => 7,
                'location_type' => 'hybrid',
                'cost' => 'free',
                'schedule' => 'Tuesdays 3:00-4:00 PM',
            ],
            [
                'name' => 'After-School Tutoring',
                'description' => 'Free academic tutoring in math, science, and English. Available Monday through Thursday.',
                'program_type' => 'academic',
                'duration_weeks' => null,
                'max_participants' => 30,
                'current_participants' => 18,
                'location_type' => 'in_person',
                'cost' => 'free',
                'schedule' => 'Mon-Thu 3:30-5:00 PM',
            ],
            [
                'name' => 'Peer Mentorship Program',
                'description' => 'Connect with trained peer mentors for academic and social support throughout the school year.',
                'program_type' => 'mentorship',
                'duration_weeks' => 36,
                'max_participants' => 50,
                'current_participants' => 35,
                'location_type' => 'in_person',
                'cost' => 'free',
            ],
            [
                'name' => 'College Prep Workshop Series',
                'description' => 'Monthly workshops covering college applications, essays, financial aid, and career exploration.',
                'program_type' => 'academic',
                'duration_weeks' => 20,
                'max_participants' => 40,
                'current_participants' => 28,
                'location_type' => 'hybrid',
                'cost' => 'free',
                'schedule' => 'First Saturday of each month',
            ],
            [
                'name' => 'Mindfulness & Meditation Club',
                'description' => 'Learn mindfulness techniques and meditation practices in a supportive group setting.',
                'program_type' => 'wellness',
                'duration_weeks' => null,
                'max_participants' => 20,
                'current_participants' => 12,
                'location_type' => 'in_person',
                'cost' => 'free',
                'schedule' => 'Fridays at lunch',
            ],
        ];

        foreach ($programs as $data) {
            Program::firstOrCreate(
                ['org_id' => $orgId, 'name' => $data['name']],
                array_merge($data, [
                    'org_id' => $orgId,
                    'active' => true,
                    'status' => 'active',
                    'created_by' => $userId,
                ])
            );
        }

        $this->info('  - Created ' . count($programs) . ' programs');
    }

    protected function seedCourses(int $orgId, int $userId): void
    {
        $courses = [
            [
                'title' => 'Managing Test Anxiety',
                'description' => 'A 5-step course to help students understand and manage test anxiety using proven techniques.',
                'course_type' => 'intervention',
                'target_audience' => 'students',
                'objectives' => [
                    'Understand what causes test anxiety',
                    'Learn breathing techniques for calming',
                    'Develop a pre-test routine',
                    'Practice positive self-talk',
                ],
                'steps' => [
                    ['title' => 'Understanding Test Anxiety', 'step_type' => 'content', 'duration' => 10],
                    ['title' => 'Breathing Techniques', 'step_type' => 'activity', 'duration' => 15],
                    ['title' => 'Building Your Routine', 'step_type' => 'reflection', 'duration' => 10],
                    ['title' => 'Positive Self-Talk', 'step_type' => 'content', 'duration' => 10],
                    ['title' => 'Putting It Together', 'step_type' => 'quiz', 'duration' => 5],
                ],
            ],
            [
                'title' => 'Building Study Skills',
                'description' => 'Learn essential study skills including note-taking, time management, and active reading strategies.',
                'course_type' => 'skill_building',
                'target_audience' => 'students',
                'objectives' => [
                    'Master effective note-taking methods',
                    'Create a study schedule',
                    'Use active reading strategies',
                ],
                'steps' => [
                    ['title' => 'Note-Taking Methods', 'step_type' => 'content', 'duration' => 15],
                    ['title' => 'Time Management', 'step_type' => 'activity', 'duration' => 20],
                    ['title' => 'Active Reading', 'step_type' => 'content', 'duration' => 15],
                    ['title' => 'Practice Session', 'step_type' => 'activity', 'duration' => 25],
                ],
            ],
            [
                'title' => 'Mindfulness Basics',
                'description' => 'Introduction to mindfulness meditation for stress reduction and focus improvement.',
                'course_type' => 'wellness',
                'target_audience' => 'students',
                'objectives' => [
                    'Understand what mindfulness is',
                    'Practice basic meditation',
                    'Apply mindfulness to daily life',
                ],
                'steps' => [
                    ['title' => 'What is Mindfulness?', 'step_type' => 'content', 'duration' => 10],
                    ['title' => 'Your First Meditation', 'step_type' => 'activity', 'duration' => 10],
                    ['title' => 'Mindful Moments', 'step_type' => 'reflection', 'duration' => 10],
                ],
            ],
        ];

        foreach ($courses as $courseData) {
            $steps = $courseData['steps'];
            unset($courseData['steps']);

            $course = MiniCourse::firstOrCreate(
                ['org_id' => $orgId, 'title' => $courseData['title']],
                array_merge($courseData, [
                    'org_id' => $orgId,
                    'status' => 'active',
                    'created_by' => $userId,
                    'visibility' => 'organization',
                ])
            );

            // Only create steps if this is a new course
            if ($course->wasRecentlyCreated) {
                foreach ($steps as $index => $stepData) {
                    MiniCourseStep::create([
                        'mini_course_id' => $course->id,
                        'title' => $stepData['title'],
                        'step_type' => $stepData['step_type'],
                        'sort_order' => $index + 1,
                        'estimated_duration_minutes' => $stepData['duration'],
                        'instructions' => 'Complete this step to continue.',
                    ]);
                }
            }
        }

        $this->info('  - Created ' . count($courses) . ' courses with steps');
    }
}
