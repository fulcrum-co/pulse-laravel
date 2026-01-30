<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adaptive_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_type'); // course_suggestion, course_edit, provider_recommendation, intervention_alert
            $table->json('input_sources')->nullable(); // quantitative, qualitative, behavioral, explicit
            $table->json('conditions')->nullable(); // Rule configuration
            $table->boolean('ai_interpretation_enabled')->default(false);
            $table->text('ai_prompt_context')->nullable();
            $table->string('output_action'); // suggest_for_review, auto_create, auto_enroll, notify
            $table->json('output_config')->nullable();
            $table->unsignedSmallInteger('cooldown_hours')->default(24);
            $table->boolean('active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->unsignedInteger('triggered_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['org_id', 'active']);
            $table->index(['trigger_type']);
            $table->index(['output_action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adaptive_triggers');
    }
};
