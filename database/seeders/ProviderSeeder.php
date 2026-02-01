<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    public function run(): void
    {
        $school = Organization::where('org_type', 'school')->first();
        if (! $school) {
            $school = Organization::first();
        }
        if (! $school) {
            $this->command->error('No organization found. Please seed organizations first.');

            return;
        }

        $admin = User::where('org_id', $school->id)->first();
        if (! $admin) {
            $admin = User::first();
        }
        if (! $admin) {
            $this->command->error('No user found. Please seed users first.');

            return;
        }

        $providers = [
            [
                'name' => 'Dr. Sarah Chen',
                'provider_type' => Provider::TYPE_THERAPIST,
                'specialty_areas' => ['anxiety', 'depression', 'trauma', 'adolescent development'],
                'credentials' => 'Licensed Clinical Psychologist, Ph.D.',
                'bio' => 'Dr. Chen specializes in adolescent mental health with over 15 years of experience working with teens and their families. Her approach combines cognitive-behavioral therapy with mindfulness techniques.',
                'contact_email' => 'sarah.chen@example.com',
                'contact_phone' => '(555) 123-4567',
                'availability_notes' => 'Available Tuesdays and Thursdays, 3-7 PM',
                'hourly_rate' => 150.00,
                'accepts_insurance' => true,
                'serves_remote' => true,
                'serves_in_person' => true,
                'ratings_average' => 4.8,
                'verified_at' => now()->subMonths(6),
            ],
            [
                'name' => 'Marcus Thompson',
                'provider_type' => Provider::TYPE_TUTOR,
                'specialty_areas' => ['mathematics', 'algebra', 'calculus', 'test prep'],
                'credentials' => 'M.Ed. Mathematics Education, Certified Math Teacher',
                'bio' => 'Marcus has helped hundreds of students improve their math grades and confidence. He specializes in making complex concepts accessible and building strong foundational skills.',
                'contact_email' => 'marcus.t@example.com',
                'contact_phone' => '(555) 234-5678',
                'availability_notes' => 'Weekday afternoons and Saturday mornings',
                'hourly_rate' => 65.00,
                'accepts_insurance' => false,
                'serves_remote' => true,
                'serves_in_person' => true,
                'ratings_average' => 4.9,
                'verified_at' => now()->subMonths(3),
            ],
            [
                'name' => 'Jennifer Martinez, LCSW',
                'provider_type' => Provider::TYPE_COUNSELOR,
                'specialty_areas' => ['family counseling', 'grief', 'life transitions', 'self-esteem'],
                'credentials' => 'Licensed Clinical Social Worker',
                'bio' => 'Jennifer provides compassionate counseling services focused on helping teens navigate difficult life transitions and family challenges.',
                'contact_email' => 'j.martinez.lcsw@example.com',
                'contact_phone' => '(555) 345-6789',
                'availability_notes' => 'Monday-Friday, flexible hours',
                'hourly_rate' => 120.00,
                'accepts_insurance' => true,
                'serves_remote' => true,
                'serves_in_person' => true,
                'ratings_average' => 4.7,
                'verified_at' => now()->subMonths(8),
            ],
            [
                'name' => 'David Park',
                'provider_type' => Provider::TYPE_COACH,
                'specialty_areas' => ['executive function', 'ADHD coaching', 'organization', 'time management'],
                'credentials' => 'Certified ADHD Coach (PCAC), Life Coach',
                'bio' => 'David helps students with executive function challenges develop practical systems for organization, time management, and achieving their academic goals.',
                'contact_email' => 'david.park.coach@example.com',
                'contact_phone' => '(555) 456-7890',
                'availability_notes' => 'Afternoons and evenings, virtual sessions preferred',
                'hourly_rate' => 85.00,
                'accepts_insurance' => false,
                'serves_remote' => true,
                'serves_in_person' => false,
                'ratings_average' => 4.6,
                'verified_at' => now()->subMonths(4),
            ],
            [
                'name' => 'Angela Washington',
                'provider_type' => Provider::TYPE_MENTOR,
                'specialty_areas' => ['college preparation', 'career exploration', 'leadership', 'first-generation students'],
                'credentials' => 'Former College Admissions Counselor, Youth Mentor',
                'bio' => 'Angela is passionate about helping first-generation college students navigate the application process and discover their career paths.',
                'contact_email' => 'angela.w@example.com',
                'contact_phone' => '(555) 567-8901',
                'availability_notes' => 'Weekends and some weekday evenings',
                'hourly_rate' => 50.00,
                'accepts_insurance' => false,
                'serves_remote' => true,
                'serves_in_person' => true,
                'ratings_average' => 4.9,
                'verified_at' => now()->subMonths(2),
            ],
            [
                'name' => 'Dr. Robert Kim',
                'provider_type' => Provider::TYPE_SPECIALIST,
                'specialty_areas' => ['learning disabilities', 'dyslexia', 'educational assessment', 'IEP support'],
                'credentials' => 'Ed.D., Licensed Educational Psychologist',
                'bio' => 'Dr. Kim specializes in educational assessments and supporting students with learning differences. He works closely with schools to ensure appropriate accommodations.',
                'contact_email' => 'dr.kim@example.com',
                'contact_phone' => '(555) 678-9012',
                'availability_notes' => 'By appointment, typically 2-week lead time',
                'hourly_rate' => 200.00,
                'accepts_insurance' => true,
                'serves_remote' => false,
                'serves_in_person' => true,
                'ratings_average' => 4.8,
                'verified_at' => now()->subMonths(12),
            ],
            [
                'name' => 'Lisa Chen',
                'provider_type' => Provider::TYPE_TUTOR,
                'specialty_areas' => ['english', 'writing', 'essay coaching', 'reading comprehension'],
                'credentials' => 'M.A. English Literature, Certified Writing Tutor',
                'bio' => 'Lisa helps students find their voice through writing. She specializes in essay development, creative writing, and improving reading comprehension skills.',
                'contact_email' => 'lisa.chen.tutor@example.com',
                'contact_phone' => '(555) 789-0123',
                'availability_notes' => 'Flexible schedule, online and in-person',
                'hourly_rate' => 55.00,
                'accepts_insurance' => false,
                'serves_remote' => true,
                'serves_in_person' => true,
                'ratings_average' => 4.7,
                'verified_at' => now()->subMonths(5),
            ],
            [
                'name' => 'James Wilson, LPC',
                'provider_type' => Provider::TYPE_THERAPIST,
                'specialty_areas' => ['substance abuse', 'anger management', 'behavioral issues', 'group therapy'],
                'credentials' => 'Licensed Professional Counselor, Certified Addictions Counselor',
                'bio' => 'James specializes in helping adolescents overcome behavioral challenges and substance use issues through evidence-based therapeutic approaches.',
                'contact_email' => 'j.wilson.lpc@example.com',
                'contact_phone' => '(555) 890-1234',
                'availability_notes' => 'Weekday evenings, group sessions on Saturdays',
                'hourly_rate' => 130.00,
                'accepts_insurance' => true,
                'serves_remote' => true,
                'serves_in_person' => true,
                'ratings_average' => 4.5,
                'verified_at' => now()->subMonths(9),
            ],
        ];

        foreach ($providers as $providerData) {
            Provider::create([
                'org_id' => $school->id,
                'name' => $providerData['name'],
                'provider_type' => $providerData['provider_type'],
                'specialty_areas' => $providerData['specialty_areas'],
                'credentials' => $providerData['credentials'],
                'bio' => $providerData['bio'],
                'contact_email' => $providerData['contact_email'],
                'contact_phone' => $providerData['contact_phone'],
                'availability_notes' => $providerData['availability_notes'],
                'hourly_rate' => $providerData['hourly_rate'],
                'accepts_insurance' => $providerData['accepts_insurance'],
                'serves_remote' => $providerData['serves_remote'],
                'serves_in_person' => $providerData['serves_in_person'],
                'ratings_average' => $providerData['ratings_average'],
                'active' => true,
                'verified_at' => $providerData['verified_at'],
                'created_by' => $admin->id,
            ]);
        }
    }
}
