<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mini_course_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('contact_type'); // App\Models\Student or App\Models\User
            $table->unsignedBigInteger('contact_id');
            $table->foreignId('mini_course_id')->constrained('mini_courses')->cascadeOnDelete();
            $table->string('suggestion_source'); // ai_generated, ai_recommended, rule_based, peer_success, manual
            $table->decimal('relevance_score', 5, 2)->nullable(); // 0-100
            $table->json('trigger_signals')->nullable(); // What data triggered this suggestion
            $table->text('ai_rationale')->nullable();
            $table->json('ai_explanation')->nullable(); // Structured explanation with signals, outcomes
            $table->json('intended_outcomes')->nullable();
            $table->string('status')->default('pending'); // pending, accepted, declined, auto_enrolled
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->foreignId('enrollment_id')->nullable()->constrained('mini_course_enrollments')->nullOnDelete();
            $table->timestamps();

            // Indexes
            $table->index(['contact_type', 'contact_id']);
            $table->index(['org_id', 'status']);
            $table->index(['suggestion_source']);
            $table->unique(['contact_type', 'contact_id', 'mini_course_id']);
        });

        // Add foreign key from enrollments to suggestions
        Schema::table('mini_course_enrollments', function (Blueprint $table) {
            $table->foreign('suggestion_id')
                  ->references('id')
                  ->on('mini_course_suggestions')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mini_course_enrollments', function (Blueprint $table) {
            $table->dropForeign(['suggestion_id']);
        });

        Schema::dropIfExists('mini_course_suggestions');
    }
};
