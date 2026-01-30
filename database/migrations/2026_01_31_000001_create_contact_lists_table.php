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
        Schema::create('contact_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained('organizations')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('list_type')->default('student'); // student, teacher, mixed
            $table->json('filter_criteria')->nullable();
            $table->boolean('is_dynamic')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['org_id', 'list_type']);
            $table->index(['org_id', 'is_dynamic']);
        });

        Schema::create('contact_list_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_list_id')->constrained()->cascadeOnDelete();
            $table->morphs('contact'); // contact_type, contact_id
            $table->timestamp('added_at')->useCurrent();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unique(['contact_list_id', 'contact_type', 'contact_id'], 'contact_list_member_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_list_members');
        Schema::dropIfExists('contact_lists');
    }
};
