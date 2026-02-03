<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('learners')) {
            return;
        }

        Schema::create('learners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('learner_number')->nullable();
            $table->string('grade_level')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('ethnicity')->nullable();
            $table->boolean('iep_status')->default(false);
            $table->boolean('ell_status')->default(false);
            $table->boolean('free_reduced_lunch')->default(false);
            $table->string('enrollment_status')->default('active');
            $table->date('enrollment_date')->nullable();
            $table->string('risk_level')->default('good'); // good, low, high
            $table->decimal('risk_score', 5, 2)->nullable();
            $table->json('tags')->nullable();
            $table->json('custom_fields')->nullable();
            $table->foreignId('counselor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('homeroom_classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['org_id', 'risk_level']);
            $table->index(['org_id', 'grade_level']);
        });

        // Pivot table for learners and classrooms
        Schema::create('classroom_learner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learner_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['classroom_id', 'learner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_learner');
        Schema::dropIfExists('learners');
    }
};
