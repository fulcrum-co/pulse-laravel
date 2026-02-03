<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, ensure org hierarchy is correct
        $this->ensureOrgHierarchy();

        // Get the organization org
        $organization = DB::table('organizations')->where('org_type', 'organization')->first();
        if (! $organization) {
            return;
        }

        // Get an admin user for created_by
        $admin = DB::table('users')->where('org_id', $organization->id)->first();
        if (! $admin) {
            $admin = DB::table('users')->first();
        }
        $createdBy = $admin?->id;

        // Seed providers if none exist
        $this->seedProviders($organization->id, $createdBy);

        // Seed programs if none exist
        $this->seedPrograms($organization->id, $createdBy);

        // Seed mini courses if none exist
        $this->seedMiniCourses($organization->id, $createdBy);
    }

    protected function ensureOrgHierarchy(): void
    {
        $district = DB::table('organizations')->where('org_type', 'district')->first();
        $organization = DB::table('organizations')->where('org_type', 'organization')->first();

        if ($district && $organization && ! $organization->parent_org_id) {
            DB::table('organizations')
                ->where('id', $organization->id)
                ->update(['parent_org_id' => $district->id]);
        }
    }

    protected function seedProviders(int $orgId, ?int $createdBy): void
    {
        if (DB::table('providers')->where('org_id', $orgId)->exists()) {
            return;
        }

        $providers = [
            [
                'org_id' => $orgId,
                'name' => 'Dr. Sarah Chen',
                'provider_type' => 'therapist',
                'specialty_areas' => json_encode(['anxiety', 'depression', 'trauma']),
                'credentials' => 'Licensed Clinical Psychologist, Ph.D.',
                'bio' => 'Dr. Chen specializes in adolescent mental health with over 15 years of experience working with teens and their families.',
                'contact_email' => 'sarah.chen@example.com',
                'contact_phone' => '(555) 123-4567',
                'availability_notes' => 'Available Monday-Thursday, 9am-5pm',
                'hourly_rate' => 150.00,
                'accepts_insurance' => true,
                'serves_remote' => true,
                'serves_in_person' => true,
                'active' => true,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'name' => 'Marcus Thompson',
                'provider_type' => 'counselor',
                'specialty_areas' => json_encode(['academic', 'career', 'social skills']),
                'credentials' => 'M.Ed., Licensed Organization Counselor',
                'bio' => 'Marcus has 12 years of experience helping learners navigate academic and personal challenges.',
                'contact_email' => 'marcus.thompson@example.com',
                'contact_phone' => '(555) 234-5678',
                'availability_notes' => 'Available during organization hours',
                'hourly_rate' => 100.00,
                'accepts_insurance' => false,
                'serves_remote' => true,
                'serves_in_person' => true,
                'active' => true,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'name' => 'Dr. Emily Rodriguez',
                'provider_type' => 'therapist',
                'specialty_areas' => json_encode(['family therapy', 'grief', 'adjustment']),
                'credentials' => 'Licensed Marriage and Family Therapist, Ph.D.',
                'bio' => 'Dr. Rodriguez specializes in helping families navigate difficult transitions and build stronger relationships.',
                'contact_email' => 'emily.rodriguez@example.com',
                'contact_phone' => '(555) 345-6789',
                'availability_notes' => 'Limited availability - evenings only',
                'hourly_rate' => 175.00,
                'accepts_insurance' => true,
                'serves_remote' => true,
                'serves_in_person' => false,
                'active' => true,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'name' => 'James Wilson',
                'provider_type' => 'mentor',
                'specialty_areas' => json_encode(['leadership', 'motivation', 'goal setting']),
                'credentials' => 'Certified Life Coach',
                'bio' => 'James works with learners to develop leadership skills and achieve their personal and academic goals.',
                'contact_email' => 'james.wilson@example.com',
                'contact_phone' => '(555) 456-7890',
                'availability_notes' => 'Flexible scheduling available',
                'hourly_rate' => 75.00,
                'accepts_insurance' => false,
                'serves_remote' => true,
                'serves_in_person' => true,
                'active' => true,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('providers')->insert($providers);
    }

    protected function seedPrograms(int $orgId, ?int $createdBy): void
    {
        if (DB::table('programs')->where('org_id', $orgId)->exists()) {
            return;
        }

        $programs = [
            [
                'org_id' => $orgId,
                'name' => 'Anxiety Management Workshop',
                'program_type' => 'therapy',
                'description' => 'An 8-week workshop teaching learners evidence-based techniques for managing anxiety and stress.',
                'provider_org_name' => 'Wellness Center',
                'target_needs' => json_encode(['anxiety', 'stress', 'coping skills']),
                'eligibility_criteria' => json_encode(['Grades 9-12', 'Parent consent required']),
                'cost_structure' => 'free',
                'duration_weeks' => 8,
                'frequency_per_week' => 1,
                'location_type' => 'hybrid',
                'capacity' => 15,
                'is_rolling_enrollment' => false,
                'active' => true,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'name' => 'Social Skills Development',
                'program_type' => 'support_group',
                'description' => 'A supportive group program helping learners develop communication and interpersonal skills.',
                'provider_org_name' => 'Youth Services',
                'target_needs' => json_encode(['social skills', 'communication', 'peer relationships']),
                'eligibility_criteria' => json_encode(['Grades 6-12', 'Counselor referral']),
                'cost_structure' => 'free',
                'duration_weeks' => 12,
                'frequency_per_week' => 1,
                'location_type' => 'in_person',
                'capacity' => 10,
                'is_rolling_enrollment' => false,
                'active' => true,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'name' => 'Mindfulness & Meditation',
                'program_type' => 'enrichment',
                'description' => 'Weekly mindfulness sessions teaching learners meditation and relaxation techniques.',
                'provider_org_name' => 'Mindful Organizations',
                'target_needs' => json_encode(['stress reduction', 'focus', 'emotional regulation']),
                'eligibility_criteria' => json_encode(['All grades welcome']),
                'cost_structure' => 'free',
                'duration_weeks' => 6,
                'frequency_per_week' => 2,
                'location_type' => 'virtual',
                'capacity' => 20,
                'is_rolling_enrollment' => true,
                'active' => true,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'name' => 'Peer Support Training',
                'program_type' => 'mentorship',
                'description' => 'Train learner leaders to provide peer support and recognize warning signs in their classmates.',
                'provider_org_name' => 'Learner Leadership Initiative',
                'target_needs' => json_encode(['leadership', 'peer support', 'crisis awareness']),
                'eligibility_criteria' => json_encode(['Grades 10-12', 'Teacher recommendation', 'GPA 2.5+']),
                'cost_structure' => 'free',
                'duration_weeks' => 4,
                'frequency_per_week' => 2,
                'location_type' => 'in_person',
                'capacity' => 25,
                'is_rolling_enrollment' => false,
                'active' => true,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('programs')->insert($programs);
    }

    protected function seedMiniCourses(int $orgId, ?int $createdBy): void
    {
        if (DB::table('mini_courses')->where('org_id', $orgId)->exists()) {
            return;
        }

        $courses = [
            [
                'org_id' => $orgId,
                'title' => 'Understanding Your Emotions',
                'description' => 'Learn to identify, understand, and manage your emotions effectively.',
                'course_type' => 'skill_building',
                'estimated_duration_minutes' => 30,
                'status' => 'active',
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'title' => 'Building Healthy Relationships',
                'description' => 'Develop skills for creating and maintaining positive relationships with peers and adults.',
                'course_type' => 'wellness',
                'estimated_duration_minutes' => 45,
                'status' => 'active',
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'title' => 'Stress Management 101',
                'description' => 'Practical techniques for managing stress and preventing burnout.',
                'course_type' => 'behavioral',
                'estimated_duration_minutes' => 25,
                'status' => 'active',
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'title' => 'Supporting Learners in Crisis',
                'description' => 'Training for teachers on recognizing and responding to learners in distress.',
                'course_type' => 'academic',
                'estimated_duration_minutes' => 60,
                'status' => 'active',
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($courses as $course) {
            $courseId = DB::table('mini_courses')->insertGetId($course);

            // Add some steps for each course
            $steps = [
                ['mini_course_id' => $courseId, 'title' => 'Introduction', 'step_type' => 'video', 'sort_order' => 1, 'duration_minutes' => 5, 'created_at' => now(), 'updated_at' => now()],
                ['mini_course_id' => $courseId, 'title' => 'Core Concepts', 'step_type' => 'reading', 'sort_order' => 2, 'duration_minutes' => 10, 'created_at' => now(), 'updated_at' => now()],
                ['mini_course_id' => $courseId, 'title' => 'Practice Activity', 'step_type' => 'activity', 'sort_order' => 3, 'duration_minutes' => 10, 'created_at' => now(), 'updated_at' => now()],
                ['mini_course_id' => $courseId, 'title' => 'Knowledge Check', 'step_type' => 'quiz', 'sort_order' => 4, 'duration_minutes' => 5, 'created_at' => now(), 'updated_at' => now()],
            ];

            DB::table('mini_course_steps')->insert($steps);
        }
    }

    public function down(): void
    {
        // Note: This migration seeds data, so down() is intentionally minimal
        // to avoid accidentally deleting user-created data
    }
};
