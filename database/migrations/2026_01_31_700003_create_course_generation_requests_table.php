<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('course_generation_requests')) {
            return;
        }

        Schema::create('course_generation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();

            // Request source
            $table->string('trigger_type'); // risk_threshold, workflow, manual
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('workflow_execution_id')->nullable()->constrained('workflow_executions')->nullOnDelete();

            // Target assignment
            $table->string('assignment_type'); // individual, group
            $table->json('target_student_ids')->nullable(); // Array of student IDs
            $table->unsignedBigInteger('target_group_id')->nullable(); // Reference to a student group/contact list

            // Context data used for generation
            $table->json('student_context');
            /*
             * Example:
             * {
             *   "risk_signals": {"attendance": 0.7, "behavior": 0.4, "academic": 0.8},
             *   "demographics": {"grade": "9", "iep": true},
             *   "recent_events": [...],
             *   "course_history": [...]
             * }
             */

            // Generation config
            $table->foreignId('template_id')->nullable()->constrained('course_templates')->nullOnDelete();
            $table->string('generation_strategy'); // template_fill, ai_full, hybrid
            $table->json('generation_params')->nullable();

            // Output
            $table->foreignId('generated_course_id')->nullable()->constrained('mini_courses')->nullOnDelete();
            $table->json('generation_log')->nullable(); // AI responses, content selections, etc.

            // Approval workflow
            $table->string('status')->default('pending'); // pending, generating, pending_approval, approved, rejected, failed
            $table->boolean('requires_approval')->default(true);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('org_id');
            $table->index('status');
            $table->index('trigger_type');
            $table->index('assignment_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_generation_requests');
    }
};
