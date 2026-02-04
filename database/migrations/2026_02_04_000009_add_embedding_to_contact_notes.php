<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Embedding dimensions (OpenAI text-embedding-3-small uses 1536).
     */
    protected int $dimensions = 1536;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isPostgres = DB::connection()->getDriverName() === 'pgsql';

        Schema::table('contact_notes', function (Blueprint $table) {
            if (! Schema::hasColumn('contact_notes', 'embedding_generated_at')) {
                $table->timestamp('embedding_generated_at')->nullable();
            }
            if (! Schema::hasColumn('contact_notes', 'embedding_model')) {
                $table->string('embedding_model', 50)->nullable();
            }
            // Track when drift was last scored
            if (! Schema::hasColumn('contact_notes', 'drift_scored_at')) {
                $table->timestamp('drift_scored_at')->nullable();
            }
        });

        // pgvector-specific columns and indexes (PostgreSQL only)
        if ($isPostgres) {
            // Add embedding column with vector type
            DB::statement("ALTER TABLE contact_notes ADD COLUMN IF NOT EXISTS embedding vector({$this->dimensions})");

            // Create index for approximate nearest neighbor search
            DB::statement('CREATE INDEX IF NOT EXISTS contact_notes_embedding_idx ON contact_notes USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isPostgres = DB::connection()->getDriverName() === 'pgsql';

        // Drop pgvector columns/indexes (PostgreSQL only)
        if ($isPostgres) {
            DB::statement('DROP INDEX IF EXISTS contact_notes_embedding_idx');
            DB::statement('ALTER TABLE contact_notes DROP COLUMN IF EXISTS embedding');
        }

        Schema::table('contact_notes', function (Blueprint $table) {
            $table->dropColumn(['embedding_generated_at', 'embedding_model', 'drift_scored_at']);
        });
    }
};
