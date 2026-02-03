<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('report_collaborators')) {
            return;
        }

        Schema::create('report_collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_report_id')->constrained('custom_reports')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['owner', 'editor', 'viewer'])->default('editor');
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['custom_report_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_collaborators');
    }
};
