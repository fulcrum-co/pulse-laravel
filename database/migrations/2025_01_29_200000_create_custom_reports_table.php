<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('custom_reports')) {
            return;
        }

        Schema::create('custom_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('org_id')->nullable()->index();
            $table->unsignedBigInteger('team_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('last_edited_by')->nullable();

            // Report identification
            $table->string('report_name');
            $table->text('report_description')->nullable();
            $table->string('report_type')->default('custom')->index();
            $table->string('status')->default('draft')->index();
            $table->string('public_token', 64)->nullable()->unique();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedInteger('version')->default(1);

            // Report configuration (JSON fields)
            $table->json('report_variables')->nullable();
            $table->json('report_operations')->nullable();
            $table->json('report_period')->nullable();
            $table->json('report_layout')->nullable();
            $table->json('page_settings')->nullable();
            $table->json('branding')->nullable();
            $table->json('filters')->nullable();
            $table->json('assigned_to')->nullable();
            $table->json('distribution_schedule')->nullable();
            $table->json('snapshot_data')->nullable();

            // LLM/AI settings
            $table->boolean('generate_llm_narrative')->default(false);
            $table->text('llm_narrative_prompt')->nullable();
            $table->timestamp('llm_narrative_last_generated')->nullable();

            // Flags
            $table->boolean('auto_send')->default(false);
            $table->boolean('anonymous_user_included')->default(false);
            $table->boolean('is_live')->default(true);

            // Media
            $table->string('thumbnail_path')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('org_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('last_edited_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_reports');
    }
};
