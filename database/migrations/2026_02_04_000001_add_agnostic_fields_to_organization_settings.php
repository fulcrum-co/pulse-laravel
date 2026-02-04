<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organization_settings')) {
            return;
        }

        Schema::table('organization_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('organization_settings', 'contact_label_singular')) {
                $table->string('contact_label_singular')->default('Contact');
            }
            if (! Schema::hasColumn('organization_settings', 'contact_label_plural')) {
                $table->string('contact_label_plural')->default('Contacts');
            }
            if (! Schema::hasColumn('organization_settings', 'plan_label')) {
                $table->string('plan_label')->default('Plan');
            }
            if (! Schema::hasColumn('organization_settings', 'primary_color')) {
                $table->string('primary_color')->default('#3B82F6');
            }
            if (! Schema::hasColumn('organization_settings', 'logo_path')) {
                $table->string('logo_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('organization_settings')) {
            return;
        }

        Schema::table('organization_settings', function (Blueprint $table) {
            $columns = [
                'contact_label_singular',
                'contact_label_plural',
                'plan_label',
                'primary_color',
                'logo_path',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('organization_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
