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
        if (Schema::hasTable('contact_metrics')) {
            return;
        }

        Schema::create('contact_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();

            // Polymorphic contact reference (Student or User)
            $table->string('contact_type');
            $table->unsignedBigInteger('contact_id');

            // Metric identification
            $table->string('metric_category'); // academics, attendance, behavior, life_skills, wellness, engagement, pd
            $table->string('metric_key'); // gpa, attendance_rate, emotional_wellbeing, plan_progress
            $table->string('metric_label')->nullable();

            // Value storage (flexible for different data types)
            $table->decimal('numeric_value', 10, 4)->nullable();
            $table->string('text_value')->nullable();
            $table->json('json_value')->nullable();

            // Scoring/Thresholds
            $table->decimal('normalized_score', 5, 2)->nullable(); // 0-100 normalized
            $table->string('status')->nullable(); // on_track, at_risk, off_track, not_started

            // Data source tracking
            $table->string('source_type'); // sis_api, survey, manual, calculated, conversation
            $table->string('source_id')->nullable(); // External ID from source system
            $table->foreignId('source_survey_attempt_id')->nullable()->constrained('survey_attempts')->nullOnDelete();

            // Time dimension
            $table->date('period_start');
            $table->date('period_end');
            $table->string('period_type')->default('point_in_time'); // point_in_time, daily, weekly, quarter, semester, year
            $table->string('school_year')->nullable(); // 2024-2025
            $table->tinyInteger('quarter')->nullable(); // 1-4

            // Audit/FERPA
            $table->foreignId('recorded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at');
            $table->boolean('is_pii')->default(false);
            $table->boolean('requires_consent')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for efficient querying
            $table->index(['contact_type', 'contact_id', 'metric_category']);
            $table->index(['org_id', 'metric_key', 'period_start']);
            $table->index(['org_id', 'contact_type', 'school_year', 'quarter']);
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_metrics');
    }
};
