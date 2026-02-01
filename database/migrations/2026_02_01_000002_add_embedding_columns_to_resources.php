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
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    // Add embedding column using raw SQL for vector type
                    // Laravel's schema builder doesn't natively support pgvector
                });

                // Add embedding column with vector type
                DB::statement("ALTER TABLE {$table} ADD COLUMN IF NOT EXISTS embedding vector({$this->dimensions})");

                // Add metadata columns
                Schema::table($table, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'embedding_generated_at')) {
                        $table->timestamp('embedding_generated_at')->nullable();
                    }
                    if (!Schema::hasColumn($table->getTable(), 'embedding_model')) {
                        $table->string('embedding_model', 50)->nullable();
                    }
                });

                // Create index for approximate nearest neighbor search
                // Using IVFFlat index which is good for medium-sized datasets
                DB::statement("CREATE INDEX IF NOT EXISTS {$table}_embedding_idx ON {$table} USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                // Drop indexes first
                DB::statement("DROP INDEX IF EXISTS {$table}_embedding_idx");

                // Drop columns
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn(['embedding_generated_at', 'embedding_model']);
                });

                DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS embedding");
            }
        }
    }
};
