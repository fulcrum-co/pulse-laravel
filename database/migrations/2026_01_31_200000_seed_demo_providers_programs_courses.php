<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, ensure org hierarchy is correct
        $this->ensureOrgHierarchy();

        // Get the school org
        $school = DB::table('organizations')->where('org_type', 'school')->first();
        if (!$school) {
            return;
        }

        // Get an admin user for created_by
        $admin = DB::table('users')->where('org_id', $school->id)->first();
        if (!$admin) {
            $admin = DB::table('users')->first();
        }
        $createdBy = $admin?->id;

        // Seed providers if none exist
        $this->seedProviders($school->id, $createdBy);

        // Seed programs if none exist
        $this->seedPrograms($school->id, $createdBy);

        // Seed mini courses if none exist
        $this->seedMiniCourses($school->id, $createdBy);
    }

    protected function ensureOrgHierarchy(): void
    {
        $district = DB::table('organizations')->where('org_type', 'district')->first();
        $school = DB::table('organizations')->where('org_type', 'school')->first();

        if ($district && $school && !$school->parent_org_id) {
            DB::table('organizations')
                ->where('id', $school->id)
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
                'availability_status' => 'available',
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
                'credentials' => 'M.Ed., Licensed School Counselor',
                'bio' => 'Marcus has 12 years of experience helping students navigate academic and personal challenges.',
                'contact_email' => 'marcus.thompson@example.com',
                'contact_phone' => '(555) 234-5678',
                'availability_status' => 'available',
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
                'availability_status' => 'limited',
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
                'bio' => 'James works with students to develop leadership skills and achieve their personal and academic goals.',
                'contact_email' => 'james.wilson@example.com',
                'contact_phone' => '(555) 456-7890',
                'availability_status' => 'available',
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
                'program_type' => 'workshop',
                'description' => 'An 8-week workshop teaching students evidence-based techniques for managing anxiety and stress.',
                'duration_weeks' => 8,
                'max_participants' => 15,
                'target_grades' => json_encode(['9-12']),
                'active' => true,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'name' => 'Social Skills Development',
                'program_type' => 'group_therapy',
                'description' => 'A supportive group program helping students develop communication and interpersonal skills.',
                'duration_weeks' => 12,
                'max_participants' => 10,
                'target_grades' => json_encode(['6-8', '9-12']),
                'active' => true,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'name' => 'Mindfulness & Meditation',
                'program_type' => 'workshop',
                'description' => 'Weekly mindfulness sessions teaching students meditation and relaxation techniques.',
                'duration_weeks' => 6,
                'max_participants' => 20,
                'target_grades' => json_encode(['K-2', '3-5', '6-8', '9-12']),
                'active' => true,
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'name' => 'Peer Support Training',
                'program_type' => 'training',
                'description' => 'Train student leaders to provide peer support and recognize warning signs in their classmates.',
                'duration_weeks' => 4,
                'max_participants' => 25,
                'target_grades' => json_encode(['9-12']),
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
                'course_type' => 'sel',
                'target_audience' => 'student',
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
                'course_type' => 'sel',
                'target_audience' => 'student',
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
                'target_audience' => 'student',
                'estimated_duration_minutes' => 25,
                'status' => 'active',
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_id' => $orgId,
                'title' => 'Supporting Students in Crisis',
                'description' => 'Training for teachers on recognizing and responding to students in distress.',
                'course_type' => 'professional_development',
                'target_audience' => 'teacher',
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
                ['mini_course_id' => $courseId, 'title' => 'Introduction', 'step_type' => 'video', 'order' => 1, 'duration_minutes' => 5, 'created_at' => now(), 'updated_at' => now()],
                ['mini_course_id' => $courseId, 'title' => 'Core Concepts', 'step_type' => 'reading', 'order' => 2, 'duration_minutes' => 10, 'created_at' => now(), 'updated_at' => now()],
                ['mini_course_id' => $courseId, 'title' => 'Practice Activity', 'step_type' => 'activity', 'order' => 3, 'duration_minutes' => 10, 'created_at' => now(), 'updated_at' => now()],
                ['mini_course_id' => $courseId, 'title' => 'Knowledge Check', 'step_type' => 'quiz', 'order' => 4, 'duration_minutes' => 5, 'created_at' => now(), 'updated_at' => now()],
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
