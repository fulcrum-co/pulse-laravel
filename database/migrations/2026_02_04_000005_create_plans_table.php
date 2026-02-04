<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plans')) {
            return;
        }

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->morphs('plannable');
            $table->string('title');
            $table->string('status')->default('active');
            $table->timestamp('review_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
