<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL: alter the enum/check constraint to include 'center'
        DB::statement("ALTER TABLE page_help_hints DROP CONSTRAINT IF EXISTS page_help_hints_position_check");
        DB::statement("ALTER TABLE page_help_hints ADD CONSTRAINT page_help_hints_position_check CHECK (position IN ('top', 'bottom', 'left', 'right', 'center'))");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE page_help_hints DROP CONSTRAINT IF EXISTS page_help_hints_position_check");
        DB::statement("ALTER TABLE page_help_hints ADD CONSTRAINT page_help_hints_position_check CHECK (position IN ('top', 'bottom', 'left', 'right'))");
    }
};
