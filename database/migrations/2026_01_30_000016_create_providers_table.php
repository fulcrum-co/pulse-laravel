<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('providers')) {
            return;
        }

        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->string('provider_type'); // therapist, tutor, coach, mentor, support_person, specialist
            $table->json('specialty_areas')->nullable();
            $table->string('credentials')->nullable();
            $table->text('bio')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('availability_notes')->nullable();
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->boolean('accepts_insurance')->default(false);
            $table->json('insurance_types')->nullable();
            $table->text('location_address')->nullable();
            $table->boolean('serves_remote')->default(false);
            $table->boolean('serves_in_person')->default(true);
            $table->integer('service_radius_miles')->nullable();
            $table->decimal('ratings_average', 3, 2)->default(0);
            $table->unsignedInteger('ratings_count')->default(0);
            $table->string('external_profile_url')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['org_id', 'provider_type']);
            $table->index(['org_id', 'active']);
            $table->index(['serves_remote', 'serves_in_person']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
