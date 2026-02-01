<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Default notification preferences schema per PRD Section 4.2.
     * Each category has channel preferences (in_app, email, sms).
     */
    public const DEFAULT_PREFERENCES = [
        'workflow' => ['in_app' => true, 'email' => false, 'sms' => false],
        'workflow_custom' => ['in_app' => true, 'email' => false, 'sms' => false],
        'survey' => ['in_app' => true, 'email' => true, 'sms' => false],
        'report' => ['in_app' => true, 'email' => true, 'sms' => false],
        'strategy' => ['in_app' => true, 'email' => true, 'sms' => false],
        'course' => ['in_app' => true, 'email' => true, 'sms' => false],
        'collection' => ['in_app' => true, 'email' => false, 'sms' => false],
        'system' => ['in_app' => true, 'email' => true, 'sms' => false],
        'quiet_hours' => ['enabled' => false, 'start' => '21:00', 'end' => '07:00'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('notification_preferences')
                ->nullable()
                ->after('preferred_contact_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_preferences');
        });
    }
};
