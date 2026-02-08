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
            // Only add video_url if it doesn't exist (offset_x and offset_y already exist)
            if (!Schema::hasColumn('page_help_hints', 'video_url')) {
                $table->string('video_url')->nullable()->after('trigger_event');
            }
            // offset_x and offset_y already exist, so we don't add them
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_help_hints', function (Blueprint $table) {
            // Only drop video_url since offset_x and offset_y existed before this migration
            if (Schema::hasColumn('page_help_hints', 'video_url')) {
                $table->dropColumn('video_url');
            }
        });
    }
};
