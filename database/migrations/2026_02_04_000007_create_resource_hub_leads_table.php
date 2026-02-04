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
        Schema::create('resource_hub_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('role')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->default('resource_hub');
            $table->string('source_url')->nullable();
            $table->json('utm_params')->nullable();
            $table->json('interests')->nullable(); // Resources/courses they viewed
            $table->json('metadata')->nullable();
            $table->integer('resource_views')->default(0);
            $table->integer('course_views')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verification_token')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['org_id', 'email']);
            $table->index(['org_id', 'created_at']);
            $table->unique(['org_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_hub_leads');
    }
};
