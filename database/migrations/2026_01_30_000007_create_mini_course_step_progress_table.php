<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mini_course_step_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('mini_course_enrollments')->cascadeOnDelete();
            $table->foreignId('step_id')->constrained('mini_course_steps')->cascadeOnDelete();
            $table->string('status')->default('not_started'); // not_started, in_progress, completed, skipped
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('time_spent_seconds')->default(0);
            $table->json('response_data')->nullable(); // For reflections, assessments
            $table->text('feedback_response')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['enrollment_id', 'status']);
            $table->unique(['enrollment_id', 'step_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_course_step_progress');
    }
};
