<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mini_course_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mini_course_id')->constrained('mini_courses')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('step_type')->default('content'); // content, reflection, action, practice, human_connection, assessment, checkpoint
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->string('content_type')->default('text'); // text, video, document, link, embedded, interactive
            $table->json('content_data')->nullable(); // Type-specific content configuration
            $table->foreignId('resource_id')->nullable()->constrained('resources')->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->unsignedSmallInteger('estimated_duration_minutes')->nullable();
            $table->boolean('is_required')->default(true);
            $table->json('completion_criteria')->nullable(); // What constitutes completion
            $table->json('branching_logic')->nullable(); // For adaptive paths
            $table->text('feedback_prompt')->nullable(); // Prompt for student feedback
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['mini_course_id', 'sort_order']);
            $table->index(['step_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_course_steps');
    }
};
