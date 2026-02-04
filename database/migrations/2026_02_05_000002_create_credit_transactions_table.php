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
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('wallet_id')->constrained('credit_wallets')->onDelete('cascade');
            $table->string('type'); // purchase, usage, refund, adjustment, bonus
            $table->decimal('amount', 15, 2); // Positive for credits in, negative for usage
            $table->decimal('balance_after', 15, 2);
            $table->string('action_type')->nullable(); // ai_analysis, transcription, sms, etc.
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('reference_type')->nullable(); // Model class for polymorphic
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();

            $table->index(['org_id', 'created_at']);
            $table->index(['wallet_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
