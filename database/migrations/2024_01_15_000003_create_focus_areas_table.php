<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('focus_areas')) {
            return;
        }

        Schema::create('focus_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategic_plan_id')->constrained('strategic_plans')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('not_started'); // on_track, at_risk, off_track, not_started
            $table->timestamps();
            $table->softDeletes();

            $table->index(['strategic_plan_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('focus_areas');
    }
};
