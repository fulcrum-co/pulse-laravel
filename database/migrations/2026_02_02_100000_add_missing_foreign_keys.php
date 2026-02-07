<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check if a foreign key constraint exists.
     */
    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        // Only check on PostgreSQL (information_schema not available on SQLite)
        if (DB::getDriverName() !== 'pgsql') {
            return false;
        }

        $result = DB::select("
            SELECT 1 FROM information_schema.table_constraints
            WHERE constraint_name = ?
            AND table_name = ?
            AND constraint_type = 'FOREIGN KEY'
        ", [$constraintName, $table]);

        return count($result) > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add foreign key: mini_courses.current_version_id -> mini_course_versions.id
        if (Schema::hasTable('mini_courses') && Schema::hasTable('mini_course_versions')) {
            if (! $this->foreignKeyExists('mini_courses', 'mini_courses_current_version_id_foreign')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->foreign('current_version_id')
                        ->references('id')
                        ->on('mini_course_versions')
                        ->nullOnDelete();
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
            if ($this->foreignKeyExists('mini_courses', 'mini_courses_current_version_id_foreign')) {
                Schema::table('mini_courses', function (Blueprint $table) {
                    $table->dropForeign(['current_version_id']);
                });
            }
        }
    }
};
