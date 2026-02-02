<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('provider_accounts')) {
            return;
        }

        Schema::create('provider_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email')->unique();
            $table->string('password')->nullable(); // Nullable for email-only providers
            $table->enum('account_type', ['full', 'email_only'])->default('email_only');

            // Stripe Connect for payments
            $table->string('stripe_account_id')->nullable();
            $table->string('stripe_account_status')->nullable(); // 'pending', 'active', 'restricted'

            // GetStream Chat
            $table->string('stream_user_id')->nullable();
            $table->text('stream_user_token')->nullable();

            // Notification preferences
            $table->json('notification_preferences')->nullable(); // {email: true, sms: true, push: false}

            // Timestamps
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('email');
            $table->index('stripe_account_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_accounts');
    }
};
