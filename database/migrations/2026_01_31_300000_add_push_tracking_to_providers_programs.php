<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add source tracking to providers for push functionality
        Schema::table('providers', function (Blueprint $table) {
            $table->foreignId('source_provider_id')->nullable()->after('org_id')
                ->constrained('providers')->nullOnDelete();
            $table->foreignId('source_org_id')->nullable()->after('source_provider_id')
                ->constrained('organizations')->nullOnDelete();
        });

        // Add source tracking to programs for push functionality
        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('source_program_id')->nullable()->after('org_id')
                ->constrained('programs')->nullOnDelete();
            $table->foreignId('source_org_id')->nullable()->after('source_program_id')
                ->constrained('organizations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropForeign(['source_provider_id']);
            $table->dropForeign(['source_org_id']);
            $table->dropColumn(['source_provider_id', 'source_org_id']);
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropForeign(['source_program_id']);
            $table->dropForeign(['source_org_id']);
            $table->dropColumn(['source_program_id', 'source_org_id']);
        });
    }
};
