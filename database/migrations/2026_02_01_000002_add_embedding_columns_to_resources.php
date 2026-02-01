<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables to add embedding columns to.
     */
    protected array $tables = [
        'resources',
        'mini_courses',
        'content_blocks',
        'providers',
        'programs',
    ];

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

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                // Add metadata columns (works on all databases)
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    if (! Schema::hasColumn($table, 'embedding_generated_at')) {
                        $blueprint->timestamp('embedding_generated_at')->nullable();
                    }
                    if (! Schema::hasColumn($table, 'embedding_model')) {
                        $blueprint->string('embedding_model', 50)->nullable();
                    }
                });

                // pgvector-specific columns and indexes (PostgreSQL only)
                if ($isPostgres) {
                    // Add embedding column with vector type
                    DB::statement("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS embedding vector({$this->dimensions})");

                    // Create index for approximate nearest neighbor search
                    DB::statement("CREATE INDEX IF NOT EXISTS {$table}_embedding_idx ON {$table} USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isPostgres = DB::connection()->getDriverName() === 'pgsql';

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                // Drop pgvector columns/indexes (PostgreSQL only)
                if ($isPostgres) {
                    DB::statement("DROP INDEX IF EXISTS {$table}_embedding_idx");
                    DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS embedding");
                }

                // Drop metadata columns
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn(['embedding_generated_at', 'embedding_model']);
                });
            }
        }
    }
};
