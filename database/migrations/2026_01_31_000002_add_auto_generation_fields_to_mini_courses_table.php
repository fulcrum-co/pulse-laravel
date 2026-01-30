<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mini_courses', function (Blueprint $table) {
            // Auto-generation tracking
            $table->string('generation_trigger')->nullable()->after('status');
            $table->string('target_entity_type')->nullable()->after('generation_trigger');
            $table->unsignedBigInteger('target_entity_id')->nullable()->after('target_entity_type');
            $table->json('generation_signals')->nullable()->after('target_entity_id');
            $table->timestamp('auto_generated_at')->nullable()->after('generation_signals');

            // Approval workflow
            $table->string('approval_status')->nullable()->after('auto_generated_at');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');

            // Index for querying by target entity
            $table->index(['target_entity_type', 'target_entity_id']);
            $table->index('generation_trigger');
            $table->index('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('mini_courses', function (Blueprint $table) {
            $table->dropIndex(['target_entity_type', 'target_entity_id']);
            $table->dropIndex(['generation_trigger']);
            $table->dropIndex(['approval_status']);

            $table->dropColumn([
                'generation_trigger',
                'target_entity_type',
                'target_entity_id',
                'generation_signals',
                'auto_generated_at',
                'approval_status',
                'approved_by',
                'approved_at',
                'approval_notes',
            ]);
        });
    }
};
