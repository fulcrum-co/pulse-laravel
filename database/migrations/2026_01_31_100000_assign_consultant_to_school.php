<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Find Margaret Chen (consultant) and Lincoln High Organization
        $consultant = DB::table('users')->where('email', 'mchen@lincolnorganizations.edu')->first();
        $organization = DB::table('organizations')->where('org_type', 'organization')->first();

        if ($consultant && $organization) {
            // Add assignment if it doesn't exist
            $exists = DB::table('user_organizations')
                ->where('user_id', $consultant->id)
                ->where('organization_id', $organization->id)
                ->exists();

            if (! $exists) {
                DB::table('user_organizations')->insert([
                    'user_id' => $consultant->id,
                    'organization_id' => $organization->id,
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
        $consultant = DB::table('users')->where('email', 'mchen@lincolnorganizations.edu')->first();
        $organization = DB::table('organizations')->where('org_type', 'organization')->first();

        if ($consultant && $organization) {
            DB::table('user_organizations')
                ->where('user_id', $consultant->id)
                ->where('organization_id', $organization->id)
                ->delete();
        }
    }
};
