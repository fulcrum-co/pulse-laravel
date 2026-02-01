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
        Schema::create('progress_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategic_plan_id')->constrained('strategic_plans')->cascadeOnDelete();
            $table->string('period_type'); // weekly, monthly, quarterly
            $table->date('period_start');
            $table->date('period_end');
            $table->text('summary');
            $table->json('highlights')->nullable(); // Key achievements
            $table->json('concerns')->nullable(); // Areas needing attention
            $table->json('recommendations')->nullable(); // AI suggestions
            $table->json('metrics_snapshot')->nullable(); // Captured metrics at generation time
            $table->timestamps();

            $table->unique(['strategic_plan_id', 'period_type', 'period_start']);
            $table->index(['strategic_plan_id', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_summaries');
    }
};
