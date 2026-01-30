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
        $school = Organization::where('org_type', 'school')->first();

        // Create admin user
        User::create([
            'org_id' => $school->id,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@lincolnhigh.edu',
            'password' => Hash::make('password'),
            'primary_role' => 'admin',
            'avatar_url' => 'https://xsgames.co/randomusers/avatar.php?g=m&t=admin',
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
            'avatar_url' => 'https://xsgames.co/randomusers/avatar.php?g=f&t=counselor',
            'active' => true,
            'suspended' => false,
        ]);

        // Create teachers with gender-appropriate avatars
        $teachers = [
            ['first_name' => 'James', 'last_name' => 'Wilson', 'email' => 'jwilson@lincolnhigh.edu', 'gender' => 'm'],
            ['first_name' => 'Maria', 'last_name' => 'Garcia', 'email' => 'mgarcia@lincolnhigh.edu', 'gender' => 'f'],
            ['first_name' => 'David', 'last_name' => 'Lee', 'email' => 'dlee@lincolnhigh.edu', 'gender' => 'm'],
            ['first_name' => 'Sarah', 'last_name' => 'Thompson', 'email' => 'sthompson@lincolnhigh.edu', 'gender' => 'f'],
        ];

        foreach ($teachers as $index => $teacher) {
            User::create([
                'org_id' => $school->id,
                'first_name' => $teacher['first_name'],
                'last_name' => $teacher['last_name'],
                'email' => $teacher['email'],
                'password' => Hash::make('password'),
                'primary_role' => 'teacher',
                'avatar_url' => 'https://xsgames.co/randomusers/avatar.php?g=' . $teacher['gender'] . '&t=' . ($index + 100),
                'active' => true,
                'suspended' => false,
            ]);
        }

        // Create student users (these will be linked to Student records)
        // Gender: m = male, f = female for avatar selection
        $studentData = [
            ['first_name' => 'Alex', 'last_name' => 'Johnson', 'gender' => 'm'],
            ['first_name' => 'Emma', 'last_name' => 'Williams', 'gender' => 'f'],
            ['first_name' => 'Liam', 'last_name' => 'Brown', 'gender' => 'm'],
            ['first_name' => 'Olivia', 'last_name' => 'Davis', 'gender' => 'f'],
            ['first_name' => 'Noah', 'last_name' => 'Miller', 'gender' => 'm'],
            ['first_name' => 'Ava', 'last_name' => 'Wilson', 'gender' => 'f'],
            ['first_name' => 'Ethan', 'last_name' => 'Moore', 'gender' => 'm'],
            ['first_name' => 'Sophia', 'last_name' => 'Taylor', 'gender' => 'f'],
            ['first_name' => 'Mason', 'last_name' => 'Anderson', 'gender' => 'm'],
            ['first_name' => 'Isabella', 'last_name' => 'Thomas', 'gender' => 'f'],
            ['first_name' => 'Lucas', 'last_name' => 'Jackson', 'gender' => 'm'],
            ['first_name' => 'Mia', 'last_name' => 'White', 'gender' => 'f'],
            ['first_name' => 'Jacob', 'last_name' => 'Harris', 'gender' => 'm'],
            ['first_name' => 'Charlotte', 'last_name' => 'Martin', 'gender' => 'f'],
            ['first_name' => 'William', 'last_name' => 'Garcia', 'gender' => 'm'],
            ['first_name' => 'Amelia', 'last_name' => 'Martinez', 'gender' => 'f'],
            ['first_name' => 'Benjamin', 'last_name' => 'Robinson', 'gender' => 'm'],
            ['first_name' => 'Harper', 'last_name' => 'Clark', 'gender' => 'f'],
            ['first_name' => 'James', 'last_name' => 'Lewis', 'gender' => 'm'],
            ['first_name' => 'Evelyn', 'last_name' => 'Lee', 'gender' => 'f'],
            ['first_name' => 'Henry', 'last_name' => 'Walker', 'gender' => 'm'],
            ['first_name' => 'Luna', 'last_name' => 'Hall', 'gender' => 'f'],
            ['first_name' => 'Alexander', 'last_name' => 'Allen', 'gender' => 'm'],
            ['first_name' => 'Chloe', 'last_name' => 'Young', 'gender' => 'f'],
            ['first_name' => 'Daniel', 'last_name' => 'King', 'gender' => 'm'],
        ];

        foreach ($studentData as $index => $student) {
            $email = strtolower($student['first_name'][0] . $student['last_name']) . '@student.lincolnhigh.edu';
            // Use xsgames.co for reliable, gender-appropriate avatars
            $avatarUrl = 'https://xsgames.co/randomusers/avatar.php?g=' . $student['gender'] . '&' . ($index + 1);
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
