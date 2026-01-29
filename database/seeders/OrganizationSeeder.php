<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // Create a sample school district
        $district = Organization::create([
            'org_type' => 'district',
            'org_name' => 'Lincoln County School District',
            'primary_contact_name' => 'Dr. Sarah Johnson',
            'primary_contact_email' => 'sjohnson@lincolnschools.edu',
            'primary_contact_phone' => '(555) 123-4567',
            'address' => [
                'street' => '100 Education Way',
                'city' => 'Lincoln',
                'state' => 'CA',
                'zip' => '90210',
            ],
            'timezone' => 'America/Los_Angeles',
            'subscription_plan' => 'enterprise',
            'subscription_status' => 'active',
            'active' => true,
        ]);

        // Create a sample school under the district
        Organization::create([
            'org_type' => 'school',
            'org_name' => 'Lincoln High School',
            'parent_org_id' => $district->id,
            'primary_contact_name' => 'Principal Michael Chen',
            'primary_contact_email' => 'mchen@lincolnhigh.edu',
            'primary_contact_phone' => '(555) 234-5678',
            'address' => [
                'street' => '500 Falcon Drive',
                'city' => 'Lincoln',
                'state' => 'CA',
                'zip' => '90210',
            ],
            'timezone' => 'America/Los_Angeles',
            'subscription_status' => 'active',
            'active' => true,
        ]);
    }
}
