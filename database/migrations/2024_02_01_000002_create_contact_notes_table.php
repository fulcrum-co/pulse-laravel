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
        Schema::create('contact_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();

            // Polymorphic contact reference
            $table->string('contact_type');
            $table->unsignedBigInteger('contact_id');

            // Note content
            $table->string('note_type')->default('general'); // general, follow_up, concern, milestone, voice_memo, ai_summary
            $table->text('content');
            $table->text('raw_content')->nullable(); // Original before AI filtering
            $table->json('structured_data')->nullable(); // Extracted data from voice memos

            // Voice memo fields
            $table->boolean('is_voice_memo')->default(false);
            $table->string('audio_file_path')->nullable();
            $table->string('audio_disk')->default('local'); // local, s3
            $table->integer('audio_duration_seconds')->nullable();
            $table->text('transcription')->nullable();
            $table->string('transcription_status')->nullable(); // pending, processing, completed, failed
            $table->string('transcription_provider')->nullable(); // whisper, assembly_ai
            $table->timestamp('transcribed_at')->nullable();

            // Privacy/Visibility
            $table->boolean('is_private')->default(false); // Only visible to author
            $table->string('visibility')->default('organization'); // private, team, organization
            $table->json('visible_to_roles')->nullable(); // ['admin', 'counselor']

            // Linking
            $table->foreignId('parent_note_id')->nullable()->constrained('contact_notes')->nullOnDelete();
            $table->foreignId('related_plan_id')->nullable()->constrained('strategic_plans')->nullOnDelete();
            $table->foreignId('related_survey_attempt_id')->nullable()->constrained('survey_attempts')->nullOnDelete();

            // FERPA compliance
            $table->boolean('contains_pii')->default(true);
            $table->boolean('requires_consent_for_share')->default(true);

            // Author
            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['contact_type', 'contact_id', 'created_at']);
            $table->index(['org_id', 'note_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_notes');
    }
};
