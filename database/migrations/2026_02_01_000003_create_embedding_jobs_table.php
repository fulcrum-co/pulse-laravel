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
        Schema::create('embedding_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->nullOnDelete();

            // Polymorphic relationship to the embeddable model
            $table->string('embeddable_type');
            $table->unsignedBigInteger('embeddable_id');

            // Job status tracking
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->text('error_message')->nullable();
            $table->unsignedInteger('retry_count')->default(0);

            // Embedding metadata
            $table->string('embedding_model')->nullable();
            $table->unsignedInteger('token_count')->nullable();
            $table->decimal('processing_time_ms', 10, 2)->nullable();

            // Timestamps
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['embeddable_type', 'embeddable_id']);
            $table->index('status');
            $table->index(['status', 'created_at']); // For finding pending jobs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embedding_jobs');
    }
};
