<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('resources')) {
            return;
        }

        Schema::table('resources', function (Blueprint $table) {
            if (! Schema::hasColumn('resources', 'domain_tags')) {
                $table->json('domain_tags')->nullable()->after('tags');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('resources')) {
            return;
        }

        Schema::table('resources', function (Blueprint $table) {
            if (Schema::hasColumn('resources', 'domain_tags')) {
                $table->dropColumn('domain_tags');
            }
        });
    }
};
