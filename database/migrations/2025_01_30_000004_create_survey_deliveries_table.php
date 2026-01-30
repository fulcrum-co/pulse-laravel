<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_attempt_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel'); // web, sms, voice_call, whatsapp, chat
            $table->string('status')->default('pending'); // pending, sent, in_progress, completed, failed
            $table->nullableMorphs('recipient'); // student, user, etc.
            $table->string('phone_number')->nullable(); // E.164 format for SMS/voice/WhatsApp
            $table->string('external_id')->nullable(); // Sinch call/message ID
            $table->json('delivery_metadata')->nullable(); // channel-specific data
            $table->json('response_data')->nullable(); // raw response before processing
            $table->integer('current_question_index')->default(0); // for conversational delivery
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['survey_id', 'channel', 'status']);
            $table->index(['recipient_type', 'recipient_id']);
            $table->index(['phone_number', 'status']);
            $table->index('external_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_deliveries');
    }
};
