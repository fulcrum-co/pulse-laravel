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
        Schema::create('progress_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategic_plan_id')->constrained('strategic_plans')->cascadeOnDelete();
            $table->foreignId('goal_id')->nullable()->constrained('goals')->cascadeOnDelete();
            $table->foreignId('key_result_id')->nullable()->constrained('key_results')->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('milestones')->cascadeOnDelete();
            $table->text('content');
            $table->string('update_type')->default('manual'); // manual, ai_generated, system
            $table->decimal('value_change', 10, 2)->nullable(); // For tracking KR progress
            $table->string('status_change')->nullable(); // If status was updated
            $table->json('attachments')->nullable(); // URLs to attached files
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['strategic_plan_id', 'created_at']);
            $table->index(['goal_id']);
            $table->index(['created_by']);
            $table->index(['update_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_updates');
    }
};
