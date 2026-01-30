<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_creation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_id')->nullable()->constrained()->nullOnDelete();
            $table->string('creation_mode'); // chat, voice, static, hybrid
            $table->string('status')->default('active'); // active, completed, abandoned
            $table->json('conversation_history')->nullable();
            $table->json('draft_questions')->nullable();
            $table->json('ai_suggestions')->nullable();
            $table->json('context')->nullable(); // purpose, target audience, etc.
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['org_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_creation_sessions');
    }
};
