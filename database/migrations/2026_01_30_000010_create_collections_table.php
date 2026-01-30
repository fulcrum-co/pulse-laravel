<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('collection_type')->default('recurring'); // recurring, one_time, event_triggered
            $table->string('data_source')->default('inline'); // survey, inline, hybrid
            $table->foreignId('survey_id')->nullable()->constrained('surveys')->nullOnDelete();
            $table->json('inline_questions')->nullable();
            $table->string('format_mode')->default('form'); // conversational, form, grid
            $table->string('status')->default('draft'); // draft, active, paused, archived
            $table->json('settings')->nullable(); // voice_enabled, ai_follow_up, etc.
            $table->json('contact_scope')->nullable(); // target_grades, classrooms, tags, filters
            $table->json('reminder_config')->nullable(); // reminder settings
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['org_id', 'status']);
            $table->index(['org_id', 'collection_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
