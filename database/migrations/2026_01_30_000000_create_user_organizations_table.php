<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('user_organizations')) {
            Schema::create('user_organizations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
                $table->string('role')->nullable(); // Role within this specific organization
                $table->boolean('is_primary')->default(false); // Is this the user's primary org
                $table->boolean('can_manage')->default(false); // Can manage this org's settings
                $table->timestamps();

                $table->unique(['user_id', 'organization_id']);
                $table->index(['user_id', 'is_primary']);
                $table->index('organization_id');
            });
        }

        // Add current_org_id to users table for tracking which org they're currently viewing
        if (! Schema::hasColumn('users', 'current_org_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('current_org_id')->nullable()->after('org_id')->constrained('organizations')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_org_id']);
            $table->dropColumn('current_org_id');
        });

        Schema::dropIfExists('user_organizations');
    }
};
