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
        // Add indexes to mini_courses table
        Schema::table('mini_courses', function (Blueprint $table) {
            // Index on published_at for sorting/filtering by publication date
            if (!Schema::hasColumn('mini_courses', 'published_at')) {
                return;
            }

            try {
                $table->index('published_at');
            } catch (\Exception $e) {
                // Index might already exist, continue
            }

            // Index on created_at for sorting/filtering by creation date
            try {
                $table->index('created_at');
            } catch (\Exception $e) {
                // Index might already exist, continue
            }

            // Index on difficulty_level for filtering courses by difficulty
            try {
                $table->index('difficulty_level');
            } catch (\Exception $e) {
                // Index might already exist, continue
            }

            // Index on course_type for filtering courses by type
            try {
                $table->index('course_type');
            } catch (\Exception $e) {
                // Index might already exist, continue
            }

            // Composite index on (org_id, created_at) for org-based filtering with date sorting
            try {
                $table->index(['org_id', 'created_at']);
            } catch (\Exception $e) {
                // Index might already exist, continue
            }

            // Composite index on (org_id, status, created_at) for complex org queries
            try {
                $table->index(['org_id', 'status', 'created_at']);
            } catch (\Exception $e) {
                // Index might already exist, continue
            }
        });

        // Add indexes to content_moderation_results table
        Schema::table('content_moderation_results', function (Blueprint $table) {
            // Index on created_at for sorting/filtering by creation date
            // Note: this index is already defined in the original migration, but adding it here ensures consistency
            try {
                $table->index('created_at');
            } catch (\Exception $e) {
                // Index might already exist, continue
            }

            // Composite index on (moderatable_type, moderatable_id) for polymorphic relationship queries
            // Note: Original migration uses (moderatable_type, moderatable_id, status), but this adds the base composite
            try {
                $table->index(['moderatable_type', 'moderatable_id']);
            } catch (\Exception $e) {
                // Index might already exist, continue
            }

            // Index on status for filtering by moderation status
            try {
                $table->index('status');
            } catch (\Exception $e) {
                // Index might already exist, continue
            }
        });

        // Add indexes to resources table
        Schema::table('resources', function (Blueprint $table) {
            // Index on created_at for sorting/filtering by creation date
            try {
                $table->index('created_at');
            } catch (\Exception $e) {
                // Index might already exist, continue
            }

            // Composite index on (org_id, created_at) for org-based filtering with date sorting
            try {
                $table->index(['org_id', 'created_at']);
            } catch (\Exception $e) {
                // Index might already exist, continue
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from mini_courses table
        Schema::table('mini_courses', function (Blueprint $table) {
            try {
                $table->dropIndex('mini_courses_published_at_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }

            try {
                $table->dropIndex('mini_courses_created_at_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }

            try {
                $table->dropIndex('mini_courses_difficulty_level_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }

            try {
                $table->dropIndex('mini_courses_course_type_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }

            try {
                $table->dropIndex('mini_courses_org_id_created_at_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }

            try {
                $table->dropIndex('mini_courses_org_id_status_created_at_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
        });

        // Drop indexes from content_moderation_results table
        Schema::table('content_moderation_results', function (Blueprint $table) {
            try {
                $table->dropIndex('content_moderation_results_created_at_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }

            try {
                $table->dropIndex('content_moderation_results_moderatable_type_moderatable_id_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }

            try {
                $table->dropIndex('content_moderation_results_status_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
        });

        // Drop indexes from resources table
        Schema::table('resources', function (Blueprint $table) {
            try {
                $table->dropIndex('resources_created_at_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }

            try {
                $table->dropIndex('resources_org_id_created_at_index');
            } catch (\Exception $e) {
                // Index doesn't exist, continue
            }
        });
    }
};
