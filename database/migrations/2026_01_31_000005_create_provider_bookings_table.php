<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('provider_bookings')) {
            return;
        }

        Schema::create('provider_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained('provider_conversations')->nullOnDelete();

            // Who booked (User or Student)
            $table->string('booked_by_type'); // 'App\Models\User' or 'App\Models\Student'
            $table->unsignedBigInteger('booked_by_id');

            // Student receiving service
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            // Booking details
            $table->enum('booking_type', ['consultation', 'session', 'assessment'])->default('session');
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');

            // Schedule
            $table->timestamp('scheduled_at');
            $table->unsignedInteger('duration_minutes')->default(60);

            // Location
            $table->enum('location_type', ['in_person', 'remote', 'phone'])->default('remote');
            $table->text('location_details')->nullable(); // Address or video meeting link

            // Notes
            $table->text('notes')->nullable();
            $table->text('provider_notes')->nullable(); // Provider's private notes

            // Cancellation tracking
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by_type')->nullable();
            $table->unsignedBigInteger('cancelled_by_id')->nullable();

            // Confirmation tracking
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Reminders
            $table->timestamp('reminder_sent_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['booked_by_type', 'booked_by_id']);
            $table->index('scheduled_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_bookings');
    }
};
