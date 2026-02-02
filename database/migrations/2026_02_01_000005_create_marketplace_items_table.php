<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('marketplace_items')) {
            return;
        }

        Schema::create('marketplace_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Polymorphic relationship to actual content
            $table->string('listable_type'); // Survey, MiniCourse, Resource, Provider
            $table->unsignedBigInteger('listable_id');

            // Ownership
            $table->foreignId('seller_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->nullOnDelete();

            // Basic info
            $table->string('title');
            $table->text('description');
            $table->string('short_description', 500)->nullable();

            // Categorization
            $table->string('category'); // survey, strategy, content, provider
            $table->json('subcategories')->nullable();
            $table->json('tags')->nullable();

            // Media
            $table->string('thumbnail_url')->nullable();
            $table->json('preview_images')->nullable();
            $table->json('preview_content')->nullable(); // Sample questions, steps, etc.

            // Targeting
            $table->json('target_grades')->nullable();
            $table->json('target_subjects')->nullable();
            $table->json('target_needs')->nullable();

            // Pricing type (detailed pricing in marketplace_pricing table)
            $table->string('pricing_type')->default('free'); // free, one_time, recurring

            // Status
            $table->string('status')->default('draft'); // draft, pending_review, approved, rejected, suspended

            // Flags
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_verified')->default(false);

            // Stats
            $table->decimal('ratings_average', 3, 2)->nullable();
            $table->integer('ratings_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->integer('purchase_count')->default(0);
            $table->integer('view_count')->default(0);

            // Review workflow
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['listable_type', 'listable_id']);
            $table->index('category');
            $table->index('status');
            $table->index('pricing_type');
            $table->index('is_featured');
            $table->index(['seller_profile_id', 'status']);
            $table->index(['category', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_items');
    }
};
