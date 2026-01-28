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
        // Knowledge Base Categories
        Schema::create('knowledge_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name'); // Japanese name
            $table->string('name_en')->nullable(); // English name
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // Icon class or SVG name
            $table->string('color')->default('gray'); // Tailwind color name
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('knowledge_categories')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('parent_id');
            $table->index('sort_order');
        });

        // Knowledge Base Articles
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            // Content
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable(); // Short description for search results
            $table->longText('content'); // HTML content from Tiptap
            $table->json('content_json')->nullable(); // Tiptap JSON for editing
            
            // Organization
            $table->foreignId('category_id')->constrained('knowledge_categories')->onDelete('cascade');
            $table->json('tags')->nullable(); // Array of tags for filtering
            
            // Type: article, faq, guide, recipe, manual, external_link
            $table->string('type')->default('article');
            
            // For external links
            $table->string('external_url')->nullable();
            $table->string('external_source')->nullable(); // Source name (e.g., "生活クラブ本部")
            
            // Publishing
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            
            // Ordering & Featuring
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);
            
            // Author
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('last_editor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('last_edited_at')->nullable();
            
            // Analytics
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('helpful_count')->default(0);
            $table->unsignedInteger('not_helpful_count')->default(0);
            
            // SEO / Search
            $table->text('search_content')->nullable(); // Plain text for full-text search
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'published_at']);
            $table->index('category_id');
            $table->index('type');
            $table->index('is_featured');
            $table->index('sort_order');
            
            // Full-text search index (PostgreSQL)
            // Note: Run this manually if needed: CREATE INDEX knowledge_articles_search_idx ON knowledge_articles USING gin(to_tsvector('japanese', search_content));
        });

        // Knowledge Article Attachments (Files)
        Schema::create('knowledge_attachments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('article_id')->constrained('knowledge_articles')->onDelete('cascade');
            
            // File info
            $table->string('filename'); // Original filename
            $table->string('path'); // Storage path
            $table->string('disk')->default('public'); // Storage disk
            $table->string('mime_type');
            $table->unsignedBigInteger('size'); // File size in bytes
            
            // Display
            $table->string('title')->nullable(); // Display title (can differ from filename)
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            
            // Type categorization
            $table->string('type')->default('document'); // document, image, video, spreadsheet, pdf
            
            // Download tracking
            $table->unsignedInteger('download_count')->default(0);
            
            $table->timestamps();
            
            $table->index('article_id');
            $table->index('type');
        });

        // External Database Links (for connecting to external resources)
        Schema::create('knowledge_external_sources', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name'); // Source name
            $table->string('name_en')->nullable();
            $table->text('description')->nullable();
            $table->string('base_url'); // Base URL for the external source
            $table->string('api_endpoint')->nullable(); // API endpoint if available
            $table->json('api_config')->nullable(); // API configuration (headers, auth type, etc.)
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Track user helpful/not helpful votes
        Schema::create('knowledge_article_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('knowledge_articles')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_helpful');
            $table->text('comment')->nullable(); // Optional feedback comment
            $table->timestamps();
            
            $table->unique(['article_id', 'user_id']);
        });

        // Related articles (many-to-many self-reference)
        Schema::create('knowledge_article_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('knowledge_articles')->onDelete('cascade');
            $table->foreignId('related_article_id')->constrained('knowledge_articles')->onDelete('cascade');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['article_id', 'related_article_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_article_relations');
        Schema::dropIfExists('knowledge_article_feedback');
        Schema::dropIfExists('knowledge_external_sources');
        Schema::dropIfExists('knowledge_attachments');
        Schema::dropIfExists('knowledge_articles');
        Schema::dropIfExists('knowledge_categories');
    }
};
