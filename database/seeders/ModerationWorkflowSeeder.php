<?php

namespace Database\Seeders;

use App\Models\ModerationSlaConfig;
use App\Models\ModerationTeamSetting;
use App\Models\ModerationWorkflow;
use App\Models\Organization;
use App\Models\User;
use App\Models\Workflow;
use App\Services\Moderation\ModerationWorkflowService;
use Illuminate\Database\Seeder;

class ModerationWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding moderation workflows...');

        // Get all organizations
        $organizations = Organization::all();

        if ($organizations->isEmpty()) {
            $this->command->warn('No organizations found. Skipping moderation workflow seeding.');

            return;
        }

        foreach ($organizations as $org) {
            $this->seedOrganization($org);
        }

        $this->command->info('Moderation workflow seeding complete!');
    }

    protected function seedOrganization(Organization $org): void
    {
        $this->command->info("Seeding moderation for: {$org->org_name}");

        // 1. Create default SLA configs
        $this->seedSlaConfigs($org);

        // 2. Create default workflow
        $this->seedDefaultWorkflow($org);

        // 3. Create team settings for eligible moderators
        $this->seedTeamSettings($org);
    }

    protected function seedSlaConfigs(Organization $org): void
    {
        ModerationSlaConfig::createDefaultsForOrganization($org->id);
        $this->command->info('  - Created SLA configs');
    }

    protected function seedDefaultWorkflow(Organization $org): void
    {
        // Check if default workflow already exists
        $existingDefault = ModerationWorkflow::forOrganization($org->id)
            ->default()
            ->first();

        if ($existingDefault) {
            $this->command->info('  - Default workflow already exists');

            return;
        }

        // Create the default workflow
        ModerationWorkflowService::createDefaultWorkflow($org->id);
        $this->command->info('  - Created default moderation workflow');
    }

    protected function seedTeamSettings(Organization $org): void
    {
        // Find eligible moderators
        $moderators = User::where('org_id', $org->id)
            ->whereIn('primary_role', ['admin', 'consultant', 'administrative_role', 'organization_admin', 'support_person'])
            ->get();

        if ($moderators->isEmpty()) {
            $this->command->info('  - No eligible moderators found');

            return;
        }

        foreach ($moderators as $moderator) {
            // Skip if already has settings
            $existing = ModerationTeamSetting::where('org_id', $org->id)
                ->where('user_id', $moderator->id)
                ->first();

            if ($existing) {
                continue;
            }

            // Determine specializations based on role
            $specializations = $this->determineSpecializations($moderator);

            ModerationTeamSetting::create([
                'org_id' => $org->id,
                'user_id' => $moderator->id,
                'content_specializations' => $specializations,
                'max_concurrent_items' => $this->determineMaxLoad($moderator),
                'auto_assign_enabled' => true,
                'schedule' => null, // No schedule restrictions by default
                'current_load' => 0,
            ]);
        }

        $this->command->info("  - Created team settings for {$moderators->count()} moderators");
    }

    protected function determineSpecializations(User $user): array
    {
        // Support Persons specialize in wellness and social-emotional content
        if ($user->primary_role === 'support_person') {
            return [
                ModerationTeamSetting::SPEC_WELLNESS,
                ModerationTeamSetting::SPEC_SOCIAL_EMOTIONAL,
                ModerationTeamSetting::SPEC_CRISIS,
            ];
        }

        // Admins and consultants can handle all types
        if (in_array($user->primary_role, ['admin', 'consultant', 'administrative_role'])) {
            return [
                ModerationTeamSetting::SPEC_WELLNESS,
                ModerationTeamSetting::SPEC_ACADEMIC,
                ModerationTeamSetting::SPEC_SOCIAL_EMOTIONAL,
                ModerationTeamSetting::SPEC_CAREER,
                ModerationTeamSetting::SPEC_CRISIS,
            ];
        }

        // Organization admins - academic and career focus
        return [
            ModerationTeamSetting::SPEC_ACADEMIC,
            ModerationTeamSetting::SPEC_CAREER,
        ];
    }

    protected function determineMaxLoad(User $user): int
    {
        // Support Persons have higher capacity for content review
        if ($user->primary_role === 'support_person') {
            return 15;
        }

        // Admins/consultants moderate capacity
        if (in_array($user->primary_role, ['admin', 'consultant', 'administrative_role'])) {
            return 10;
        }

        // Organization admins - lower capacity (have other duties)
        return 5;
    }
}
