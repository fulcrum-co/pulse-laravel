<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_report_id')->constrained('custom_reports')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('report_comments')->cascadeOnDelete();
            $table->string('element_id')->nullable(); // UUID of element being commented on
            $table->text('content');
            $table->float('position_x')->nullable(); // For positioned comments on canvas
            $table->float('position_y')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['custom_report_id', 'element_id']);
            $table->index(['custom_report_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_comments');
    }
};
