<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable pgvector extension for PostgreSQL
        // This is required for storing and querying vector embeddings
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop the extension on rollback as other tables may depend on it
        // DB::statement('DROP EXTENSION IF EXISTS vector');
    }
};
