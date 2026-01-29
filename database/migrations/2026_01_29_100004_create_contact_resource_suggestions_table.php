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
        if (Schema::hasTable('contact_resource_suggestions')) {
            return;
        }

        Schema::create('contact_resource_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();

            // Contact
            $table->string('contact_type');
            $table->unsignedBigInteger('contact_id');

            // Resource
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete();

            // Suggestion metadata
            $table->string('suggestion_source'); // manual, ai_recommendation, rule_based, peer_success
            $table->decimal('relevance_score', 5, 2)->nullable(); // AI confidence 0-100
            $table->json('matching_criteria')->nullable(); // Why this was suggested
            $table->text('ai_rationale')->nullable();

            // Status
            $table->string('status')->default('pending'); // pending, accepted, declined, assigned
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            // If assigned
            $table->foreignId('assignment_id')->nullable()->constrained('resource_assignments')->nullOnDelete();

            $table->timestamps();

            $table->unique(['contact_type', 'contact_id', 'resource_id']);
            $table->index(['org_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_resource_suggestions');
    }
};
