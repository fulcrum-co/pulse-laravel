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
        Schema::create('credit_rate_cards', function (Blueprint $table) {
            $table->id();
            $table->string('action_type')->unique(); // ai_analysis, transcription_minute, sms_outbound
            $table->string('display_name');
            $table->string('category'); // ai, telecom, voice, storage
            $table->decimal('vendor_cost', 10, 6); // Base cost per unit from vendor
            $table->string('vendor_unit'); // per_1k_tokens, per_minute, per_message
            $table->decimal('credit_cost', 10, 2); // Credits charged (vendor_cost * multiplier * 1000)
            $table->boolean('active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_rate_cards');
    }
};
