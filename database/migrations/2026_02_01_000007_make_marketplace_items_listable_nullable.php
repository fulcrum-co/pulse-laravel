<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_items', function (Blueprint $table) {
            // Make listable columns nullable for standalone marketplace items
            $table->string('listable_type')->nullable()->change();
            $table->unsignedBigInteger('listable_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_items', function (Blueprint $table) {
            $table->string('listable_type')->nullable(false)->change();
            $table->unsignedBigInteger('listable_id')->nullable(false)->change();
        });
    }
};
