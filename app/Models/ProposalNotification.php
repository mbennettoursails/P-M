<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProposalNotification extends Model
{
    protected $fillable = ['uuid', 'user_id', 'proposal_id', 'type', 'title', 'title_en', 'message', 'message_en', 'action_url', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    const TYPES = [
        'proposal_created' => ['icon' => 'plus-circle', 'color' => 'blue', 'name' => 'Proposal Created', 'name_ja' => '提案が作成されました'],
        'proposal_stage_changed' => ['icon' => 'arrow-path', 'color' => 'purple', 'name' => 'Stage Changed', 'name_ja' => 'ステージが変更されました'],
        'vote_reminder' => ['icon' => 'bell', 'color' => 'yellow', 'name' => 'Vote Reminder', 'name_ja' => '投票リマインダー'],
        'deadline_approaching' => ['icon' => 'clock', 'color' => 'orange', 'name' => 'Deadline Approaching', 'name_ja' => '期限が近づいています'],
        'new_comment' => ['icon' => 'chat-bubble-left', 'color' => 'green', 'name' => 'New Comment', 'name_ja' => '新しいコメント'],
        'comment_reply' => ['icon' => 'chat-bubble-left-right', 'color' => 'teal', 'name' => 'Comment Reply', 'name_ja' => 'コメントへの返信'],
        'proposal_outcome' => ['icon' => 'check-circle', 'color' => 'emerald', 'name' => 'Voting Result', 'name_ja' => '投票結果'],
        'invited_to_participate' => ['icon' => 'user-plus', 'color' => 'indigo', 'name' => 'Invitation', 'name_ja' => '参加依頼'],
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function proposal(): BelongsTo { return $this->belongsTo(Proposal::class); }

    public function scopeUnread($query) { return $query->whereNull('read_at'); }
    public function scopeRead($query) { return $query->whereNotNull('read_at'); }
    public function scopeRecent($query, int $days = 7) { return $query->where('created_at', '>=', now()->subDays($days)); }
    public function scopeOfType($query, string $type) { return $query->where('type', $type); }
    public function scopeForUser($query, User $user) { return $query->where('user_id', $user->id); }

    public function getIsReadAttribute(): bool { return $this->read_at !== null; }
    public function getIsUnreadAttribute(): bool { return $this->read_at === null; }
    public function getTypeConfigAttribute(): array { return self::TYPES[$this->type] ?? ['icon' => 'bell', 'color' => 'gray', 'name' => 'Notification', 'name_ja' => '通知']; }
    public function getIconAttribute(): string { return $this->type_config['icon']; }
    public function getIconColorAttribute(): string { return $this->type_config['color']; }
    public function getLocalizedTitleAttribute(): string { return (app()->getLocale() === 'en' && $this->title_en) ? $this->title_en : $this->title; }
    public function getLocalizedMessageAttribute(): string { return (app()->getLocale() === 'en' && $this->message_en) ? $this->message_en : $this->message; }
    public function getTimeAgoAttribute(): string { return $this->created_at->diffForHumans(); }
    public function getRouteKeyName(): string { return 'uuid'; }

    public function markAsRead(): bool {
        if ($this->read_at) return true;
        $this->read_at = now();
        return $this->save();
    }

    public function markAsUnread(): bool {
        $this->read_at = null;
        return $this->save();
    }

    public static function createForUser(User $user, string $type, array $data): self {
        return self::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'proposal_id' => $data['proposal_id'] ?? null,
            'type' => $type,
            'title' => $data['title'],
            'title_en' => $data['title_en'] ?? null,
            'message' => $data['message'],
            'message_en' => $data['message_en'] ?? null,
            'action_url' => $data['action_url'] ?? null,
        ]);
    }

    public static function getUnreadCountForUser(User $user): int {
        return self::where('user_id', $user->id)->unread()->count();
    }

    public static function markAllAsReadForUser(User $user): int {
        return self::where('user_id', $user->id)->unread()->update(['read_at' => now()]);
    }

    protected static function boot() {
        parent::boot();
        static::creating(function ($notification) { $notification->uuid = $notification->uuid ?? (string) Str::uuid(); });
    }
}
