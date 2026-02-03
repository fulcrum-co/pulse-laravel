<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $section = Organization::where('org_type', 'section')->first();
        $organization = Organization::where('org_type', 'organization')->first();

        // Create administrative_role at section level - Dr. Margaret Chen
        // An experienced educator in her late 50s who oversees the entire section
        $administrative_role = User::create([
            'org_id' => $section->id,
            'first_name' => 'Margaret',
            'last_name' => 'Chen',
            'email' => 'mchen@lincolnorganizations.edu',
            'password' => Hash::make('password'),
            'primary_role' => 'consultant', // Consultant role to see sub-organizations
            'phone' => '(555) 123-4567',
            'bio' => 'Administrative Role of Lincoln County Organization Section. 30+ years in education. Passionate about data-driven participant success.',
            'avatar_url' => 'https://randomuser.me/api/portraits/women/79.jpg',
            'active' => true,
            'suspended' => false,
        ]);

        // Assign consultant access to Lincoln High Organization (can view and push content)
        $administrative_role->organizations()->attach($organization->id, [
            'role' => 'consultant',
            'is_primary' => false,
            'can_manage' => true,
        ]);

        // Create admin user at organization level - Principal Michael Torres
        User::create([
            'org_id' => $organization->id,
            'first_name' => 'Michael',
            'last_name' => 'Torres',
            'email' => 'mtorres@lincolnhigh.edu',
            'password' => Hash::make('password'),
            'primary_role' => 'admin',
            'phone' => '(555) 234-5678',
            'bio' => 'Principal of Lincoln High Organization. Dedicated to fostering academic excellence and participant well-being.',
            'avatar_url' => 'https://randomuser.me/api/portraits/men/52.jpg',
            'active' => true,
            'suspended' => false,
        ]);

        // Create support_person
        $support_person = User::create([
            'org_id' => $organization->id,
            'first_name' => 'Emily',
            'last_name' => 'Rodriguez',
            'email' => 'erodriguez@lincolnhigh.edu',
            'password' => Hash::make('password'),
            'primary_role' => 'admin',
            'phone' => '(555) 345-6789',
            'avatar_url' => 'https://randomuser.me/api/portraits/women/65.jpg',
            'active' => true,
            'suspended' => false,
        ]);

        // Create instructors with gender-appropriate avatars
        $instructors = [
            ['first_name' => 'James', 'last_name' => 'Wilson', 'email' => 'jwilson@lincolnhigh.edu', 'gender' => 'men', 'img' => 32],
            ['first_name' => 'Maria', 'last_name' => 'Garcia', 'email' => 'mgarcia@lincolnhigh.edu', 'gender' => 'women', 'img' => 44],
            ['first_name' => 'David', 'last_name' => 'Lee', 'email' => 'dlee@lincolnhigh.edu', 'gender' => 'men', 'img' => 45],
            ['first_name' => 'Sarah', 'last_name' => 'Thompson', 'email' => 'sthompson@lincolnhigh.edu', 'gender' => 'women', 'img' => 28],
        ];

        foreach ($instructors as $instructor) {
            User::create([
                'org_id' => $organization->id,
                'first_name' => $instructor['first_name'],
                'last_name' => $instructor['last_name'],
                'email' => $instructor['email'],
                'password' => Hash::make('password'),
                'primary_role' => 'instructor',
                'avatar_url' => 'https://randomuser.me/api/portraits/'.$instructor['gender'].'/'.$instructor['img'].'.jpg',
                'active' => true,
                'suspended' => false,
            ]);
        }

        // Create participant users (these will be linked to Participant records)
        // Using randomuser.me portraits - reliable static URLs
        $learnerData = [
            ['first_name' => 'Alex', 'last_name' => 'Johnson', 'gender' => 'men', 'img' => 1],
            ['first_name' => 'Emma', 'last_name' => 'Williams', 'gender' => 'women', 'img' => 1],
            ['first_name' => 'Liam', 'last_name' => 'Brown', 'gender' => 'men', 'img' => 2],
            ['first_name' => 'Olivia', 'last_name' => 'Davis', 'gender' => 'women', 'img' => 2],
            ['first_name' => 'Noah', 'last_name' => 'Miller', 'gender' => 'men', 'img' => 3],
            ['first_name' => 'Ava', 'last_name' => 'Wilson', 'gender' => 'women', 'img' => 3],
            ['first_name' => 'Ethan', 'last_name' => 'Moore', 'gender' => 'men', 'img' => 4],
            ['first_name' => 'Sophia', 'last_name' => 'Taylor', 'gender' => 'women', 'img' => 4],
            ['first_name' => 'Mason', 'last_name' => 'Anderson', 'gender' => 'men', 'img' => 5],
            ['first_name' => 'Isabella', 'last_name' => 'Thomas', 'gender' => 'women', 'img' => 5],
            ['first_name' => 'Lucas', 'last_name' => 'Jackson', 'gender' => 'men', 'img' => 6],
            ['first_name' => 'Mia', 'last_name' => 'White', 'gender' => 'women', 'img' => 6],
            ['first_name' => 'Jacob', 'last_name' => 'Harris', 'gender' => 'men', 'img' => 7],
            ['first_name' => 'Charlotte', 'last_name' => 'Martin', 'gender' => 'women', 'img' => 7],
            ['first_name' => 'William', 'last_name' => 'Garcia', 'gender' => 'men', 'img' => 8],
            ['first_name' => 'Amelia', 'last_name' => 'Martinez', 'gender' => 'women', 'img' => 8],
            ['first_name' => 'Benjamin', 'last_name' => 'Robinson', 'gender' => 'men', 'img' => 9],
            ['first_name' => 'Harper', 'last_name' => 'Clark', 'gender' => 'women', 'img' => 9],
            ['first_name' => 'James', 'last_name' => 'Lewis', 'gender' => 'men', 'img' => 10],
            ['first_name' => 'Evelyn', 'last_name' => 'Lee', 'gender' => 'women', 'img' => 10],
            ['first_name' => 'Henry', 'last_name' => 'Walker', 'gender' => 'men', 'img' => 11],
            ['first_name' => 'Luna', 'last_name' => 'Hall', 'gender' => 'women', 'img' => 11],
            ['first_name' => 'Alexander', 'last_name' => 'Allen', 'gender' => 'men', 'img' => 12],
            ['first_name' => 'Chloe', 'last_name' => 'Young', 'gender' => 'women', 'img' => 12],
            ['first_name' => 'Daniel', 'last_name' => 'King', 'gender' => 'men', 'img' => 13],
        ];

        foreach ($learnerData as $participant) {
            $email = strtolower($participant['first_name'][0].$participant['last_name']).'@participant.lincolnhigh.edu';
            $avatarUrl = 'https://randomuser.me/api/portraits/'.$participant['gender'].'/'.$participant['img'].'.jpg';
            User::create([
                'org_id' => $organization->id,
                'first_name' => $participant['first_name'],
                'last_name' => $participant['last_name'],
                'email' => $email,
                'password' => Hash::make('password'),
                'primary_role' => 'participant',
                'avatar_url' => $avatarUrl,
                'active' => true,
                'suspended' => false,
            ]);
        }
    }
}
