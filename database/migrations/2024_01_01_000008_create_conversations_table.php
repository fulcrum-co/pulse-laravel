<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('conversations')) {
            return;
        }

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_attempt_id')->nullable()->constrained()->nullOnDelete();
            $table->string('conversation_type')->default('check_in'); // check_in, survey, follow_up, support
            $table->string('status')->default('active'); // active, completed, flagged
            $table->json('messages')->nullable();
            $table->json('ai_summary')->nullable();
            $table->json('detected_patterns')->nullable();
            $table->string('sentiment')->nullable(); // positive, neutral, negative, mixed
            $table->decimal('sentiment_score', 5, 2)->nullable();
            $table->boolean('requires_follow_up')->default(false);
            $table->boolean('flagged_for_review')->default(false);
            $table->text('flag_reason')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['flagged_for_review', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
