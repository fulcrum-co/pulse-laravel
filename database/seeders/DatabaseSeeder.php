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
        $this->call([
            OrganizationSeeder::class,
            UserSeeder::class,
            StudentSeeder::class,
            QuestionBankSeeder::class,
            SurveyTemplateSeeder::class,
            SurveySeeder::class,
            ResourceSeeder::class,
            StrategySeeder::class,
            OkrPlanSeeder::class,
            ContactMetricSeeder::class,
            // Adaptive LMS seeders
            ProviderSeeder::class,
            ProgramSeeder::class,
            MiniCourseSeeder::class,
            AdaptiveTriggerSeeder::class,
            MarketplaceSeeder::class,
            // Reports
            ReportSeeder::class,
            // Billing
            CreditRateCardSeeder::class,
        ]);
    }
}
