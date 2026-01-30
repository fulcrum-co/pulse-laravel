<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mini_course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mini_course_id')->constrained('mini_courses')->cascadeOnDelete();
            $table->foreignId('mini_course_version_id')->nullable()->constrained('mini_course_versions')->nullOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('enrollment_source')->default('manual'); // manual, ai_suggested, rule_triggered, self_enrolled
            $table->unsignedBigInteger('suggestion_id')->nullable(); // FK added after suggestions table
            $table->string('status')->default('enrolled'); // enrolled, in_progress, completed, paused, withdrawn
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->foreignId('current_step_id')->nullable()->constrained('mini_course_steps')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->date('expected_completion_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('feedback')->nullable(); // Student feedback collection
            $table->json('analytics_data')->nullable(); // Engagement metrics
            $table->timestamps();

            // Indexes
            $table->index(['student_id', 'status']);
            $table->index(['mini_course_id', 'status']);
            $table->unique(['mini_course_id', 'student_id']); // One enrollment per student per course
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_course_enrollments');
    }
};
