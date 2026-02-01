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
        Schema::table('content_moderation_results', function (Blueprint $table) {
            // Assignment tracking
            $table->foreignId('assigned_to')->nullable()->after('reviewed_at')
                ->constrained('users')->onDelete('set null');
            $table->foreignId('assigned_by')->nullable()->after('assigned_to')
                ->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable()->after('assigned_by');

            // Collaborators (JSON array of user IDs)
            $table->json('collaborator_ids')->nullable()->after('assigned_at');

            // Assignment priority for queue ordering
            $table->string('assignment_priority', 20)->default('normal')->after('collaborator_ids');

            // Due date for assignment
            $table->timestamp('due_at')->nullable()->after('assignment_priority');

            // Assignment notes
            $table->text('assignment_notes')->nullable()->after('due_at');

            // Indexes for efficient querying
            $table->index('assigned_to');
            $table->index(['org_id', 'assigned_to', 'status'], 'cmr_org_assigned_status_idx');
            $table->index(['assigned_to', 'status', 'human_reviewed'], 'cmr_assigned_status_reviewed_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_moderation_results', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('cmr_org_assigned_status_idx');
            $table->dropIndex('cmr_assigned_status_reviewed_idx');
            $table->dropIndex(['assigned_to']);

            // Drop foreign key constraints
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['assigned_by']);

            // Drop columns
            $table->dropColumn([
                'assigned_to',
                'assigned_by',
                'assigned_at',
                'collaborator_ids',
                'assignment_priority',
                'due_at',
                'assignment_notes',
            ]);
        });
    }
};
