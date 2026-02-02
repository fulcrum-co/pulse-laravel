<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('strategy_assignments')) {
            return;
        }

        Schema::create('strategy_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strategic_plan_id')->constrained('strategic_plans')->cascadeOnDelete();
            $table->string('assignable_type'); // App\Models\User, App\Models\Department, App\Models\Classroom, App\Models\Student
            $table->unsignedBigInteger('assignable_id');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['assignable_type', 'assignable_id'], 'strategy_assignment_assignable_index');
            $table->unique(['strategic_plan_id', 'assignable_type', 'assignable_id'], 'strategy_assignment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategy_assignments');
    }
};
