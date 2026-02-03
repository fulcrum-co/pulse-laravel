<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('provider_conversations')) {
            return;
        }

        Schema::create('provider_conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();

            // Who initiated the conversation (User or Learner)
            $table->string('initiator_type'); // 'App\Models\User' or 'App\Models\Learner'
            $table->unsignedBigInteger('initiator_id');

            // Optional: specific learner being discussed
            $table->foreignId('learner_id')->nullable()->constrained()->nullOnDelete();

            // GetStream channel identifier
            $table->string('stream_channel_id')->nullable();
            $table->string('stream_channel_type')->default('messaging');

            // Conversation state
            $table->enum('status', ['active', 'archived', 'blocked'])->default('active');

            // Message preview for UI
            $table->timestamp('last_message_at')->nullable();
            $table->text('last_message_preview')->nullable();
            $table->string('last_message_sender_type')->nullable();
            $table->unsignedBigInteger('last_message_sender_id')->nullable();

            // Unread counts (synced from GetStream webhooks)
            $table->unsignedInteger('unread_count_provider')->default(0);
            $table->unsignedInteger('unread_count_initiator')->default(0);

            // Notification tracking (to rate-limit notifications)
            $table->timestamp('last_notification_sent_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['initiator_type', 'initiator_id']);
            $table->index('stream_channel_id');
            $table->index('status');
            $table->index('last_message_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_conversations');
    }
};
