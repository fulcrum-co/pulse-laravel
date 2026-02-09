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
        Schema::create('strategy_drift_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('contact_note_id')->nullable()->constrained('contact_notes')->cascadeOnDelete();

            // Optional links to specific strategic elements
            $table->foreignId('strategic_plan_id')->nullable()->constrained('strategic_plans')->nullOnDelete();
            $table->foreignId('goal_id')->nullable()->constrained('goals')->nullOnDelete();
            $table->foreignId('key_result_id')->nullable()->constrained('key_results')->nullOnDelete();

            // Alignment metrics
            $table->decimal('alignment_score', 5, 4); // 0.0000 to 1.0000
            $table->string('alignment_level', 20); // strong, moderate, weak
            $table->json('matched_context')->nullable(); // Top matching plan elements with similarity scores
            $table->string('drift_direction', 20)->nullable(); // improving, stable, declining

            // AI-generated insight about the alignment
            $table->text('insight')->nullable();

            // Tracking
            $table->string('scored_by', 50)->default('system'); // user_id or 'system'
            $table->timestamp('scored_at');

            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['org_id', 'alignment_level', 'scored_at']);
            $table->index(['contact_note_id', 'scored_at']);
            $table->index(['org_id', 'scored_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategy_drift_scores');
    }
};
