<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mini_course_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending_review');
            $table->string('workflow_mode'); // auto_activate, create_approve, approve_first
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('revision_feedback')->nullable();
            $table->integer('revision_count')->default(0);
            $table->timestamps();

            $table->foreign('submitted_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();

            $table->index('status');
            $table->index('workflow_mode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_approval_workflows');
    }
};
