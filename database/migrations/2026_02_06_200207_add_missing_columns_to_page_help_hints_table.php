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
        Schema::table('page_help_hints', function (Blueprint $table) {
            // Add offset columns for tooltip positioning
            if (! Schema::hasColumn('page_help_hints', 'offset_x')) {
                $table->integer('offset_x')->default(0)->after('position');
            }
            if (! Schema::hasColumn('page_help_hints', 'offset_y')) {
                $table->integer('offset_y')->default(0)->after('offset_x');
            }

            // Add trigger event for context-aware tooltips/modals
            if (! Schema::hasColumn('page_help_hints', 'trigger_event')) {
                $table->string('trigger_event')->nullable()->after('selector')
                      ->comment('DOM event or action that triggers this hint (e.g., hover, click, after-click)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_help_hints', function (Blueprint $table) {
            $columnsToRemove = [];

            if (Schema::hasColumn('page_help_hints', 'offset_x')) {
                $columnsToRemove[] = 'offset_x';
            }
            if (Schema::hasColumn('page_help_hints', 'offset_y')) {
                $columnsToRemove[] = 'offset_y';
            }
            if (Schema::hasColumn('page_help_hints', 'trigger_event')) {
                $columnsToRemove[] = 'trigger_event';
            }

            if (! empty($columnsToRemove)) {
                $table->dropColumn($columnsToRemove);
            }
        });
    }
};
