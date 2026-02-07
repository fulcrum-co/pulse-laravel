<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_help_hints')) {
            return;
        }

        Schema::create('page_help_hints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('page_context', 50)->index(); // dashboard, reports, collect, etc.
            $table->string('section', 50); // unique identifier within page
            $table->string('selector'); // CSS selector like [data-help="search-reports"]
            $table->string('title');
            $table->text('description');
            $table->enum('position', ['top', 'bottom', 'left', 'right'])->default('bottom');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint: one hint per section per page per org
            $table->unique(['org_id', 'page_context', 'section'], 'page_help_hints_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_help_hints');
    }
};
