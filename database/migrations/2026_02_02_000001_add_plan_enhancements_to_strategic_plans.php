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
        Schema::table('strategic_plans', function (Blueprint $table) {
            // Plan category for new plan types (pip, idp, okr, action_plan)
            $table->string('category')->nullable()->after('plan_type');

            // Manager for PIPs (Performance Improvement Plans)
            $table->foreignId('manager_id')->nullable()->after('created_by')
                ->constrained('users')->nullOnDelete();

            // Trigger ID for action plans created from alerts
            $table->unsignedBigInteger('trigger_id')->nullable()->after('manager_id');

            // Flexible metadata storage
            $table->json('metadata')->nullable()->after('settings');

            // Index for filtering by category
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('strategic_plans', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['category', 'manager_id', 'trigger_id', 'metadata']);
        });
    }
};
