<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained('marketplace_transactions')->nullOnDelete();

            // Rating
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('review_text')->nullable();
            $table->json('rating_breakdown')->nullable(); // e.g., {quality: 5, value: 4, usability: 5}

            // Status
            $table->string('status')->default('pending'); // pending, published, hidden, flagged

            // Verification
            $table->boolean('is_verified_purchase')->default(false);

            // Engagement
            $table->integer('helpful_count')->default(0);

            // Seller response
            $table->text('seller_response')->nullable();
            $table->timestamp('seller_responded_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['marketplace_item_id', 'status']);
            $table->index(['user_id', 'marketplace_item_id']);
            $table->index('rating');
            $table->index('is_verified_purchase');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_reviews');
    }
};
