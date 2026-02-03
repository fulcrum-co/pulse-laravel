<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('provider_payments')) {
            return;
        }

        Schema::create('provider_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('booking_id')->constrained('provider_bookings')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();

            // Who paid (User or Participant or Organization)
            $table->string('payer_type');
            $table->unsignedBigInteger('payer_id');

            // Amounts (in cents)
            $table->unsignedInteger('amount'); // Total charged
            $table->unsignedInteger('platform_fee')->default(0); // Our cut
            $table->unsignedInteger('provider_payout')->default(0); // Provider's cut
            $table->string('currency', 3)->default('USD');

            // Payment type
            $table->enum('payment_type', ['session', 'deposit', 'package', 'cancellation_fee'])->default('session');

            // Status
            $table->enum('status', ['pending', 'processing', 'completed', 'refunded', 'partially_refunded', 'failed'])->default('pending');

            // Stripe references
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_charge_id')->nullable();
            $table->string('stripe_transfer_id')->nullable(); // Transfer to provider's connected account
            $table->string('stripe_refund_id')->nullable();

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('transferred_at')->nullable(); // When provider received payout
            $table->timestamp('refunded_at')->nullable();
            $table->unsignedInteger('refund_amount')->nullable();
            $table->text('refund_reason')->nullable();

            // Metadata
            $table->json('metadata')->nullable(); // Additional payment details

            $table->timestamps();

            // Indexes
            $table->index(['payer_type', 'payer_id']);
            $table->index('stripe_payment_intent_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_payments');
    }
};
