<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class News extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'excerpt',
        'content',
        'content_json',
        'category',
        'featured_image',
        'featured_image_alt',
        'status',
        'published_at',
        'archived_at',
        'is_pinned',
        'is_featured',
        'visible_to_roles',
        'author_id',
        'view_count',
    ];

    protected $casts = [
        'content_json' => 'array',
        'visible_to_roles' => 'array',
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
        'is_pinned' => 'boolean',
        'is_featured' => 'boolean',
        'view_count' => 'integer',
    ];

    /**
     * Category options with Japanese labels
     */
    public const CATEGORIES = [
        'general' => '一般',
        'announcement' => 'お知らせ',
        'event' => 'イベント',
        'recipe' => 'レシピ',
        'tips' => '暮らしのヒント',
        'urgent' => '緊急',
    ];

    /**
     * Status options with Japanese labels
     */
    public const STATUSES = [
        'draft' => '下書き',
        'published' => '公開中',
        'archived' => 'アーカイブ',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($news) {
            if (empty($news->uuid)) {
                $news->uuid = (string) Str::uuid();
            }
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->title) . '-' . Str::random(6);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    /**
     * Author of the news article
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Users who have read this article
     */
    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'news_reads')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    // ─────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────

    /**
     * Only published articles
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Only draft articles
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Only archived articles
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    /**
     * Filter by category
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Pinned articles first
     */
    public function scopePinnedFirst(Builder $query): Builder
    {
        return $query->orderByDesc('is_pinned');
    }

    /**
     * Featured articles
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Articles visible to a specific user based on their roles
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $userRoles = $user->roles->pluck('name')->toArray();
        
        return $query->where(function ($q) use ($userRoles) {
            // Articles with no role restriction
            $q->whereNull('visible_to_roles');
            
            // Or articles where user has one of the required roles
            foreach ($userRoles as $role) {
                $q->orWhereJsonContains('visible_to_roles', $role);
            }
        });
    }

    /**
     * Unread articles for a specific user
     */
    public function scopeUnreadFor(Builder $query, User $user): Builder
    {
        return $query->whereDoesntHave('readers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    /**
     * Search by title or content
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'ilike', "%{$search}%")
              ->orWhere('excerpt', 'ilike', "%{$search}%")
              ->orWhere('content', 'ilike', "%{$search}%");
        });
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────

    /**
     * Get category label in Japanese
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Get status label in Japanese
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get formatted published date
     */
    public function getPublishedDateAttribute(): ?string
    {
        return $this->published_at?->format('Y年n月j日');
    }

    /**
     * Get reading time estimate (Japanese: ~400 chars/min)
     */
    public function getReadingTimeAttribute(): int
    {
        $charCount = mb_strlen(strip_tags($this->content));
        return max(1, (int) ceil($charCount / 400));
    }

    /**
     * Check if article is published
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published' 
            && $this->published_at 
            && $this->published_at->isPast();
    }

    // ─────────────────────────────────────────────────────────────
    // METHODS
    // ─────────────────────────────────────────────────────────────

    /**
     * Mark as read by a user
     */
    public function markAsReadBy(User $user): void
    {
        if (!$this->readers()->where('user_id', $user->id)->exists()) {
            $this->readers()->attach($user->id, ['read_at' => now()]);
        }
    }

    /**
     * Check if read by a user
     */
    public function isReadBy(User $user): bool
    {
        return $this->readers()->where('user_id', $user->id)->exists();
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Publish the article
     */
    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?? now(),
        ]);
    }

    /**
     * Unpublish (return to draft)
     */
    public function unpublish(): void
    {
        $this->update([
            'status' => 'draft',
        ]);
    }

    /**
     * Archive the article
     */
    public function archive(): void
    {
        $this->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }

    /**
     * Toggle pinned status
     */
    public function togglePinned(): void
    {
        $this->update(['is_pinned' => !$this->is_pinned]);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(): void
    {
        $this->update(['is_featured' => !$this->is_featured]);
    }
}
