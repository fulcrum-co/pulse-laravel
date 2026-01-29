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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('org_type')->default('district'); // consultant, district, school, department
            $table->string('org_name');
            $table->foreignId('parent_org_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->string('primary_contact_name')->nullable();
            $table->string('primary_contact_email')->nullable();
            $table->string('primary_contact_phone')->nullable();
            $table->json('address')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('timezone')->default('America/New_York');
            $table->string('subscription_plan')->nullable();
            $table->string('subscription_status')->default('trial');
            $table->string('billing_contact_email')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
