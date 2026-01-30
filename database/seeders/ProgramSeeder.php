<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        $admin = User::where('primary_role', 'admin')->where('org_id', $school->id)->first();

        $programs = [
            [
                'name' => 'Youth Mental Health First Aid',
                'description' => 'An 8-hour training program that teaches participants how to identify, understand, and respond to signs of mental illness and substance use disorders in youth.',
                'program_type' => Program::TYPE_INTERVENTION,
                'provider_org_name' => 'Mental Health America',
                'target_needs' => ['mental health awareness', 'crisis support', 'peer support'],
                'eligibility_criteria' => ['Ages 14-18', 'Parent/guardian consent required'],
                'cost_structure' => Program::COST_FREE,
                'duration_weeks' => 2,
                'location_type' => Program::LOCATION_HYBRID,
                'enrollment_url' => 'https://example.com/ymhfa',
            ],
            [
                'name' => 'AVID College Readiness',
                'description' => 'AVID (Advancement Via Individual Determination) is a college readiness program designed to help students develop critical thinking, literacy, and math skills.',
                'program_type' => Program::TYPE_ENRICHMENT,
                'provider_org_name' => 'AVID Center',
                'target_needs' => ['college preparation', 'academic skills', 'first-generation support'],
                'eligibility_criteria' => ['GPA 2.5-3.5', 'Teacher recommendation', 'College aspirations'],
                'cost_structure' => Program::COST_FREE,
                'duration_weeks' => 36,
                'location_type' => Program::LOCATION_IN_PERSON,
                'enrollment_url' => 'https://example.com/avid',
            ],
            [
                'name' => 'Teen Grief Support Group',
                'description' => 'A peer support group for teens who have experienced the loss of a loved one. Facilitated by licensed counselors in a safe, supportive environment.',
                'program_type' => Program::TYPE_SUPPORT_GROUP,
                'provider_org_name' => 'Hospice Foundation',
                'target_needs' => ['grief support', 'loss', 'emotional processing'],
                'eligibility_criteria' => ['Ages 13-18', 'Experienced recent loss', 'Parent consent'],
                'cost_structure' => Program::COST_FREE,
                'duration_weeks' => 8,
                'location_type' => Program::LOCATION_HYBRID,
                'enrollment_url' => 'https://example.com/grief-support',
            ],
            [
                'name' => 'Kumon Math Program',
                'description' => 'Self-paced math tutoring program that builds strong foundational skills through daily practice worksheets and regular assessments.',
                'program_type' => Program::TYPE_TUTORING,
                'provider_org_name' => 'Kumon Learning Centers',
                'target_needs' => ['math skills', 'foundational learning', 'self-paced study'],
                'eligibility_criteria' => ['All grade levels', 'Diagnostic assessment required'],
                'cost_structure' => Program::COST_FIXED,
                'duration_weeks' => null,
                'location_type' => Program::LOCATION_IN_PERSON,
                'enrollment_url' => 'https://example.com/kumon',
            ],
            [
                'name' => 'Dialectical Behavior Therapy for Teens',
                'description' => 'A 16-week evidence-based therapy program teaching emotional regulation, distress tolerance, mindfulness, and interpersonal effectiveness skills.',
                'program_type' => Program::TYPE_THERAPY,
                'provider_org_name' => 'Community Mental Health Center',
                'target_needs' => ['emotional regulation', 'self-harm prevention', 'anxiety', 'depression'],
                'eligibility_criteria' => ['Ages 13-17', 'Referral from therapist or school counselor', 'Insurance or sliding scale'],
                'cost_structure' => Program::COST_INSURANCE,
                'duration_weeks' => 16,
                'location_type' => Program::LOCATION_IN_PERSON,
                'enrollment_url' => 'https://example.com/dbt-teens',
            ],
            [
                'name' => 'Big Brothers Big Sisters',
                'description' => 'One-to-one youth mentoring program matching adult volunteers with young people for supportive, empowering relationships.',
                'program_type' => Program::TYPE_MENTORSHIP,
                'provider_org_name' => 'Big Brothers Big Sisters of America',
                'target_needs' => ['mentorship', 'role modeling', 'social support', 'character development'],
                'eligibility_criteria' => ['Ages 6-18', 'Parent/guardian involvement', 'Waitlist may apply'],
                'cost_structure' => Program::COST_FREE,
                'duration_weeks' => 52,
                'location_type' => Program::LOCATION_IN_PERSON,
                'enrollment_url' => 'https://example.com/bbbs',
            ],
            [
                'name' => 'Summer STEM Academy',
                'description' => 'An intensive 6-week summer program focused on science, technology, engineering, and math through hands-on projects and field trips.',
                'program_type' => Program::TYPE_ENRICHMENT,
                'provider_org_name' => 'University Outreach Program',
                'target_needs' => ['STEM interest', 'academic enrichment', 'career exploration'],
                'eligibility_criteria' => ['Rising 9th-12th graders', 'Application and essay', 'Teacher recommendation'],
                'cost_structure' => Program::COST_SLIDING_SCALE,
                'duration_weeks' => 6,
                'location_type' => Program::LOCATION_IN_PERSON,
                'enrollment_url' => 'https://example.com/stem-academy',
            ],
            [
                'name' => 'Substance Abuse Prevention Program',
                'description' => 'Evidence-based prevention curriculum addressing risk factors and building protective factors against substance use.',
                'program_type' => Program::TYPE_INTERVENTION,
                'provider_org_name' => 'Prevention Services Inc.',
                'target_needs' => ['substance use prevention', 'risk reduction', 'decision making'],
                'eligibility_criteria' => ['School enrollment required', 'Parent consent for intensive track'],
                'cost_structure' => Program::COST_FREE,
                'duration_weeks' => 10,
                'location_type' => Program::LOCATION_IN_PERSON,
                'enrollment_url' => 'https://example.com/sap',
            ],
            [
                'name' => 'Online Anxiety Management Course',
                'description' => 'Self-paced online course teaching cognitive-behavioral techniques for managing anxiety, with weekly check-ins from a licensed counselor.',
                'program_type' => Program::TYPE_THERAPY,
                'provider_org_name' => 'Teen Mental Health Online',
                'target_needs' => ['anxiety management', 'self-help', 'coping skills'],
                'eligibility_criteria' => ['Ages 14-18', 'Mild to moderate anxiety', 'Internet access required'],
                'cost_structure' => Program::COST_SLIDING_SCALE,
                'duration_weeks' => 8,
                'location_type' => Program::LOCATION_VIRTUAL,
                'enrollment_url' => 'https://example.com/anxiety-course',
            ],
            [
                'name' => 'Career Exploration Internship',
                'description' => 'Summer internship program placing students in local businesses for hands-on career exploration and professional skill development.',
                'program_type' => Program::TYPE_EXTERNAL_SERVICE,
                'provider_org_name' => 'Chamber of Commerce Youth Initiative',
                'target_needs' => ['career exploration', 'work experience', 'professional development'],
                'eligibility_criteria' => ['Ages 16+', 'Good academic standing', 'Work permit', 'Interview required'],
                'cost_structure' => Program::COST_FREE,
                'duration_weeks' => 8,
                'location_type' => Program::LOCATION_IN_PERSON,
                'enrollment_url' => 'https://example.com/career-internship',
            ],
        ];

        foreach ($programs as $programData) {
            Program::create([
                'org_id' => $school->id,
                'name' => $programData['name'],
                'description' => $programData['description'],
                'program_type' => $programData['program_type'],
                'provider_org_name' => $programData['provider_org_name'],
                'target_needs' => $programData['target_needs'],
                'eligibility_criteria' => $programData['eligibility_criteria'],
                'cost_structure' => $programData['cost_structure'],
                'duration_weeks' => $programData['duration_weeks'],
                'location_type' => $programData['location_type'],
                'enrollment_url' => $programData['enrollment_url'],
                'active' => true,
                'created_by' => $admin->id,
            ]);
        }
    }
}
