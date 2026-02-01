<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Find Margaret Chen (consultant) and Lincoln High School
        $consultant = DB::table('users')->where('email', 'mchen@lincolnschools.edu')->first();
        $school = DB::table('organizations')->where('org_type', 'school')->first();

        if ($consultant && $school) {
            // Add assignment if it doesn't exist
            $exists = DB::table('user_organizations')
                ->where('user_id', $consultant->id)
                ->where('organization_id', $school->id)
                ->exists();

            if (! $exists) {
                DB::table('user_organizations')->insert([
                    'user_id' => $consultant->id,
                    'organization_id' => $school->id,
                    'role' => 'consultant',
                    'is_primary' => false,
                    'can_manage' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $consultant = DB::table('users')->where('email', 'mchen@lincolnschools.edu')->first();
        $school = DB::table('organizations')->where('org_type', 'school')->first();

        if ($consultant && $school) {
            DB::table('user_organizations')
                ->where('user_id', $consultant->id)
                ->where('organization_id', $school->id)
                ->delete();
        }
    }
};
