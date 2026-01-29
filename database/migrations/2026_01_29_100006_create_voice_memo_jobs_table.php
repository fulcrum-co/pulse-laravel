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
        if (Schema::hasTable('voice_memo_jobs')) {
            return;
        }

        Schema::create('voice_memo_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_note_id')->constrained('contact_notes')->cascadeOnDelete();

            $table->string('status')->default('pending'); // pending, uploading, transcribing, extracting, completed, failed
            $table->string('provider')->default('whisper'); // whisper, assembly_ai
            $table->string('external_job_id')->nullable();

            $table->json('transcription_result')->nullable();
            $table->json('extracted_data')->nullable();
            $table->text('error_message')->nullable();

            $table->integer('retry_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voice_memo_jobs');
    }
};
