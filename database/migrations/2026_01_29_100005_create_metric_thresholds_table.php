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
        if (Schema::hasTable('metric_thresholds')) {
            return;
        }

        Schema::create('metric_thresholds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();

            $table->string('metric_category');
            $table->string('metric_key');
            $table->string('contact_type')->nullable(); // null = all contact types

            // Thresholds (using numeric_value comparison)
            $table->decimal('on_track_min', 10, 4)->nullable();
            $table->decimal('at_risk_min', 10, 4)->nullable();
            $table->decimal('off_track_min', 10, 4)->nullable();

            // Heat map colors (hex or Tailwind class)
            $table->string('color_on_track')->default('#22c55e'); // green-500
            $table->string('color_at_risk')->default('#eab308'); // yellow-500
            $table->string('color_off_track')->default('#ef4444'); // red-500
            $table->string('color_no_data')->default('#9ca3af'); // gray-400

            // Labels
            $table->string('label_on_track')->nullable();
            $table->string('label_at_risk')->nullable();
            $table->string('label_off_track')->nullable();

            $table->boolean('invert_scale')->default(false); // For metrics where lower is better
            $table->boolean('active')->default(true);

            $table->timestamps();

            $table->unique(['org_id', 'metric_category', 'metric_key', 'contact_type'], 'metric_thresholds_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metric_thresholds');
    }
};
