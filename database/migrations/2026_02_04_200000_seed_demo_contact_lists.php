<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all organizations
        $orgs = DB::table('organizations')->get();

        foreach ($orgs as $org) {
            // Get the first user from this org to set as creator
            $user = DB::table('users')->where('org_id', $org->id)->first();
            if (! $user) {
                continue;
            }

            // Check if contact lists already exist for this org
            $existing = DB::table('contact_lists')->where('org_id', $org->id)->count();
            if ($existing > 0) {
                continue;
            }

            // Create "All Contacts" list
            $allContactsId = DB::table('contact_lists')->insertGetId([
                'org_id' => $org->id,
                'name' => 'All Contacts',
                'description' => 'All contacts in the organization',
                'list_type' => 'student',
                'is_dynamic' => false,
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create "Weekly Report Recipients" list
            $weeklyId = DB::table('contact_lists')->insertGetId([
                'org_id' => $org->id,
                'name' => 'Weekly Report Recipients',
                'description' => 'Contacts who receive weekly progress reports',
                'list_type' => 'student',
                'is_dynamic' => false,
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create "Priority Contacts" list
            $priorityId = DB::table('contact_lists')->insertGetId([
                'org_id' => $org->id,
                'name' => 'Priority Contacts',
                'description' => 'High-priority contacts requiring attention',
                'list_type' => 'student',
                'is_dynamic' => false,
                'created_by' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Get students for this org
            $students = DB::table('students')->where('org_id', $org->id)->get();

            // Add all students to "All Contacts" list
            foreach ($students as $student) {
                DB::table('contact_list_members')->insert([
                    'contact_list_id' => $allContactsId,
                    'contact_type' => 'App\\Models\\Student',
                    'contact_id' => $student->id,
                    'added_at' => now(),
                    'added_by' => $user->id,
                ]);
            }

            // Add first 10 students to "Weekly Report Recipients"
            foreach ($students->take(10) as $student) {
                DB::table('contact_list_members')->insert([
                    'contact_list_id' => $weeklyId,
                    'contact_type' => 'App\\Models\\Student',
                    'contact_id' => $student->id,
                    'added_at' => now(),
                    'added_by' => $user->id,
                ]);
            }

            // Add first 5 students to "Priority Contacts"
            foreach ($students->take(5) as $student) {
                DB::table('contact_list_members')->insert([
                    'contact_list_id' => $priorityId,
                    'contact_type' => 'App\\Models\\Student',
                    'contact_id' => $student->id,
                    'added_at' => now(),
                    'added_by' => $user->id,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only delete the specific lists created by this migration
        DB::table('contact_lists')
            ->whereIn('name', ['All Contacts', 'Weekly Report Recipients', 'Priority Contacts'])
            ->delete();
    }
};
