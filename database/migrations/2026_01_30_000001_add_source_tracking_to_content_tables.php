<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add source tracking columns to surveys, custom_reports, and resources
     * to enable push/distribution from parent organizations to child organizations.
     */
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('resource_distributions')) {
            return;
        }

        // Add source tracking to surveys
        Schema::table('surveys', function (Blueprint $table) {
            $table->foreignId('source_survey_id')->nullable()->after('org_id')
                ->constrained('surveys')->nullOnDelete();
            $table->foreignId('source_org_id')->nullable()->after('source_survey_id')
                ->constrained('organizations')->nullOnDelete();
        });

        // Add source tracking to custom_reports
        Schema::table('custom_reports', function (Blueprint $table) {
            $table->foreignId('source_report_id')->nullable()->after('org_id')
                ->constrained('custom_reports')->nullOnDelete();
            $table->foreignId('source_org_id')->nullable()->after('source_report_id')
                ->constrained('organizations')->nullOnDelete();
        });

        // Add source tracking to resources
        Schema::table('resources', function (Blueprint $table) {
            $table->foreignId('source_resource_id')->nullable()->after('org_id')
                ->constrained('resources')->nullOnDelete();
            $table->foreignId('source_org_id')->nullable()->after('source_resource_id')
                ->constrained('organizations')->nullOnDelete();
        });

        // Create resource_distributions table for multi-org sharing
        Schema::create('resource_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('target_org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('distributed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['resource_id', 'target_org_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_distributions');

        Schema::table('resources', function (Blueprint $table) {
            $table->dropForeign(['source_resource_id']);
            $table->dropForeign(['source_org_id']);
            $table->dropColumn(['source_resource_id', 'source_org_id']);
        });

        Schema::table('custom_reports', function (Blueprint $table) {
            $table->dropForeign(['source_report_id']);
            $table->dropForeign(['source_org_id']);
            $table->dropColumn(['source_report_id', 'source_org_id']);
        });

        Schema::table('surveys', function (Blueprint $table) {
            $table->dropForeign(['source_survey_id']);
            $table->dropForeign(['source_org_id']);
            $table->dropColumn(['source_survey_id', 'source_org_id']);
        });
    }
};
