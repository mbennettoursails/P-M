<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProposalComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'proposal_id',
        'user_id',
        'parent_id',
        'content',
        'edited_at',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    // Edit window in minutes
    const EDIT_WINDOW_MINUTES = 15;
    
    // Maximum nesting depth
    const MAX_DEPTH = 3;

    // ─────────────────────────────────────────────────────────────
    // BOOT
    // ─────────────────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($comment) {
            if (empty($comment->uuid)) {
                $comment->uuid = (string) Str::uuid();
            }
        });
    }

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProposalComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ProposalComment::class, 'parent_id')->orderBy('created_at', 'asc');
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────

    public function getDepthAttribute(): int
    {
        $depth = 0;
        $comment = $this;

        while ($comment->parent_id !== null) {
            $depth++;
            $comment = $comment->parent;
            
            // Safety limit
            if ($depth >= self::MAX_DEPTH) {
                break;
            }
        }

        return $depth;
    }

    public function getCanBeEditedAttribute(): bool
    {
        // Cannot edit deleted comments
        if ($this->trashed()) {
            return false;
        }

        // Check if within edit window
        return $this->created_at->addMinutes(self::EDIT_WINDOW_MINUTES)->isFuture();
    }

    public function getIsEditedAttribute(): bool
    {
        return $this->edited_at !== null;
    }

    public function getCanHaveRepliesAttribute(): bool
    {
        return $this->depth < self::MAX_DEPTH - 1;
    }

    public function getDisplayContentAttribute(): string
    {
        if ($this->trashed()) {
            return '[deleted]';
        }

        return $this->content;
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans(['short' => true]);
    }

    // ─────────────────────────────────────────────────────────────
    // METHODS
    // ─────────────────────────────────────────────────────────────

    public function canUserEdit(User $user): bool
    {
        // Must be the author
        if ($user->id !== $this->user_id) {
            return false;
        }

        return $this->can_be_edited;
    }

    public function canUserDelete(User $user): bool
    {
        // Author can delete
        if ($user->id === $this->user_id) {
            return true;
        }

        // Proposal author can delete any comment
        if ($user->id === $this->proposal->author_id) {
            return true;
        }

        return false;
    }

    public function updateContent(string $newContent): bool
    {
        if (!$this->can_be_edited) {
            return false;
        }

        $this->content = $newContent;
        $this->edited_at = now();

        return $this->save();
    }

    // ─────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }
}
