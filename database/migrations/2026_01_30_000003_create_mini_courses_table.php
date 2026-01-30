<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mini_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('source_course_id')->nullable()->constrained('mini_courses')->nullOnDelete();
            $table->foreignId('source_org_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->unsignedBigInteger('current_version_id')->nullable(); // FK added after versions table
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('objectives')->nullable(); // Array of learning objectives
            $table->text('rationale')->nullable(); // Why this course exists
            $table->text('expected_experience')->nullable(); // What the learner will experience
            $table->string('course_type')->default('intervention'); // intervention, enrichment, skill_building, wellness, academic, behavioral
            $table->string('creation_source')->default('human_created'); // ai_generated, human_created, hybrid, template
            $table->json('ai_generation_context')->nullable(); // Signals that triggered AI generation
            $table->json('target_grades')->nullable();
            $table->json('target_risk_levels')->nullable();
            $table->json('target_needs')->nullable();
            $table->unsignedSmallInteger('estimated_duration_minutes')->nullable();
            $table->string('difficulty_level')->nullable(); // beginner, intermediate, advanced
            $table->string('status')->default('draft'); // draft, active, archived
            $table->boolean('is_public')->default(false);
            $table->boolean('is_template')->default(false);
            $table->json('analytics_config')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['org_id', 'status']);
            $table->index(['org_id', 'course_type']);
            $table->index(['is_template']);
            $table->index(['creation_source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mini_courses');
    }
};
