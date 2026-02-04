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
        Schema::create('credit_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('parent_wallet_id')->nullable()->constrained('credit_wallets')->onDelete('set null');
            $table->string('wallet_mode')->default('separate'); // separate, pooled
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('lifetime_purchased', 15, 2)->default(0);
            $table->decimal('lifetime_used', 15, 2)->default(0);
            $table->string('pricing_tier')->default('starter'); // starter, growth, enterprise, strategic
            $table->boolean('auto_topup_enabled')->default(false);
            $table->decimal('auto_topup_threshold', 15, 2)->nullable();
            $table->decimal('auto_topup_amount', 10, 2)->nullable();
            $table->integer('auto_topup_monthly_limit')->default(3);
            $table->integer('auto_topup_count_this_month')->default(0);
            $table->timestamp('grace_period_until')->nullable();
            $table->timestamps();

            $table->unique('org_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_wallets');
    }
};
