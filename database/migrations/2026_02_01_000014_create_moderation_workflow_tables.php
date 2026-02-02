<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('moderation_workflows')) {
            return;
        }

        // Moderation workflows - extends base workflows with moderation-specific config
        Schema::create('moderation_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('workflow_id')->constrained('workflows')->cascadeOnDelete();
            $table->string('content_type')->default('all'); // 'mini_course', 'content_block', 'all'
            $table->json('trigger_conditions')->nullable(); // score thresholds, flags
            $table->boolean('is_default')->default(false);
            $table->integer('priority')->default(0);
            $table->timestamps();

            $table->index(['org_id', 'content_type']);
            $table->index(['org_id', 'is_default']);
        });

        // Moderation queue items - work items for moderators
        Schema::create('moderation_queue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('moderation_result_id')->constrained('content_moderation_results')->cascadeOnDelete();
            $table->foreignId('workflow_id')->nullable()->constrained('moderation_workflows')->nullOnDelete();
            $table->string('current_step_id')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, completed, escalated, expired
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['org_id', 'status', 'priority']);
            $table->index(['assigned_to', 'status']);
            $table->index(['status', 'due_at']);
            $table->index('moderation_result_id');
        });

        // Moderation decisions - audit trail
        Schema::create('moderation_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_item_id')->constrained('moderation_queue_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('decision'); // approve, reject, request_changes, escalate, skip
            $table->text('notes')->nullable();
            $table->json('field_changes')->nullable(); // track edits made
            $table->integer('time_spent_seconds')->nullable();
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->timestamps();

            $table->index(['queue_item_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // Moderation SLA configs
        Schema::create('moderation_sla_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('priority'); // low, normal, high, urgent
            $table->integer('target_hours'); // 72, 48, 24, 4
            $table->integer('warning_hours'); // 48, 24, 12, 2
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['org_id', 'priority']);
        });

        // Moderation team settings
        Schema::create('moderation_team_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->json('content_specializations')->nullable(); // ['wellness', 'academic']
            $table->integer('max_concurrent_items')->default(10);
            $table->boolean('auto_assign_enabled')->default(true);
            $table->json('schedule')->nullable(); // availability hours
            $table->integer('current_load')->default(0);
            $table->timestamps();

            $table->unique(['org_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('moderation_team_settings');
        Schema::dropIfExists('moderation_sla_configs');
        Schema::dropIfExists('moderation_decisions');
        Schema::dropIfExists('moderation_queue_items');
        Schema::dropIfExists('moderation_workflows');
    }
};
