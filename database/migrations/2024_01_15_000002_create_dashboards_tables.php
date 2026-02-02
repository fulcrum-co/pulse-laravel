<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Skip if table already exists (idempotent migration)
        if (Schema::hasTable('dashboards')) {
            return;
        }

        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_shared')->default(false);
            $table->json('layout')->nullable(); // Grid layout config
            $table->timestamps();

            $table->index(['org_id', 'user_id']);
            $table->index(['org_id', 'is_shared']);
        });

        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained()->cascadeOnDelete();
            $table->string('widget_type'); // metric_card, bar_chart, line_chart, student_list, survey_summary, alert_feed
            $table->string('title');
            $table->json('config')->nullable(); // Data source, filters, display options
            $table->json('position')->nullable(); // x, y, width, height for grid
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['dashboard_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_widgets');
        Schema::dropIfExists('dashboards');
    }
};
