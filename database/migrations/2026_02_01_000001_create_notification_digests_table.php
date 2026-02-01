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
        Schema::create('notification_digests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('digest_type', 20); // daily, weekly
            $table->json('notification_ids'); // Array of included notification IDs
            $table->integer('notification_count');
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['user_id', 'digest_type', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_digests');
    }
};
