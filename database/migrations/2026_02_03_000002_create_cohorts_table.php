<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cohorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('mini_course_id')->constrained('mini_courses')->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->string('name'); // e.g., "Spring 2026 Cohort A"
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('visibility_status')->default('private'); // public, gated, private
            $table->string('status')->default('draft'); // draft, enrollment_open, active, completed, archived
            $table->unsignedInteger('max_capacity')->nullable(); // null = unlimited
            $table->boolean('allow_self_enrollment')->default(false);
            $table->boolean('drip_content')->default(false); // Release content based on schedule
            $table->json('drip_schedule')->nullable(); // Step release schedule
            $table->json('live_sessions')->nullable(); // Scheduled Zoom/Teams sessions
            $table->string('community_type')->nullable(); // discussion_board, slack, discord, none
            $table->string('community_url')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['org_id', 'status']);
            $table->index(['mini_course_id', 'status']);
            $table->index(['semester_id']);
            $table->index(['visibility_status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cohorts');
    }
};
