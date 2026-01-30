<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('template_type'); // wellness_check, academic_stress, sel_screener, custom
            $table->json('questions'); // full question definitions
            $table->json('interpretation_config')->nullable(); // overall scoring/interpretation
            $table->json('delivery_defaults')->nullable(); // default channels, timing
            $table->json('tags')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('usage_count')->default(0);
            $table->integer('estimated_duration_minutes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['org_id', 'template_type']);
            $table->index(['is_public', 'is_featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_templates');
    }
};
