<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->onDelete('cascade');
            $table->foreignId('session_id')->nullable()->constrained('collection_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('channel'); // sms, email, whatsapp
            $table->string('status')->default('pending'); // pending, sent, delivered, failed
            $table->timestamp('scheduled_for');
            $table->timestamp('sent_at')->nullable();
            $table->json('delivery_metadata')->nullable(); // external_id, error messages, etc.
            $table->text('message_template')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_for']);
            $table->index(['user_id', 'status']);
            $table->index(['collection_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_reminders');
    }
};
