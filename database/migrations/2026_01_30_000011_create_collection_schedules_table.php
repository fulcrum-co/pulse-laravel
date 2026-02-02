<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('collection_schedules')) {
            return;
        }

        Schema::create('collection_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained()->onDelete('cascade');
            $table->string('schedule_type')->default('interval'); // interval, custom, event
            $table->string('interval_type')->nullable(); // daily, weekly, monthly
            $table->integer('interval_value')->nullable()->default(1); // every N days/weeks/months
            $table->json('custom_days')->nullable(); // ['monday', 'wednesday', 'friday']
            $table->json('custom_times')->nullable(); // ['09:00', '14:00']
            $table->json('event_trigger')->nullable(); // { type: 'survey_completed', survey_id: X }
            $table->string('timezone')->default('America/New_York');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamp('next_scheduled_at')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'next_scheduled_at']);
            $table->index(['collection_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_schedules');
    }
};
