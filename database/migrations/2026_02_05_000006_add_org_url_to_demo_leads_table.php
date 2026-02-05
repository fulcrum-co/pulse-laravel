<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('demo_leads')) {
            return;
        }

        if (! Schema::hasColumn('demo_leads', 'org_url')) {
            Schema::table('demo_leads', function (Blueprint $table) {
                $table->string('org_url')->nullable()->after('org_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('demo_leads') && Schema::hasColumn('demo_leads', 'org_url')) {
            Schema::table('demo_leads', function (Blueprint $table) {
                $table->dropColumn('org_url');
            });
        }
    }
};
