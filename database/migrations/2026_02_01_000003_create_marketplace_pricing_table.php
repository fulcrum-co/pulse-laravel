<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_item_id')->constrained()->cascadeOnDelete();

            // Pricing type
            $table->string('pricing_type'); // free, one_time, recurring

            // One-time pricing
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('original_price', 10, 2)->nullable(); // For showing discounts

            // Recurring pricing
            $table->string('billing_interval')->nullable(); // month, year
            $table->integer('billing_interval_count')->default(1);
            $table->decimal('recurring_price', 10, 2)->nullable();

            // License options
            $table->string('license_type')->default('single'); // single, team, site, district
            $table->integer('seat_limit')->nullable(); // For team licenses
            $table->json('license_terms')->nullable(); // Custom terms/restrictions

            // Stripe references
            $table->string('stripe_price_id')->nullable();
            $table->string('stripe_product_id')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['marketplace_item_id', 'license_type']);
            $table->index('pricing_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_pricing');
    }
};
