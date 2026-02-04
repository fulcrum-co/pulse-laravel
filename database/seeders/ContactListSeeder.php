<?php

namespace Database\Seeders;

use App\Models\ContactList;
use App\Models\Organization;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class ContactListSeeder extends Seeder
{
    /**
     * Seed contact lists with members.
     */
    public function run(): void
    {
        $org = Organization::first();
        $user = User::where('org_id', $org->id)->first();

        if (! $org || ! $user) {
            $this->command->warn('No organization or user found. Skipping contact list seeder.');

            return;
        }

        // Get existing students
        $students = Student::where('org_id', $org->id)->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found. Skipping contact list seeder.');

            return;
        }

        // Create "All Students" contact list
        $allStudentsList = ContactList::firstOrCreate([
            'org_id' => $org->id,
            'name' => 'All Students',
        ], [
            'description' => 'All students in the organization',
            'list_type' => ContactList::TYPE_STUDENT,
            'is_dynamic' => false,
            'created_by' => $user->id,
        ]);

        // Add all students to the list
        $allStudentsList->addContacts($students->pluck('id')->toArray(), [], $user->id);
        $this->command->info("Created 'All Students' list with {$students->count()} contacts.");

        // Create "At Risk Students" list (students with risk_level)
        $atRiskStudents = $students->filter(fn ($s) => in_array($s->risk_level, ['high', 'medium']));
        if ($atRiskStudents->isNotEmpty()) {
            $atRiskList = ContactList::firstOrCreate([
                'org_id' => $org->id,
                'name' => 'At Risk Students',
            ], [
                'description' => 'Students identified as at-risk',
                'list_type' => ContactList::TYPE_STUDENT,
                'is_dynamic' => false,
                'created_by' => $user->id,
            ]);
            $atRiskList->addContacts($atRiskStudents->pluck('id')->toArray(), [], $user->id);
            $this->command->info("Created 'At Risk Students' list with {$atRiskStudents->count()} contacts.");
        }

        // Create a grade-specific list
        $gradeGroups = $students->groupBy('grade_level')->filter(fn ($group, $grade) => $grade);
        foreach ($gradeGroups->take(3) as $grade => $gradeStudents) {
            $gradeList = ContactList::firstOrCreate([
                'org_id' => $org->id,
                'name' => "Grade {$grade} Students",
            ], [
                'description' => "Students in grade {$grade}",
                'list_type' => ContactList::TYPE_STUDENT,
                'is_dynamic' => false,
                'created_by' => $user->id,
            ]);
            $gradeList->addContacts($gradeStudents->pluck('id')->toArray(), [], $user->id);
            $this->command->info("Created 'Grade {$grade} Students' list with {$gradeStudents->count()} contacts.");
        }

        // Create "Weekly Update Recipients" - for distribution testing
        $weeklyList = ContactList::firstOrCreate([
            'org_id' => $org->id,
            'name' => 'Weekly Update Recipients',
        ], [
            'description' => 'Contacts who receive weekly progress updates',
            'list_type' => ContactList::TYPE_STUDENT,
            'is_dynamic' => false,
            'created_by' => $user->id,
        ]);
        $weeklyList->addContacts($students->take(10)->pluck('id')->toArray(), [], $user->id);
        $this->command->info("Created 'Weekly Update Recipients' list with " . min(10, $students->count()) . " contacts.");
    }
}
