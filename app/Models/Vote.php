<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $fillable = ['proposal_id', 'user_id', 'vote_value', 'reason', 'is_anonymous', 'voted_at', 'changed_at'];

    protected $casts = ['is_anonymous' => 'boolean', 'voted_at' => 'datetime', 'changed_at' => 'datetime'];

    public function proposal(): BelongsTo { return $this->belongsTo(Proposal::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function scopeAnonymous($query) { return $query->where('is_anonymous', true); }
    public function scopePublic($query) { return $query->where('is_anonymous', false); }
    public function scopeByValue($query, string $value) { return $query->where('vote_value', $value); }

    public function getVoterNameAttribute(): string {
        if ($this->is_anonymous) return app()->getLocale() === 'ja' ? '匿名' : 'Anonymous';
        return $this->user->name ?? (app()->getLocale() === 'ja' ? '不明' : 'Unknown');
    }

    public function getVoteValueLabelAttribute(): string {
        $config = $this->proposal->decision_type_config;
        $index = array_search($this->vote_value, $config['votes']);
        if ($index !== false && app()->getLocale() === 'ja' && isset($config['votes_ja'][$index])) {
            return $config['votes_ja'][$index];
        }
        return ucfirst(str_replace('_', ' ', $this->vote_value));
    }

    public function getVoteColorAttribute(): string { return $this->proposal->vote_colors[$this->vote_value] ?? 'gray'; }
    public function getVoteIconAttribute(): string { return $this->proposal->vote_icons[$this->vote_value] ?? 'question-mark-circle'; }
    public function getHasReasonAttribute(): bool { return !empty($this->reason); }
    public function getWasChangedAttribute(): bool { return $this->changed_at !== null; }

    public function updateVote(string $newValue, ?string $reason = null): bool {
        $this->vote_value = $newValue;
        $this->reason = $reason;
        $this->changed_at = now();
        return $this->save();
    }

    public function canBeChangedBy(User $user): bool {
        return $this->user_id === $user->id && $this->proposal->is_voting_active;
    }
}
