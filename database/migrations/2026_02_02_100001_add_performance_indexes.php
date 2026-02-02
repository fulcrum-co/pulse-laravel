<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check if an index exists on a table.
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("
            SELECT 1 FROM pg_indexes
            WHERE tablename = ?
            AND indexname = ?
        ", [$table, $indexName]);

        return count($result) > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add indexes to mini_courses table
        if (Schema::hasTable('mini_courses')) {
            if (Schema::hasColumn('mini_courses', 'published_at') && ! $this->indexExists('mini_courses', 'mini_courses_published_at_index')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->index('published_at');
                });
            }

            if (! $this->indexExists('mini_courses', 'mini_courses_created_at_index')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->index('created_at');
                });
            }

            if (Schema::hasColumn('mini_courses', 'difficulty_level') && ! $this->indexExists('mini_courses', 'mini_courses_difficulty_level_index')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->index('difficulty_level');
                });
            }

            if (Schema::hasColumn('mini_courses', 'course_type') && ! $this->indexExists('mini_courses', 'mini_courses_course_type_index')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->index('course_type');
                });
            }

            if (! $this->indexExists('mini_courses', 'mini_courses_org_id_created_at_index')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->index(['org_id', 'created_at']);
                });
            }

            if (! $this->indexExists('mini_courses', 'mini_courses_org_id_status_created_at_index')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->index(['org_id', 'status', 'created_at']);
                });
            }
        }

        // Add indexes to content_moderation_results table
        if (Schema::hasTable('content_moderation_results')) {
            if (! $this->indexExists('content_moderation_results', 'content_moderation_results_created_at_index')) {
                Schema::table('content_moderation_results', function (Blueprint $table) {
                    $table->index('created_at');
                });
            }

            if (! $this->indexExists('content_moderation_results', 'content_moderation_results_moderatable_type_moderatable_id_index')) {
                Schema::table('content_moderation_results', function (Blueprint $table) {
                    $table->index(['moderatable_type', 'moderatable_id']);
                });
            }

            if (! $this->indexExists('content_moderation_results', 'content_moderation_results_status_index')) {
                Schema::table('content_moderation_results', function (Blueprint $table) {
                    $table->index('status');
                });
            }
        }

        // Add indexes to resources table
        if (Schema::hasTable('resources')) {
            if (! $this->indexExists('resources', 'resources_created_at_index')) {
                Schema::table('resources', function (Blueprint $table) {
                    $table->index('created_at');
                });
            }

            if (! $this->indexExists('resources', 'resources_org_id_created_at_index')) {
                Schema::table('resources', function (Blueprint $table) {
                    $table->index(['org_id', 'created_at']);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mini_courses')) {
            if ($this->indexExists('mini_courses', 'mini_courses_published_at_index')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->dropIndex('mini_courses_published_at_index');
                });
            }
            if ($this->indexExists('mini_courses', 'mini_courses_created_at_index')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->dropIndex('mini_courses_created_at_index');
                });
            }
            if ($this->indexExists('mini_courses', 'mini_courses_difficulty_level_index')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->dropIndex('mini_courses_difficulty_level_index');
                });
            }
            if ($this->indexExists('mini_courses', 'mini_courses_course_type_index')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->dropIndex('mini_courses_course_type_index');
                });
            }
            if ($this->indexExists('mini_courses', 'mini_courses_org_id_created_at_index')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->dropIndex('mini_courses_org_id_created_at_index');
                });
            }
            if ($this->indexExists('mini_courses', 'mini_courses_org_id_status_created_at_index')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->dropIndex('mini_courses_org_id_status_created_at_index');
                });
            }
        }

        if (Schema::hasTable('content_moderation_results')) {
            if ($this->indexExists('content_moderation_results', 'content_moderation_results_created_at_index')) {
                Schema::table('content_moderation_results', function (Blueprint $table) {
                    $table->dropIndex('content_moderation_results_created_at_index');
                });
            }
            if ($this->indexExists('content_moderation_results', 'content_moderation_results_moderatable_type_moderatable_id_index')) {
                Schema::table('content_moderation_results', function (Blueprint $table) {
                    $table->dropIndex('content_moderation_results_moderatable_type_moderatable_id_index');
                });
            }
            if ($this->indexExists('content_moderation_results', 'content_moderation_results_status_index')) {
                Schema::table('content_moderation_results', function (Blueprint $table) {
                    $table->dropIndex('content_moderation_results_status_index');
                });
            }
        }

        if (Schema::hasTable('resources')) {
            if ($this->indexExists('resources', 'resources_created_at_index')) {
                Schema::table('resources', function (Blueprint $table) {
                    $table->dropIndex('resources_created_at_index');
                });
            }
            if ($this->indexExists('resources', 'resources_org_id_created_at_index')) {
                Schema::table('resources', function (Blueprint $table) {
                    $table->dropIndex('resources_org_id_created_at_index');
                });
            }
        }
    }
};
