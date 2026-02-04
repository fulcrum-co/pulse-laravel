<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mini_courses', function (Blueprint $table) {
            // Add workflow context for courses generated via workflow actions
            $table->jsonb('workflow_context')->nullable()->after('generation_signals');

            // Add index for finding workflow-generated courses
            $table->index(['org_id', 'generation_trigger', 'approval_status']);
        });
    }

    public function down(): void
    {
        Schema::table('mini_courses', function (Blueprint $table) {
            $table->dropIndex(['org_id', 'generation_trigger', 'approval_status']);
            $table->dropColumn('workflow_context');
        });
    }
};
