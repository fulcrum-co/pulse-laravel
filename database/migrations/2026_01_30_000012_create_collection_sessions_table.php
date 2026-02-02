<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('collection_sessions')) {
            return;
        }

        Schema::create('collection_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->nullable()->constrained('collection_schedules')->nullOnDelete();
            $table->date('session_date');
            $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
            $table->integer('total_contacts')->default(0);
            $table->integer('completed_count')->default(0);
            $table->integer('skipped_count')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('collected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['collection_id', 'session_date']);
            $table->index(['collection_id', 'status']);
            $table->index(['collected_by_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_sessions');
    }
};
