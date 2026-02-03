<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cohort_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_id')->constrained('cohorts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('student'); // student, mentor, facilitator, admin
            $table->string('status')->default('enrolled'); // enrolled, active, completed, withdrawn, paused
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->foreignId('current_step_id')->nullable()->constrained('mini_course_steps')->nullOnDelete();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('enrollment_source')->default('manual'); // manual, self_enrolled, bulk_import, api
            $table->text('notes')->nullable();
            $table->json('feedback')->nullable();
            $table->json('analytics_data')->nullable();
            // Lead generation fields
            $table->string('lead_source')->nullable(); // widget, landing_page, referral, organic
            $table->string('lead_source_url')->nullable(); // URL where they came from
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->unsignedInteger('lead_score')->default(0);
            $table->timestamps();

            $table->unique(['cohort_id', 'user_id']);
            $table->index(['user_id', 'status']);
            $table->index(['cohort_id', 'role']);
            $table->index(['cohort_id', 'status']);
            $table->index(['lead_source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cohort_members');
    }
};
