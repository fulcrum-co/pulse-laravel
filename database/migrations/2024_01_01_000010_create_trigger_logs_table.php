<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('trigger_logs')) {
            return;
        }

        Schema::create('trigger_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trigger_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learner_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('survey_attempt_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('triggered'); // triggered, acknowledged, resolved, dismissed
            $table->json('matched_conditions')->nullable();
            $table->json('actions_taken')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['trigger_id', 'status']);
            $table->index(['learner_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trigger_logs');
    }
};
