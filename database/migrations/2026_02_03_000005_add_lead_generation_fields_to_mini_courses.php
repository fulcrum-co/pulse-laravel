<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mini_courses', function (Blueprint $table) {
            // Lead generation visibility (extends existing is_public)
            $table->string('visibility')->default('private')->after('is_public'); // public, gated, private
            $table->boolean('requires_email')->default(false)->after('visibility'); // For gated content
            $table->boolean('is_embeddable')->default(false)->after('requires_email'); // Can be embedded on 3rd party sites
            $table->json('allowed_embed_domains')->nullable()->after('is_embeddable'); // Whitelist for embedding

            // SEO and discoverability
            $table->string('slug')->nullable()->after('title');
            $table->string('meta_title')->nullable()->after('slug');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->json('meta_keywords')->nullable()->after('meta_description');

            // Lead magnet features
            $table->boolean('badge_enabled')->default(false)->after('meta_keywords');
            $table->string('badge_name')->nullable()->after('badge_enabled');
            $table->string('badge_image_url')->nullable()->after('badge_name');
            $table->boolean('certificate_enabled')->default(false)->after('badge_image_url');
            $table->string('certificate_template')->nullable()->after('certificate_enabled');

            // Tracking
            $table->unsignedInteger('public_view_count')->default(0)->after('certificate_template');
            $table->unsignedInteger('lead_capture_count')->default(0)->after('public_view_count');

            $table->index(['visibility']);
            $table->index(['slug']);
            $table->unique(['org_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::table('mini_courses', function (Blueprint $table) {
            $table->dropIndex(['visibility']);
            $table->dropIndex(['slug']);
            $table->dropUnique(['org_id', 'slug']);

            $table->dropColumn([
                'visibility',
                'requires_email',
                'is_embeddable',
                'allowed_embed_domains',
                'slug',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'badge_enabled',
                'badge_name',
                'badge_image_url',
                'certificate_enabled',
                'certificate_template',
                'public_view_count',
                'lead_capture_count',
            ]);
        });
    }
};
