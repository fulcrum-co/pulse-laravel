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
        $district = Organization::where('org_type', 'district')->first();
        $school = Organization::where('org_type', 'school')->first();

        // Create superintendent at district level - Dr. Margaret Chen
        // An experienced educator in her late 50s who oversees the entire district
        User::create([
            'org_id' => $district->id,
            'first_name' => 'Margaret',
            'last_name' => 'Chen',
            'email' => 'mchen@lincolnschools.edu',
            'password' => Hash::make('password'),
            'primary_role' => 'consultant', // Consultant role to see sub-organizations
            'phone' => '(555) 123-4567',
            'bio' => 'Superintendent of Lincoln County School District. 30+ years in education. Passionate about data-driven student success.',
            'avatar_url' => 'https://randomuser.me/api/portraits/women/79.jpg',
            'active' => true,
            'suspended' => false,
        ]);

        // Create admin user at school level
        User::create([
            'org_id' => $school->id,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@lincolnhigh.edu',
            'password' => Hash::make('password'),
            'primary_role' => 'admin',
            'avatar_url' => 'https://randomuser.me/api/portraits/men/75.jpg',
            'active' => true,
            'suspended' => false,
        ]);

        // Create counselor
        $counselor = User::create([
            'org_id' => $school->id,
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

        // Create teachers with gender-appropriate avatars
        $teachers = [
            ['first_name' => 'James', 'last_name' => 'Wilson', 'email' => 'jwilson@lincolnhigh.edu', 'gender' => 'men', 'img' => 32],
            ['first_name' => 'Maria', 'last_name' => 'Garcia', 'email' => 'mgarcia@lincolnhigh.edu', 'gender' => 'women', 'img' => 44],
            ['first_name' => 'David', 'last_name' => 'Lee', 'email' => 'dlee@lincolnhigh.edu', 'gender' => 'men', 'img' => 45],
            ['first_name' => 'Sarah', 'last_name' => 'Thompson', 'email' => 'sthompson@lincolnhigh.edu', 'gender' => 'women', 'img' => 28],
        ];

        foreach ($teachers as $teacher) {
            User::create([
                'org_id' => $school->id,
                'first_name' => $teacher['first_name'],
                'last_name' => $teacher['last_name'],
                'email' => $teacher['email'],
                'password' => Hash::make('password'),
                'primary_role' => 'teacher',
                'avatar_url' => 'https://randomuser.me/api/portraits/' . $teacher['gender'] . '/' . $teacher['img'] . '.jpg',
                'active' => true,
                'suspended' => false,
            ]);
        }

        // Create student users (these will be linked to Student records)
        // Using randomuser.me portraits - reliable static URLs
        $studentData = [
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

        foreach ($studentData as $student) {
            $email = strtolower($student['first_name'][0] . $student['last_name']) . '@student.lincolnhigh.edu';
            $avatarUrl = 'https://randomuser.me/api/portraits/' . $student['gender'] . '/' . $student['img'] . '.jpg';
            User::create([
                'org_id' => $school->id,
                'first_name' => $student['first_name'],
                'last_name' => $student['last_name'],
                'email' => $email,
                'password' => Hash::make('password'),
                'primary_role' => 'student',
                'avatar_url' => $avatarUrl,
                'active' => true,
                'suspended' => false,
            ]);
        }
    }
}
