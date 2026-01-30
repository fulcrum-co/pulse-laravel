<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_attempts', function (Blueprint $table) {
            $table->string('response_channel')->default('web')->after('status'); // web, sms, voice, chat
            $table->foreignId('delivery_id')->nullable()->after('response_channel')->constrained('survey_deliveries')->nullOnDelete();
            $table->json('voice_recordings')->nullable()->after('ai_analysis'); // paths to voice response audio
            $table->json('transcriptions')->nullable()->after('voice_recordings'); // transcribed voice responses
            $table->json('conversation_log')->nullable()->after('transcriptions'); // for chat-based surveys
            $table->json('raw_responses')->nullable()->after('conversation_log'); // before normalization
        });
    }

    public function down(): void
    {
        Schema::table('survey_attempts', function (Blueprint $table) {
            $table->dropForeign(['delivery_id']);
            $table->dropColumn([
                'response_channel',
                'delivery_id',
                'voice_recordings',
                'transcriptions',
                'conversation_log',
                'raw_responses',
            ]);
        });
    }
};
