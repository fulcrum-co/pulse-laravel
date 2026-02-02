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
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('distribution_schedules')) {
            return;
        }

        Schema::create('distribution_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained()->cascadeOnDelete();

            $table->string('schedule_type'); // interval, custom
            $table->string('interval_type')->nullable(); // daily, weekly, monthly
            $table->integer('interval_value')->default(1);
            $table->json('custom_days')->nullable(); // ['monday', 'friday']
            $table->time('send_time')->nullable();
            $table->string('timezone')->default('America/New_York');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('next_scheduled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_schedules');
    }
};
