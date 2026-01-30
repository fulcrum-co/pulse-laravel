<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->string('creation_mode')->default('static')->after('survey_type'); // static, chat, voice, ai_assisted
            $table->foreignId('template_id')->nullable()->after('creation_mode')->constrained('survey_templates')->nullOnDelete();
            $table->foreignId('creation_session_id')->nullable()->after('template_id')->constrained('survey_creation_sessions')->nullOnDelete();
            $table->json('interpretation_config')->nullable()->after('questions'); // AI interpretation rules
            $table->json('delivery_channels')->nullable()->after('target_classrooms'); // ['web', 'sms', 'voice']
            $table->json('voice_config')->nullable()->after('delivery_channels'); // TTS voice, pace, language
            $table->boolean('allow_voice_responses')->default(false)->after('voice_config');
            $table->boolean('ai_follow_up_enabled')->default(false)->after('allow_voice_responses');
            $table->text('llm_system_prompt')->nullable()->after('ai_follow_up_enabled');
            $table->json('scoring_config')->nullable()->after('llm_system_prompt'); // how to calculate scores
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropForeign(['creation_session_id']);
            $table->dropColumn([
                'creation_mode',
                'template_id',
                'creation_session_id',
                'interpretation_config',
                'delivery_channels',
                'voice_config',
                'allow_voice_responses',
                'ai_follow_up_enabled',
                'llm_system_prompt',
                'scoring_config',
            ]);
        });
    }
};
