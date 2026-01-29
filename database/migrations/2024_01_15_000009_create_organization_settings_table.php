<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->unique()->constrained('organizations')->cascadeOnDelete();
            $table->json('status_labels')->nullable(); // Custom labels for on_track, at_risk, off_track, not_started
            $table->json('risk_labels')->nullable(); // Custom labels for good, low, high
            $table->json('settings')->nullable(); // Other org-specific settings
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};
