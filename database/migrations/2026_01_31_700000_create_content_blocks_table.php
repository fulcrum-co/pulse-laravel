<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('content_blocks')) {
            return;
        }

        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->cascadeOnDelete();

            // Identification
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();

            // Content
            $table->string('block_type'); // video, document, activity, assessment, text, link, embed
            $table->json('content_data'); // Type-specific content

            // Source tracking
            $table->string('source_type')->default('internal'); // internal, youtube, vimeo, khan_academy, uploaded, custom_url
            $table->string('source_url', 500)->nullable();
            $table->json('source_metadata')->nullable(); // API response data, video duration, etc.

            // Classification
            $table->json('topics')->nullable(); // ['anxiety', 'stress', 'coping']
            $table->json('skills')->nullable(); // ['breathing', 'journaling', 'communication']
            $table->json('grade_levels')->nullable(); // ['6', '7', '8'] or ['elementary', 'middle', 'high']
            $table->json('subject_areas')->nullable(); // ['SEL', 'health', 'academics']

            // Targeting
            $table->json('target_risk_factors')->nullable(); // ['attendance', 'behavior', 'academic']
            $table->json('target_demographics')->nullable(); // Filters for when to use
            $table->boolean('iep_appropriate')->default(true);
            $table->string('language', 10)->default('en');

            // Usage tracking
            $table->unsignedInteger('usage_count')->default(0);
            $table->decimal('avg_completion_rate', 5, 2)->nullable();
            $table->decimal('avg_rating', 3, 2)->nullable();

            // Status
            $table->string('status')->default('draft'); // draft, active, archived
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('org_id');
            $table->index('block_type');
            $table->index('source_type');
            $table->index('status');
            $table->unique(['org_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
