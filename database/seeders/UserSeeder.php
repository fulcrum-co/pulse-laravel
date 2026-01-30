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
            'avatar_url' => 'https://i.pravatar.cc/150?u=admin@lincolnhigh.edu',
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
            'avatar_url' => 'https://i.pravatar.cc/150?u=erodriguez@lincolnhigh.edu',
            'active' => true,
            'suspended' => false,
        ]);

        // Create teachers
        $teachers = [
            ['first_name' => 'James', 'last_name' => 'Wilson', 'email' => 'jwilson@lincolnhigh.edu'],
            ['first_name' => 'Maria', 'last_name' => 'Garcia', 'email' => 'mgarcia@lincolnhigh.edu'],
            ['first_name' => 'David', 'last_name' => 'Lee', 'email' => 'dlee@lincolnhigh.edu'],
            ['first_name' => 'Sarah', 'last_name' => 'Thompson', 'email' => 'sthompson@lincolnhigh.edu'],
        ];

        foreach ($teachers as $teacher) {
            User::create([
                'org_id' => $school->id,
                'first_name' => $teacher['first_name'],
                'last_name' => $teacher['last_name'],
                'email' => $teacher['email'],
                'password' => Hash::make('password'),
                'primary_role' => 'teacher',
                'avatar_url' => 'https://i.pravatar.cc/150?u=' . $teacher['email'],
                'active' => true,
                'suspended' => false,
            ]);
        }

        // Create student users (these will be linked to Student records)
        $studentData = [
            ['first_name' => 'Alex', 'last_name' => 'Johnson'],
            ['first_name' => 'Emma', 'last_name' => 'Williams'],
            ['first_name' => 'Liam', 'last_name' => 'Brown'],
            ['first_name' => 'Olivia', 'last_name' => 'Davis'],
            ['first_name' => 'Noah', 'last_name' => 'Miller'],
            ['first_name' => 'Ava', 'last_name' => 'Wilson'],
            ['first_name' => 'Ethan', 'last_name' => 'Moore'],
            ['first_name' => 'Sophia', 'last_name' => 'Taylor'],
            ['first_name' => 'Mason', 'last_name' => 'Anderson'],
            ['first_name' => 'Isabella', 'last_name' => 'Thomas'],
            ['first_name' => 'Lucas', 'last_name' => 'Jackson'],
            ['first_name' => 'Mia', 'last_name' => 'White'],
            ['first_name' => 'Jacob', 'last_name' => 'Harris'],
            ['first_name' => 'Charlotte', 'last_name' => 'Martin'],
            ['first_name' => 'William', 'last_name' => 'Garcia'],
            ['first_name' => 'Amelia', 'last_name' => 'Martinez'],
            ['first_name' => 'Benjamin', 'last_name' => 'Robinson'],
            ['first_name' => 'Harper', 'last_name' => 'Clark'],
            ['first_name' => 'James', 'last_name' => 'Lewis'],
            ['first_name' => 'Evelyn', 'last_name' => 'Lee'],
            ['first_name' => 'Henry', 'last_name' => 'Walker'],
            ['first_name' => 'Luna', 'last_name' => 'Hall'],
            ['first_name' => 'Alexander', 'last_name' => 'Allen'],
            ['first_name' => 'Chloe', 'last_name' => 'Young'],
            ['first_name' => 'Daniel', 'last_name' => 'King'],
        ];

        foreach ($studentData as $index => $student) {
            $email = strtolower($student['first_name'][0] . $student['last_name']) . '@student.lincolnhigh.edu';
            User::create([
                'org_id' => $school->id,
                'first_name' => $student['first_name'],
                'last_name' => $student['last_name'],
                'email' => $email,
                'password' => Hash::make('password'),
                'primary_role' => 'student',
                'avatar_url' => 'https://i.pravatar.cc/150?u=' . $email,
                'active' => true,
                'suspended' => false,
            ]);
        }
    }
}
