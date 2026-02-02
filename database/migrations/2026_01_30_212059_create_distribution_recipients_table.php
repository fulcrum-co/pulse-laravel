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
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('distribution_recipients')) {
            return;
        }

        Schema::create('distribution_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')->constrained('distribution_deliveries')->cascadeOnDelete();

            // Polymorphic contact reference
            $table->string('contact_type'); // student, user, etc.
            $table->unsignedBigInteger('contact_id');

            // Denormalized for delivery
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Status tracking
            $table->string('status')->default('pending'); // pending, sent, delivered, opened, clicked, bounced, failed
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();

            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // message_id, provider_response

            $table->timestamps();

            $table->index(['contact_type', 'contact_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_recipients');
    }
};
