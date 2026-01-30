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
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();

            // Distribution type
            $table->string('distribution_type')->default('one_time'); // one_time, recurring
            $table->string('channel')->default('email'); // email, sms
            $table->string('status')->default('draft'); // draft, scheduled, active, paused, completed, archived

            // Content source
            $table->string('content_type')->default('custom'); // report, custom
            $table->foreignId('report_id')->nullable()->constrained('custom_reports')->nullOnDelete();
            $table->string('report_mode')->nullable(); // static (PDF), live (dashboard link)

            // Custom message content
            $table->string('subject')->nullable(); // for email
            $table->text('message_body')->nullable(); // text/HTML with merge fields
            $table->foreignId('message_template_id')->nullable()->constrained('message_templates')->nullOnDelete();

            // Recipients
            $table->string('recipient_type')->default('contact_list'); // contact_list, individual, query
            $table->foreignId('contact_list_id')->nullable()->constrained('contact_lists')->nullOnDelete();
            $table->json('recipient_ids')->nullable(); // array for individuals
            $table->json('recipient_query')->nullable(); // for dynamic query

            // Scheduling
            $table->timestamp('scheduled_for')->nullable();
            $table->string('timezone')->default('America/New_York');
            $table->json('recurrence_config')->nullable(); // {type, interval, days, end_date}

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};
