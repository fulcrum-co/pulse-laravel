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
        Schema::create('feature_valves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->string('feature_key'); // ai_analysis, voice_transcription, sms_outreach
            $table->boolean('is_active')->default(true);
            $table->integer('daily_limit')->nullable();
            $table->integer('daily_usage')->default(0);
            $table->string('reversion_message')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('changed_at')->nullable();
            $table->string('change_reason')->nullable();
            $table->timestamps();

            $table->unique(['org_id', 'feature_key']);
            $table->index('feature_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_valves');
    }
};
