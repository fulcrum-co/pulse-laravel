<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('programs')) {
            return;
        }

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('program_type'); // therapy, tutoring, mentorship, enrichment, intervention, support_group, external_service
            $table->string('provider_org_name')->nullable();
            $table->json('target_needs')->nullable();
            $table->json('eligibility_criteria')->nullable();
            $table->string('cost_structure')->default('free'); // free, sliding_scale, fixed, insurance
            $table->text('cost_details')->nullable();
            $table->unsignedSmallInteger('duration_weeks')->nullable();
            $table->unsignedTinyInteger('frequency_per_week')->nullable();
            $table->string('location_type')->default('in_person'); // in_person, virtual, hybrid
            $table->text('location_address')->nullable();
            $table->json('contact_info')->nullable();
            $table->string('enrollment_url')->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->unsignedInteger('current_enrollment')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_rolling_enrollment')->default(false);
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['org_id', 'program_type']);
            $table->index(['org_id', 'active']);
            $table->index(['cost_structure']);
            $table->index(['location_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
