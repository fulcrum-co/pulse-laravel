<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('collection_entries')) {
            return;
        }

        Schema::create('collection_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->onDelete('cascade');
            $table->foreignId('session_id')->constrained('collection_sessions')->onDelete('cascade');
            $table->string('contact_type'); // App\Models\Contact or App\Models\User
            $table->unsignedBigInteger('contact_id');
            $table->foreignId('collected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, in_progress, completed, skipped
            $table->string('input_mode')->default('form'); // form, voice, ai_conversation, grid
            $table->json('responses')->nullable(); // structured responses keyed by question_id
            $table->json('voice_recordings')->nullable(); // paths to audio files
            $table->json('transcriptions')->nullable(); // transcribed text from voice
            $table->json('ai_conversation_log')->nullable(); // AI conversation history
            $table->json('raw_input')->nullable(); // unprocessed input data
            $table->json('computed_scores')->nullable(); // calculated scores
            $table->json('flags')->nullable(); // risk flags, alerts
            $table->integer('duration_seconds')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('skip_reason')->nullable();
            $table->timestamps();

            $table->index(['contact_type', 'contact_id']);
            $table->index(['session_id', 'status']);
            $table->index(['collection_id', 'contact_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_entries');
    }
};
