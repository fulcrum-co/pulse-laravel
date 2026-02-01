<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('marketplace_item_id')->constrained()->cascadeOnDelete();

            // Buyer info
            $table->foreignId('buyer_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buyer_org_id')->nullable()->constrained('organizations')->nullOnDelete();

            // Seller info
            $table->foreignId('seller_profile_id')->constrained()->cascadeOnDelete();

            // Transaction type
            $table->string('transaction_type'); // purchase, subscription, download (free)

            // Status
            $table->string('status')->default('pending'); // pending, completed, refunded, cancelled, failed

            // Financials
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('platform_fee', 10, 2)->default(0);
            $table->decimal('seller_payout', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');

            // License
            $table->string('license_type')->nullable(); // single, team, site, district
            $table->integer('seat_count')->nullable();
            $table->timestamp('license_expires_at')->nullable();

            // Stripe references
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('stripe_invoice_id')->nullable();

            // Subscription tracking
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);

            // Usage tracking
            $table->timestamp('first_accessed_at')->nullable();
            $table->integer('access_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['buyer_user_id', 'status']);
            $table->index(['seller_profile_id', 'status']);
            $table->index(['marketplace_item_id', 'status']);
            $table->index('transaction_type');
            $table->index('stripe_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_transactions');
    }
};
