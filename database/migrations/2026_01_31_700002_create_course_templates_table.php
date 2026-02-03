<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('course_templates')) {
            return;
        }

        Schema::create('course_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->cascadeOnDelete(); // NULL for system templates

            // Identification
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('course_type'); // wellness, academic, social_emotional, intervention, etc.

            // Template structure
            $table->json('template_data');
            /*
             * Example structure:
             * {
             *   "objectives_template": ["Understand {topic}", "Practice {skill}", "Apply techniques"],
             *   "steps": [
             *     {
             *       "order": 1,
             *       "title_template": "Introduction to {topic}",
             *       "step_type": "content",
             *       "content_type": "video",
             *       "content_block_query": {
             *         "block_type": "video",
             *         "topics": ["{primary_topic}"],
             *         "max_duration": 300
             *       },
             *       "fallback_ai_prompt": "Create an engaging introduction to {topic} for {grade_level} learners"
             *     }
             *   ],
             *   "variables": {
             *     "topic": { "type": "string", "required": true },
             *     "grade_level": { "type": "string", "required": true },
             *     "primary_topic": { "type": "string", "required": false }
             *   }
             * }
             */

            // Targeting
            $table->json('target_risk_factors')->nullable();
            $table->json('target_grade_levels')->nullable();
            $table->unsignedSmallInteger('estimated_duration_minutes')->nullable();

            // Status
            $table->boolean('is_system')->default(false); // Built-in vs org-created
            $table->string('status')->default('draft'); // draft, active, archived

            // Metadata
            $table->unsignedInteger('usage_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('org_id');
            $table->index('course_type');
            $table->index('status');
            $table->index('is_system');
            $table->unique(['org_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_templates');
    }
};
