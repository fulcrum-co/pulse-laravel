<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('resources')) {
            return;
        }

        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('resource_type')->default('article'); // article, video, worksheet, activity, link, document
            $table->string('category')->nullable(); // anxiety, depression, stress, social, academic, etc.
            $table->json('tags')->nullable();
            $table->string('url')->nullable();
            $table->string('file_path')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->integer('estimated_duration_minutes')->nullable();
            $table->json('target_levels')->nullable();
            $table->json('target_risk_levels')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['org_id', 'category']);
            $table->index(['org_id', 'resource_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
