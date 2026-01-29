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
        if (Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // What was accessed/modified
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');

            // Contact affected (for FERPA tracking)
            $table->string('contact_type')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();

            // Action details
            $table->string('action'); // view, create, update, delete, export, share, print
            $table->string('action_category'); // data_access, data_modification, report_generation, share
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('description')->nullable();

            // Request context
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id')->nullable();
            $table->string('request_method')->nullable();
            $table->string('request_url')->nullable();

            // FERPA specific
            $table->boolean('involves_pii')->default(false);
            $table->boolean('involves_education_records')->default(false);
            $table->string('legal_basis')->nullable(); // legitimate_educational_interest, directory_info, consent

            $table->timestamp('created_at');

            // Indexes for compliance queries
            $table->index(['org_id', 'created_at']);
            $table->index(['user_id', 'action']);
            $table->index(['contact_type', 'contact_id']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['involves_pii', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
