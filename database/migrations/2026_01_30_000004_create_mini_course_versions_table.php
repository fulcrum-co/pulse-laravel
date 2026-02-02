<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('mini_course_versions')) {
            return;
        }

        Schema::create('mini_course_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mini_course_id')->constrained('mini_courses')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('objectives')->nullable();
            $table->text('rationale')->nullable();
            $table->text('expected_experience')->nullable();
            $table->json('steps_snapshot')->nullable(); // Full snapshot of steps at this version
            $table->text('change_summary')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Indexes
            $table->index(['mini_course_id', 'version_number']);
            $table->unique(['mini_course_id', 'version_number']);
        });

        // Add foreign key from mini_courses to current_version_id
        Schema::table('mini_courses', function (Blueprint $table) {
            $table->foreign('current_version_id')
                ->references('id')
                ->on('mini_course_versions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mini_courses', function (Blueprint $table) {
            $table->dropForeign(['current_version_id']);
        });

        Schema::dropIfExists('mini_course_versions');
    }
};
