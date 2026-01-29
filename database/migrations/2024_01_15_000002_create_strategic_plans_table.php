<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strategic_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('source_plan_id')->nullable()->constrained('strategic_plans')->nullOnDelete();
            $table->foreignId('source_org_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('plan_type')->default('organizational'); // organizational, teacher, student, department, grade
            $table->string('target_type')->nullable(); // For improvement plans: App\Models\User, App\Models\Student, App\Models\Department
            $table->unsignedBigInteger('target_id')->nullable(); // ID of the target entity
            $table->string('status')->default('draft'); // draft, active, completed, archived
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('consultant_visible')->default(true);
            $table->json('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['org_id', 'plan_type']);
            $table->index(['org_id', 'status']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategic_plans');
    }
};
