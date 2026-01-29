<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('objective_id')->constrained('objectives')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('not_started'); // on_track, at_risk, off_track, not_started
            $table->timestamps();
            $table->softDeletes();

            $table->index(['objective_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
