<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('marketplace_purchases')) {
            return;
        }

        Schema::create('marketplace_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('marketplace_transactions')->cascadeOnDelete();
            $table->foreignId('marketplace_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('org_id')->nullable()->constrained('organizations')->nullOnDelete();

            // Access control
            $table->boolean('has_access')->default(true);
            $table->timestamp('access_granted_at')->nullable();
            $table->timestamp('access_expires_at')->nullable();
            $table->timestamp('access_revoked_at')->nullable();

            // Usage tracking
            $table->integer('downloads_remaining')->nullable();
            $table->timestamp('last_accessed_at')->nullable();

            $table->timestamps();

            // Unique constraint: one purchase record per user per item
            $table->unique(['user_id', 'marketplace_item_id']);

            $table->index(['marketplace_item_id', 'has_access']);
            $table->index(['user_id', 'has_access']);
            $table->index('access_expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_purchases');
    }
};
