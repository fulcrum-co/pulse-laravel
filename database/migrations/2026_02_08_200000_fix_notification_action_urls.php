<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $replacements = [
            '/students' => '/contacts',
            '/strategies' => '/marketplace/strategies',
            '/learning' => '/resources/courses',
            '/collections' => '/collect',
        ];

        foreach ($replacements as $old => $new) {
            DB::table('user_notifications')
                ->where('action_url', $old)
                ->update(['action_url' => $new]);
        }
    }

    public function down(): void
    {
        $replacements = [
            '/contacts' => '/students',
            '/marketplace/strategies' => '/strategies',
            '/resources/courses' => '/learning',
            '/collect' => '/collections',
        ];

        foreach ($replacements as $old => $new) {
            DB::table('user_notifications')
                ->where('action_url', $old)
                ->update(['action_url' => $new]);
        }
    }
};
