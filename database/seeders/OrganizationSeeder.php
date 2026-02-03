<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        // Create a sample organization section
        $section = Organization::create([
            'org_type' => 'section',
            'org_name' => 'Lincoln County Organization Section',
            'primary_contact_name' => 'Dr. Sarah Johnson',
            'primary_contact_email' => 'sjohnson@lincolnorganizations.edu',
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

        // Create sample organizations under the section
        Organization::create([
            'org_type' => 'organization',
            'org_name' => 'Lincoln High Organization',
            'parent_org_id' => $section->id,
            'primary_contact_name' => 'Principal Michael Torres',
            'primary_contact_email' => 'mtorres@lincolnhigh.edu',
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

        Organization::create([
            'org_type' => 'organization',
            'org_name' => 'Washington Middle Organization',
            'parent_org_id' => $section->id,
            'primary_contact_name' => 'Principal Lisa Park',
            'primary_contact_email' => 'lpark@washingtonms.edu',
            'primary_contact_phone' => '(555) 234-5679',
            'address' => [
                'street' => '200 Eagle Lane',
                'city' => 'Lincoln',
                'state' => 'CA',
                'zip' => '90211',
            ],
            'timezone' => 'America/Los_Angeles',
            'subscription_status' => 'active',
            'active' => true,
        ]);

        Organization::create([
            'org_type' => 'organization',
            'org_name' => 'Jefferson Elementary',
            'parent_org_id' => $section->id,
            'primary_contact_name' => 'Principal Robert Kim',
            'primary_contact_email' => 'rkim@jeffersonelem.edu',
            'primary_contact_phone' => '(555) 234-5680',
            'address' => [
                'street' => '150 Bear Creek Road',
                'city' => 'Lincoln',
                'state' => 'CA',
                'zip' => '90212',
            ],
            'timezone' => 'America/Los_Angeles',
            'subscription_status' => 'active',
            'active' => true,
        ]);

        Organization::create([
            'org_type' => 'organization',
            'org_name' => 'Roosevelt Elementary',
            'parent_org_id' => $section->id,
            'primary_contact_name' => 'Principal Amanda Wright',
            'primary_contact_email' => 'awright@rooseveltelem.edu',
            'primary_contact_phone' => '(555) 234-5681',
            'address' => [
                'street' => '300 Oak Street',
                'city' => 'Lincoln',
                'state' => 'CA',
                'zip' => '90213',
            ],
            'timezone' => 'America/Los_Angeles',
            'subscription_status' => 'active',
            'active' => true,
        ]);
    }
}
