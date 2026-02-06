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
            // Make selector nullable so intro cards (center position) don't need a selector
            $table->string('selector')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_help_hints', function (Blueprint $table) {
            // Revert selector back to NOT NULL
            $table->string('selector')->nullable(false)->change();
        });
    }
};
