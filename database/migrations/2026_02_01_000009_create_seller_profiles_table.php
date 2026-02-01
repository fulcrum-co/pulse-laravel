<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->nullOnDelete();

            // Profile info
            $table->string('display_name');
            $table->string('slug')->unique();
            $table->text('bio')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('banner_url')->nullable();

            // Expertise
            $table->json('expertise_areas')->nullable();
            $table->json('credentials')->nullable();

            // Seller type
            $table->string('seller_type')->default('individual'); // individual, organization, verified_educator

            // Verification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_badge')->nullable(); // educator, expert, partner, top_seller

            // Stripe Connect
            $table->string('stripe_account_id')->nullable();
            $table->string('stripe_account_status')->nullable(); // pending, active, restricted, disabled
            $table->boolean('payouts_enabled')->default(false);

            // Stats
            $table->integer('total_sales')->default(0);
            $table->integer('total_items')->default(0);
            $table->decimal('lifetime_revenue', 12, 2)->default(0);
            $table->decimal('ratings_average', 3, 2)->nullable();
            $table->integer('ratings_count')->default(0);
            $table->integer('followers_count')->default(0);

            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'active']);
            $table->index('seller_type');
            $table->index('is_verified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_profiles');
    }
};
