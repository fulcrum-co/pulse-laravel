<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            // Add support for multiple reports
            $table->json('report_ids')->nullable()->after('report_id');

            // Add support for multiple contact lists
            $table->json('contact_list_ids')->nullable()->after('contact_list_id');
        });

        // Migrate existing data: copy single report_id to report_ids array
        DB::table('distributions')
            ->whereNotNull('report_id')
            ->get()
            ->each(function ($distribution) {
                DB::table('distributions')
                    ->where('id', $distribution->id)
                    ->update(['report_ids' => json_encode([$distribution->report_id])]);
            });

        // Migrate existing data: copy single contact_list_id to contact_list_ids array
        DB::table('distributions')
            ->whereNotNull('contact_list_id')
            ->get()
            ->each(function ($distribution) {
                DB::table('distributions')
                    ->where('id', $distribution->id)
                    ->update(['contact_list_ids' => json_encode([$distribution->contact_list_id])]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropColumn(['report_ids', 'contact_list_ids']);
        });
    }
};
