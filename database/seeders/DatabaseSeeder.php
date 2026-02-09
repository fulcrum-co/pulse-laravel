<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $useEnhancedDemo = true; // Set to false for basic demo

        // ============================================
        // PHASE 1: Foundation Data
        // ============================================
        $this->call([
            OrganizationSeeder::class,
            UserSeederV2::class, // ← NUCLEAR FIX: V2 bypasses OPcache
            QuestionBankSeeder::class,
            SurveyTemplateSeeder::class,
        ]);

        // ============================================
        // PHASE 2: Contacts/Students
        // ============================================
        if ($useEnhancedDemo) {
            $this->call([
                ContactEnhancedSeeder::class, // 50 contacts with risk distribution
            ]);
        } else {
            $this->call([
                StudentSeeder::class, // Basic student seeder
            ]);
        }

        $this->call([
            ProviderSeeder::class,
            ProgramSeeder::class,
            StrategySeeder::class,
        ]);

        // ============================================
        // PHASE 3: Surveys, Collections, Metrics, Notes
        // ============================================
        if ($useEnhancedDemo) {
            $this->call([
                SurveyEnhancedSeeder::class, // 6-8 surveys + 150-200 attempts
                CollectionEnhancedSeeder::class, // 10 collections + 300-400 entries
                ContactNotesSeeder::class, // 100-150 notes
            ]);
        } else {
            $this->call([
                SurveySeeder::class,
            ]);
        }

        $this->call([
            ContactMetricSeeder::class, // Already has 18mo historical data
        ]);

        // ============================================
        // PHASE 4: Resources & Courses
        // ============================================
        if ($useEnhancedDemo) {
            $this->call([
                ResourceEnhancedSeeder::class, // TODO: 25-30 resources
                MiniCourseEnhancedSeeder::class, // TODO: 12-15 courses
            ]);
        } else {
            $this->call([
                ResourceSeeder::class,
                MiniCourseSeeder::class,
            ]);
        }

        $this->call([
            AdaptiveTriggerSeeder::class,
        ]);

        // ============================================
        // PHASE 5: Plans
        // ============================================
        if ($useEnhancedDemo) {
            $this->call([
                StrategicPlanEnhancedSeeder::class, // 15-20 intervention plans
            ]);
        }

        $this->call([
            OkrPlanSeeder::class,
        ]);

        // ============================================
        // PHASE 6: Marketplace & Moderation
        // ============================================
        if ($useEnhancedDemo) {
            $this->call([
                MarketplaceEnhancedSeeder::class, // TODO: 30-35 items
                ModerationEnhancedSeeder::class, // TODO: 25-30 results
            ]);
        } else {
            $this->call([
                MarketplaceSeeder::class,
                ModerationDemoSeeder::class,
            ]);
        }

        // ============================================
        // PHASE 7: Reports & Distributions
        // ============================================
        if ($useEnhancedDemo) {
            $this->call([
                ReportEnhancedSeeder::class, // TODO: 20-25 reports
                DistributionEnhancedSeeder::class, // TODO: 8-10 campaigns
            ]);
        } else {
            $this->call([
                ReportSeeder::class,
            ]);
        }

        // ============================================
        // PHASE 8: Conversations & Contact Lists
        // ============================================
        if ($useEnhancedDemo) {
            $this->call([
                ConversationEnhancedSeeder::class, // TODO: Alert history
            ]);
        }

        $this->call([
            ContactListSeeder::class,
        ]);

        // ============================================
        // PHASE 9: Supplementary
        // ============================================
        $this->call([
            AlertDemoSeeder::class,
            HelpContentSeeder::class,
            PageHelpHintSeeder::class,
            CreditRateCardSeeder::class,
        ]);
    }
}
