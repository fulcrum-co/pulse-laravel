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
            $table->string('video_url')->nullable()->after('trigger_event');
            $table->integer('offset_x')->default(0)->after('video_url');
            $table->integer('offset_y')->default(0)->after('offset_x');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_help_hints', function (Blueprint $table) {
            $table->dropColumn(['video_url', 'offset_x', 'offset_y']);
        });
    }
};
