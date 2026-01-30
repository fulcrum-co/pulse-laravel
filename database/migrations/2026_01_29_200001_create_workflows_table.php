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
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // draft, active, paused, archived
            $table->string('mode')->default('simple'); // simple, advanced
            $table->string('trigger_type')->default('manual'); // metric_threshold, metric_change, survey_response, survey_answer, attendance, schedule, manual
            $table->jsonb('trigger_config')->nullable();
            $table->jsonb('nodes')->default('[]');
            $table->jsonb('edges')->default('[]');
            $table->jsonb('settings')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_triggered_at')->nullable();
            $table->integer('execution_count')->default(0);
            $table->unsignedBigInteger('legacy_trigger_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['org_id', 'status']);
            $table->index(['org_id', 'trigger_type']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
