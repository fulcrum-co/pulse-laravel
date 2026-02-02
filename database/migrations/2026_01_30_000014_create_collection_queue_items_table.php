<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('collection_queue_items')) {
            return;
        }

        Schema::create('collection_queue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('collection_sessions')->onDelete('cascade');
            $table->foreignId('entry_id')->constrained('collection_entries')->onDelete('cascade');
            $table->string('contact_type'); // App\Models\Student or App\Models\User
            $table->unsignedBigInteger('contact_id');
            $table->integer('position'); // queue position (1-based)
            $table->string('status')->default('pending'); // pending, current, completed, skipped
            $table->integer('priority')->default(3); // 1-5 (5 = highest priority)
            $table->string('priority_reason')->nullable(); // why this contact is prioritized
            $table->timestamps();

            $table->index(['session_id', 'position']);
            $table->index(['session_id', 'status']);
            $table->index(['session_id', 'priority', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_queue_items');
    }
};
