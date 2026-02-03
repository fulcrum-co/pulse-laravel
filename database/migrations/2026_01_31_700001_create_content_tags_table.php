<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('content_tags')) {
            return;
        }

        Schema::create('content_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('slug', 100);
            $table->string('category'); // topic, skill, level, subject, risk_factor
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable(); // Hex color for UI
            $table->timestamps();

            $table->unique(['org_id', 'slug']);
            $table->index('category');
        });

        // Pivot table for content_blocks <-> content_tags
        Schema::create('content_block_tag', function (Blueprint $table) {
            $table->foreignId('content_block_id')->constrained('content_blocks')->cascadeOnDelete();
            $table->foreignId('content_tag_id')->constrained('content_tags')->cascadeOnDelete();
            $table->primary(['content_block_id', 'content_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_block_tag');
        Schema::dropIfExists('content_tags');
    }
};
