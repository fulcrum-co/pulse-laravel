<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('help_categories')) {
            return;
        }

        Schema::create('help_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('help_categories')->nullOnDelete();
            $table->string('slug')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon', 50)->default('book-open'); // Heroicon name
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['org_id', 'slug'], 'help_categories_org_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('help_categories');
    }
};
