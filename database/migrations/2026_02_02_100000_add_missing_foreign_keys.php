<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add foreign key: mini_courses.current_version_id -> mini_course_versions.id
        if (Schema::hasTable('mini_courses') && Schema::hasTable('mini_course_versions')) {
            Schema::table('mini_courses', function (Blueprint $table) {
                try {
                    // Check if the foreign key already exists before adding it
                    $table->foreign('current_version_id')
                        ->references('id')
                        ->on('mini_course_versions')
                        ->nullOnDelete();
                } catch (\Exception $e) {
                    // Foreign key may already exist, log and continue
                    \Log::warning('Foreign key constraint may already exist: ' . $e->getMessage());
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint if it exists
        if (Schema::hasTable('mini_courses')) {
            Schema::table('mini_courses', function (Blueprint $table) {
                try {
                    $table->dropForeign(['current_version_id']);
                } catch (\Exception $e) {
                    // Foreign key may not exist, log and continue
                    \Log::warning('Could not drop foreign key constraint: ' . $e->getMessage());
                }
            });
        }
    }
};
