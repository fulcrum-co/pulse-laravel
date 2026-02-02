<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('strategy_survey_assignments')) {
            return;
        }

        Schema::create('strategy_survey_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained('surveys')->cascadeOnDelete();
            $table->string('assignable_type'); // App\Models\FocusArea, App\Models\Objective, App\Models\Activity
            $table->unsignedBigInteger('assignable_id');
            $table->timestamps();

            $table->index(['assignable_type', 'assignable_id'], 'strategy_survey_assignable_index');
            $table->unique(['survey_id', 'assignable_type', 'assignable_id'], 'strategy_survey_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategy_survey_assignments');
    }
};
