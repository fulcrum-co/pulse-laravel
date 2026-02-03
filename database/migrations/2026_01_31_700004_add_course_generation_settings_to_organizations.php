<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->json('course_generation_settings')->nullable()->after('active');
            /*
             * Default structure:
             * {
             *   "enabled": true,
             *   "approval_required": true,
             *   "approval_roles": ["admin", "support_person"],
             *   "auto_approve_templates": false,
             *   "allowed_triggers": ["risk_threshold", "workflow", "manual"],
             *   "risk_threshold_config": {
             *     "enabled": true,
             *     "min_risk_score": 0.7,
             *     "risk_factors": ["attendance", "behavior", "academic"],
             *     "cooldown_days": 30
             *   },
             *   "default_generation_strategy": "hybrid",
             *   "external_sources": {
             *     "youtube_enabled": true,
             *     "khan_academy_enabled": true,
             *     "custom_uploads_enabled": true
             *   },
             *   "ai_config": {
             *     "model": "claude-sonnet",
             *     "creativity_level": "balanced",
             *     "max_steps_per_course": 10
             *   },
             *   "notification_settings": {
             *     "notify_on_generation": true,
             *     "notify_on_approval_needed": true,
             *     "notify_roles": ["admin", "support_person"]
             *   }
             * }
             */
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('course_generation_settings');
        });
    }
};
