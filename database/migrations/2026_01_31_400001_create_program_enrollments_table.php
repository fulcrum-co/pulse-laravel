<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('program_enrollments')) {
            return;
        }

        Schema::create('program_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learner_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('enrolled'); // enrolled, active, completed, withdrawn
            $table->text('notes')->nullable();
            $table->timestamp('enrolled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('progress_percent')->default(0);
            $table->json('feedback')->nullable();
            $table->timestamps();

            $table->index(['learner_id', 'status']);
            $table->index(['program_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_enrollments');
    }
};
