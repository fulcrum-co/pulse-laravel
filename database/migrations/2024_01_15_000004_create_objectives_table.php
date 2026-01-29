<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('focus_area_id')->constrained('focus_areas')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('not_started'); // on_track, at_risk, off_track, not_started
            $table->timestamps();
            $table->softDeletes();

            $table->index(['focus_area_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objectives');
    }
};
