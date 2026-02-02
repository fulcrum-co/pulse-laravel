<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('triggers')) {
            return;
        }

        Schema::create('triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_type')->default('keyword'); // keyword, sentiment, risk_score, pattern
            $table->json('conditions');
            $table->json('actions');
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->json('notify_users')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['org_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('triggers');
    }
};
