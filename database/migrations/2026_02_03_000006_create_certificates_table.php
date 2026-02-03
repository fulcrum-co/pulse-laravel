<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Public verification ID
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mini_course_id')->constrained('mini_courses')->cascadeOnDelete();
            $table->foreignId('cohort_id')->nullable()->constrained('cohorts')->nullOnDelete();
            $table->foreignId('cohort_member_id')->nullable()->constrained('cohort_members')->nullOnDelete();
            $table->string('type')->default('completion'); // completion, badge, credential
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('badge_name')->nullable();
            $table->string('badge_image_url')->nullable();
            $table->string('certificate_url')->nullable(); // Generated PDF URL
            $table->string('verification_url')->nullable(); // Public verification page
            $table->json('metadata')->nullable(); // Additional cert data
            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_revoked')->default(false);
            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason')->nullable();
            // LinkedIn sharing
            $table->boolean('shared_to_linkedin')->default(false);
            $table->timestamp('linkedin_shared_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['mini_course_id']);
            $table->index(['cohort_id']);
            $table->index(['issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
