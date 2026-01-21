<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use SoftDeletes;

    protected $fillable = ['proposal_id', 'user_id', 'parent_id', 'content', 'stage_context', 'is_edited', 'edited_at'];

    protected $casts = ['is_edited' => 'boolean', 'edited_at' => 'datetime'];

    const EDIT_WINDOW_MINUTES = 15;
    const MAX_DEPTH = 5;

    public function proposal(): BelongsTo { return $this->belongsTo(Proposal::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function parent(): BelongsTo { return $this->belongsTo(Comment::class, 'parent_id'); }
    public function replies(): HasMany { return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at'); }
    public function allReplies(): HasMany { return $this->replies()->with('allReplies', 'user'); }

    public function scopeRootLevel($query) { return $query->whereNull('parent_id'); }
    public function scopeInStage($query, string $stage) { return $query->where('stage_context', $stage); }
    public function scopeRecent($query, int $hours = 24) { return $query->where('created_at', '>=', now()->subHours($hours)); }

    public function getIsRootAttribute(): bool { return $this->parent_id === null; }
    
    public function getDepthAttribute(): int {
        $depth = 0;
        $comment = $this;
        while ($comment->parent_id !== null && $depth < self::MAX_DEPTH) {
            $depth++;
            $comment = $comment->parent;
        }
        return $depth;
    }

    public function getCanReplyAttribute(): bool { return $this->depth < self::MAX_DEPTH; }
    public function getReplyCountAttribute(): int { return $this->replies()->count(); }
    public function getIsWithinEditWindowAttribute(): bool { return $this->created_at->diffInMinutes(now()) <= self::EDIT_WINDOW_MINUTES; }
    public function getStageConfigAttribute(): array { return Proposal::STAGES[$this->stage_context] ?? []; }
    public function getTimeAgoAttribute(): string { return $this->created_at->diffForHumans(); }
    public function getContentPreviewAttribute(): string { return \Illuminate\Support\Str::limit(strip_tags($this->content), 100); }

    public function edit(string $newContent): bool {
        $this->content = $newContent;
        $this->is_edited = true;
        $this->edited_at = now();
        return $this->save();
    }

    public function canBeEditedBy(User $user): bool {
        return $this->user_id === $user->id && $this->is_within_edit_window;
    }

    public function canBeDeletedBy(User $user): bool {
        if ($this->user_id === $user->id) return true;
        if ($this->proposal->author_id === $user->id) return true;
        if ($user->role === 'shokuin') return true;
        return false;
    }

    public function getRootComment(): Comment {
        $comment = $this;
        while ($comment->parent_id !== null) { $comment = $comment->parent; }
        return $comment;
    }
}
