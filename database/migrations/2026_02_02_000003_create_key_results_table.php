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
        Schema::create('key_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained('goals')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('metric_type'); // percentage, number, currency, boolean, milestone
            $table->decimal('target_value', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->default(0);
            $table->decimal('starting_value', 10, 2)->default(0);
            $table->string('unit')->nullable(); // %, $, hours, count, etc.
            $table->date('due_date')->nullable();
            $table->string('status')->default('not_started'); // not_started, in_progress, on_track, at_risk, completed
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('history')->nullable(); // Track value changes over time
            $table->timestamps();
            $table->softDeletes();

            $table->index(['goal_id', 'sort_order']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('key_results');
    }
};
