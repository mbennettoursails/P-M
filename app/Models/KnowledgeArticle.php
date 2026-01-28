<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class KnowledgeArticle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'title_en',
        'slug',
        'excerpt',
        'content',
        'content_json',
        'category_id',
        'tags',
        'type',
        'external_url',
        'external_source',
        'status',
        'published_at',
        'sort_order',
        'is_featured',
        'is_pinned',
        'author_id',
        'last_editor_id',
        'last_edited_at',
        'view_count',
        'helpful_count',
        'not_helpful_count',
        'search_content',
    ];

    protected $casts = [
        'content_json' => 'array',
        'tags' => 'array',
        'published_at' => 'datetime',
        'last_edited_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_pinned' => 'boolean',
        'view_count' => 'integer',
        'helpful_count' => 'integer',
        'not_helpful_count' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Article types with Japanese labels
     */
    public const TYPES = [
        'article' => '記事',
        'faq' => 'よくある質問',
        'guide' => 'ガイド',
        'recipe' => 'レシピ',
        'manual' => 'マニュアル',
        'external_link' => '外部リンク',
    ];

    /**
     * Status labels
     */
    public const STATUSES = [
        'draft' => '下書き',
        'published' => '公開中',
        'archived' => 'アーカイブ',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->uuid)) {
                $article->uuid = (string) Str::uuid();
            }
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title) . '-' . Str::random(6);
            }
        });

        static::saving(function ($article) {
            // Update search content for full-text search
            $article->search_content = strip_tags($article->title . ' ' . $article->excerpt . ' ' . $article->content);
            
            // Set last_edited_at
            if ($article->isDirty(['title', 'content', 'excerpt'])) {
                $article->last_edited_at = now();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(KnowledgeCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function lastEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_editor_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(KnowledgeAttachment::class, 'article_id')->orderBy('sort_order');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(KnowledgeArticleFeedback::class, 'article_id');
    }

    public function relatedArticles(): BelongsToMany
    {
        return $this->belongsToMany(
            KnowledgeArticle::class,
            'knowledge_article_relations',
            'article_id',
            'related_article_id'
        )->withPivot('sort_order')->orderByPivot('sort_order');
    }

    // ─────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tags', $tag);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        $search = '%' . $search . '%';
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'ilike', $search)
              ->orWhere('excerpt', 'ilike', $search)
              ->orWhere('search_content', 'ilike', $search)
              ->orWhereJsonContains('tags', trim($search, '%'));
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_pinned')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at');
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────

    public function getDisplayTitleAttribute(): string
    {
        if (app()->getLocale() === 'en' && $this->title_en) {
            return $this->title_en;
        }
        return $this->title;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getPublishedDateAttribute(): ?string
    {
        return $this->published_at?->format('Y年n月j日');
    }

    public function getReadingTimeAttribute(): int
    {
        $charCount = mb_strlen(strip_tags($this->content));
        return max(1, (int) ceil($charCount / 400));
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published' 
            && $this->published_at 
            && $this->published_at->isPast();
    }

    public function getHelpfulPercentageAttribute(): ?int
    {
        $total = $this->helpful_count + $this->not_helpful_count;
        if ($total === 0) {
            return null;
        }
        return (int) round(($this->helpful_count / $total) * 100);
    }

    public function getIsExternalAttribute(): bool
    {
        return $this->type === 'external_link' && !empty($this->external_url);
    }

    // ─────────────────────────────────────────────────────────────
    // METHODS
    // ─────────────────────────────────────────────────────────────

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?? now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update(['status' => 'draft']);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function markHelpful(User $user): void
    {
        $feedback = $this->feedback()->where('user_id', $user->id)->first();
        
        if ($feedback) {
            if (!$feedback->is_helpful) {
                $this->decrement('not_helpful_count');
                $this->increment('helpful_count');
                $feedback->update(['is_helpful' => true]);
            }
        } else {
            $this->feedback()->create([
                'user_id' => $user->id,
                'is_helpful' => true,
            ]);
            $this->increment('helpful_count');
        }
    }

    public function markNotHelpful(User $user): void
    {
        $feedback = $this->feedback()->where('user_id', $user->id)->first();
        
        if ($feedback) {
            if ($feedback->is_helpful) {
                $this->decrement('helpful_count');
                $this->increment('not_helpful_count');
                $feedback->update(['is_helpful' => false]);
            }
        } else {
            $this->feedback()->create([
                'user_id' => $user->id,
                'is_helpful' => false,
            ]);
            $this->increment('not_helpful_count');
        }
    }

    public function getUserFeedback(User $user): ?bool
    {
        $feedback = $this->feedback()->where('user_id', $user->id)->first();
        return $feedback?->is_helpful;
    }
}
