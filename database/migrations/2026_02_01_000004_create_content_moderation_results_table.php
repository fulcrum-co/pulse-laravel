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
        Schema::create('content_moderation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->onDelete('cascade');

            // Polymorphic relationship to the content being moderated
            $table->morphs('moderatable');

            // Overall moderation status
            $table->string('status'); // pending, passed, flagged, rejected, approved_override
            $table->decimal('overall_score', 5, 4); // 0.0000 to 1.0000

            // Individual dimension scores (0-1, higher is better/safer)
            $table->decimal('age_appropriateness_score', 5, 4)->nullable();
            $table->decimal('clinical_safety_score', 5, 4)->nullable();
            $table->decimal('cultural_sensitivity_score', 5, 4)->nullable();
            $table->decimal('accuracy_score', 5, 4)->nullable();

            // Detailed flags and recommendations
            $table->json('flags')->nullable(); // Array of specific concerns found
            $table->json('recommendations')->nullable(); // Suggested improvements
            $table->json('dimension_details')->nullable(); // Per-dimension analysis details

            // Review tracking
            $table->boolean('human_reviewed')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            // Moderation metadata
            $table->string('model_version')->nullable(); // AI model used
            $table->integer('processing_time_ms')->nullable();
            $table->integer('token_count')->nullable();

            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['moderatable_type', 'moderatable_id', 'status']);
            $table->index(['org_id', 'status']);
            $table->index(['status', 'human_reviewed']);
            $table->index('created_at');
        });

        // Add moderation tracking columns to mini_courses
        Schema::table('mini_courses', function (Blueprint $table) {
            $table->string('moderation_status')->nullable()->after('approval_notes');
            $table->foreignId('latest_moderation_id')->nullable()->after('moderation_status');
        });

        // Add moderation tracking columns to content_blocks
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->string('moderation_status')->nullable()->after('reviewed_by');
            $table->foreignId('latest_moderation_id')->nullable()->after('moderation_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->dropColumn(['moderation_status', 'latest_moderation_id']);
        });

        Schema::table('mini_courses', function (Blueprint $table) {
            $table->dropColumn(['moderation_status', 'latest_moderation_id']);
        });

        Schema::dropIfExists('content_moderation_results');
    }
};
