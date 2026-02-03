<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mini_courses', function (Blueprint $table) {
            // Link to generation request
            $table->foreignId('generation_request_id')
                ->nullable()
                ->after('approval_notes')
                ->constrained('course_generation_requests')
                ->nullOnDelete();

            // Link to template used
            $table->foreignId('template_id')
                ->nullable()
                ->after('generation_request_id')
                ->constrained('course_templates')
                ->nullOnDelete();

            // Assignment tracking for group/individual courses
            $table->json('assigned_learner_ids')->nullable()->after('template_id');
            $table->unsignedBigInteger('assigned_group_id')->nullable()->after('assigned_learner_ids');

            // Indexes
            $table->index('generation_request_id');
            $table->index('template_id');
            $table->index('assigned_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('mini_courses', function (Blueprint $table) {
            $table->dropForeign(['generation_request_id']);
            $table->dropForeign(['template_id']);
            $table->dropIndex(['generation_request_id']);
            $table->dropIndex(['template_id']);
            $table->dropIndex(['assigned_group_id']);

            $table->dropColumn([
                'generation_request_id',
                'template_id',
                'assigned_learner_ids',
                'assigned_group_id',
            ]);
        });
    }
};
