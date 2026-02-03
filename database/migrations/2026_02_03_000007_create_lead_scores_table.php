<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('total_score')->default(0);
            $table->unsignedInteger('modules_completed')->default(0);
            $table->unsignedInteger('certifications_earned')->default(0);
            $table->unsignedInteger('courses_started')->default(0);
            $table->unsignedInteger('courses_completed')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('last_decay_at')->nullable(); // When score was last decayed
            $table->json('score_history')->nullable(); // Track score changes over time
            $table->json('crm_sync_data')->nullable(); // Data synced to external CRM
            $table->timestamp('crm_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['org_id', 'user_id']);
            $table->index(['org_id', 'total_score']);
            $table->index(['last_activity_at']);
        });

        // Lead score events table for tracking individual score changes
        Schema::create('lead_score_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_score_id')->constrained('lead_scores')->cascadeOnDelete();
            $table->string('event_type'); // module_completed, certification_earned, login, decay
            $table->integer('points'); // Can be negative for decay
            $table->string('description')->nullable();
            $table->nullableMorphs('scoreable'); // The entity that triggered the score change
            $table->timestamps();

            $table->index(['lead_score_id', 'event_type']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_score_events');
        Schema::dropIfExists('lead_scores');
    }
};
