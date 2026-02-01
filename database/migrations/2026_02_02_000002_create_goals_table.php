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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategic_plan_id')->constrained('strategic_plans')->cascadeOnDelete();
            $table->foreignId('parent_goal_id')->nullable()->constrained('goals')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('goal_type', ['objective', 'key_result', 'outcome'])->default('objective');
            $table->decimal('target_value', 10, 2)->nullable();
            $table->decimal('current_value', 10, 2)->nullable();
            $table->string('unit')->nullable(); // percentage, count, currency, etc.
            $table->date('due_date')->nullable();
            $table->string('status')->default('not_started'); // not_started, in_progress, at_risk, completed
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['strategic_plan_id', 'sort_order']);
            $table->index(['parent_goal_id']);
            $table->index(['owner_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
