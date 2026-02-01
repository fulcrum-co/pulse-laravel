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
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');

            // Classification
            $table->string('category', 50);  // survey, report, strategy, workflow_alert, course, system
            $table->string('type', 100);      // survey_assigned, workflow_triggered, etc.

            // Content
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('priority', 20)->default('normal');  // low, normal, high, urgent

            // Status & Lifecycle
            $table->string('status', 20)->default('unread');    // unread, read, snoozed, dismissed, resolved

            // Action
            $table->string('action_url', 500)->nullable();
            $table->string('action_label', 100)->nullable();

            // Polymorphic source reference
            $table->nullableMorphs('notifiable');

            // Flexible metadata
            $table->json('metadata')->nullable();

            // Lifecycle timestamps
            $table->timestamp('snoozed_until')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // Creator tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Primary query path: user's notifications by status
            $table->index(['user_id', 'status', 'created_at'], 'idx_user_status_created');

            // Filtered views by category
            $table->index(['user_id', 'category', 'status'], 'idx_user_category_status');

            // Snooze resurfacing job
            $table->index(['status', 'snoozed_until'], 'idx_snooze_check');

            // Expiration cleanup job
            $table->index(['status', 'expires_at'], 'idx_expire_check');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
