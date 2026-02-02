<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('question_banks')) {
            return;
        }

        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('category'); // wellness, academic, behavioral, sel, custom
            $table->string('subcategory')->nullable();
            $table->text('question_text');
            $table->string('question_type'); // scale, multiple_choice, text, voice, matrix
            $table->json('options')->nullable(); // for multiple_choice, scale labels
            $table->json('interpretation_rules')->nullable(); // AI interpretation guidance
            $table->json('scoring_weights')->nullable();
            $table->string('audio_file_path')->nullable(); // pre-recorded question audio
            $table->string('audio_disk')->default('local');
            $table->boolean('is_public')->default(false); // available to all orgs
            $table->boolean('is_validated')->default(false); // clinically validated
            $table->json('tags')->nullable();
            $table->integer('usage_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['org_id', 'category']);
            $table->index(['is_public', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_banks');
    }
};
