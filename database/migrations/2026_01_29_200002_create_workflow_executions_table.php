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
        Schema::create('workflow_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->string('triggered_by')->nullable(); // metric_update, survey_response, schedule, manual_test, etc.
            $table->jsonb('trigger_data')->nullable();
            $table->jsonb('context')->default('{}');
            $table->string('status')->default('pending'); // pending, running, waiting, completed, failed, cancelled
            $table->string('current_node_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->jsonb('node_results')->default('{}');
            $table->text('error_message')->nullable();
            $table->timestamp('resume_at')->nullable();
            $table->jsonb('resume_data')->nullable();
            $table->timestamps();

            $table->index(['workflow_id', 'status']);
            $table->index(['org_id', 'created_at']);
            $table->index(['status', 'resume_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_executions');
    }
};
