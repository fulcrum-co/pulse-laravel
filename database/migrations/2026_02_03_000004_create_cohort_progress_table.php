<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cohort_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cohort_member_id')->constrained('cohort_members')->cascadeOnDelete();
            $table->foreignId('mini_course_step_id')->constrained('mini_course_steps')->cascadeOnDelete();
            $table->string('status')->default('not_started'); // not_started, in_progress, completed, skipped
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->json('response_data')->nullable(); // For reflections, quizzes, assessments
            $table->decimal('score', 5, 2)->nullable(); // Quiz/assessment score
            $table->json('feedback_response')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamps();

            $table->unique(['cohort_member_id', 'mini_course_step_id'], 'cohort_progress_member_step_unique');
            $table->index(['cohort_member_id', 'status']);
            $table->index(['mini_course_step_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cohort_progress');
    }
};
