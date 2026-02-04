<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pending_extractions')) {
            return;
        }

        Schema::create('pending_extractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('collection_event_id')->constrained('collection_events')->cascadeOnDelete();
            $table->text('raw_transcript')->nullable();
            $table->string('audio_path')->nullable();
            $table->json('extracted_data')->nullable();
            $table->integer('confidence_score')->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_extractions');
    }
};
