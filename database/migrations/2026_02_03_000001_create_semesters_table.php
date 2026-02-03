<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('academic_year'); // e.g., "2025-2026"
            $table->string('term_name'); // e.g., "Fall", "Spring", "Summer", "Q1", "Q2"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['org_id', 'is_active']);
            $table->index(['org_id', 'academic_year']);
            $table->unique(['org_id', 'academic_year', 'term_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
