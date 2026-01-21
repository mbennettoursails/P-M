<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Proposal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'title', 'title_en', 'description', 'description_en',
        'decision_type', 'current_stage', 'quorum_percentage', 'pass_threshold',
        'allow_anonymous_voting', 'show_results_during_voting', 'allowed_roles',
        'is_invite_only', 'feedback_deadline', 'voting_deadline', 'closed_at',
        'outcome', 'outcome_summary', 'outcome_summary_en', 'author_id',
    ];

    protected $casts = [
        'allowed_roles' => 'array',
        'allow_anonymous_voting' => 'boolean',
        'show_results_during_voting' => 'boolean',
        'is_invite_only' => 'boolean',
        'feedback_deadline' => 'datetime',
        'voting_deadline' => 'datetime',
        'closed_at' => 'datetime',
    ];

    const DECISION_TYPES = [
        'democratic' => [
            'name' => 'Democratic (Majority)',
            'name_ja' => '民主的（多数決）',
            'votes' => ['yes', 'no', 'abstain'],
            'votes_ja' => ['賛成', '反対', '棄権'],
            'vote_colors' => ['yes' => 'green', 'no' => 'red', 'abstain' => 'gray'],
            'vote_icons' => ['yes' => 'check-circle', 'no' => 'x-circle', 'abstain' => 'minus-circle'],
            'description' => 'Simple majority wins. Quick operational decisions.',
            'description_ja' => '過半数で可決。迅速な運営上の決定に適しています。',
        ],
        'consensus' => [
            'name' => 'Consensus',
            'name_ja' => 'コンセンサス（全員合意）',
            'votes' => ['agree', 'disagree', 'stand_aside', 'block'],
            'votes_ja' => ['賛成', '反対', '傍観', 'ブロック'],
            'vote_colors' => ['agree' => 'green', 'disagree' => 'red', 'stand_aside' => 'yellow', 'block' => 'red'],
            'vote_icons' => ['agree' => 'check-circle', 'disagree' => 'x-circle', 'stand_aside' => 'pause-circle', 'block' => 'hand-raised'],
            'description' => 'All must agree or stand aside. For major policy changes.',
            'description_ja' => '全員が同意または傍観。重要な方針変更に適しています。',
        ],
        'consent' => [
            'name' => 'Consent',
            'name_ja' => '同意（異議なし）',
            'votes' => ['no_objection', 'concern', 'object'],
            'votes_ja' => ['異議なし', '懸念あり', '異議あり'],
            'vote_colors' => ['no_objection' => 'green', 'concern' => 'yellow', 'object' => 'red'],
            'vote_icons' => ['no_objection' => 'check-circle', 'concern' => 'exclamation-triangle', 'object' => 'x-circle'],
            'description' => 'No meaningful objections. "Safe to try" decisions.',
            'description_ja' => '重大な異議がなければ可決。「試してみる価値あり」の決定に適しています。',
        ],
    ];

    const STAGES = [
        'draft' => ['name' => 'Draft', 'name_ja' => '下書き', 'order' => 1, 'color' => 'gray', 'icon' => 'pencil'],
        'feedback' => ['name' => 'Feedback', 'name_ja' => 'フィードバック', 'order' => 2, 'color' => 'blue', 'icon' => 'chat-bubble-left-right'],
        'refinement' => ['name' => 'Refinement', 'name_ja' => '修正', 'order' => 3, 'color' => 'purple', 'icon' => 'pencil-square'],
        'voting' => ['name' => 'Voting', 'name_ja' => '投票中', 'order' => 4, 'color' => 'amber', 'icon' => 'hand-raised'],
        'closed' => ['name' => 'Closed', 'name_ja' => '終了', 'order' => 5, 'color' => 'green', 'icon' => 'check-circle'],
        'archived' => ['name' => 'Archived', 'name_ja' => 'アーカイブ', 'order' => 6, 'color' => 'slate', 'icon' => 'archive-box'],
    ];

    const OUTCOMES = [
        'passed' => ['name' => 'Passed', 'name_ja' => '可決', 'color' => 'green', 'icon' => 'check-circle'],
        'rejected' => ['name' => 'Rejected', 'name_ja' => '否決', 'color' => 'red', 'icon' => 'x-circle'],
        'no_quorum' => ['name' => 'No Quorum', 'name_ja' => '定足数未達', 'color' => 'yellow', 'icon' => 'exclamation-triangle'],
        'blocked' => ['name' => 'Blocked', 'name_ja' => 'ブロック', 'color' => 'red', 'icon' => 'hand-raised'],
        'withdrawn' => ['name' => 'Withdrawn', 'name_ja' => '撤回', 'color' => 'gray', 'icon' => 'arrow-uturn-left'],
    ];

    const VALID_TRANSITIONS = [
        'draft' => ['feedback', 'withdrawn'],
        'feedback' => ['refinement', 'voting', 'withdrawn'],
        'refinement' => ['feedback', 'voting', 'withdrawn'],
        'voting' => ['closed'],
        'closed' => ['archived'],
        'archived' => [],
    ];

    // Relationships
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'author_id'); }
    public function stages(): HasMany { return $this->hasMany(ProposalStage::class)->orderBy('started_at'); }
    public function currentStageRecord(): HasOne { return $this->hasOne(ProposalStage::class)->where('is_active', true); }
    public function participants(): BelongsToMany {
        return $this->belongsToMany(User::class, 'proposal_participants')
            ->withPivot(['can_vote', 'can_comment', 'invited_at', 'viewed_at', 'responded_at'])
            ->withTimestamps();
    }
    public function votes(): HasMany { return $this->hasMany(Vote::class); }
    public function documents(): HasMany { return $this->hasMany(ProposalDocument::class)->orderBy('sort_order'); }
    public function comments(): HasMany { return $this->hasMany(Comment::class); }
    public function rootComments(): HasMany { return $this->hasMany(Comment::class)->whereNull('parent_id')->orderBy('created_at'); }
    public function notifications(): HasMany { return $this->hasMany(ProposalNotification::class); }

    // Scopes
    public function scopeActive($query) { return $query->whereNotIn('current_stage', ['closed', 'archived']); }
    public function scopeVotingOpen($query) { return $query->where('current_stage', 'voting'); }
    public function scopeByDecisionType($query, string $type) { return $query->where('decision_type', $type); }
    public function scopeByStage($query, string $stage) { return $query->where('current_stage', $stage); }
    
    public function scopeForUser($query, User $user) {
        return $query->where(function ($q) use ($user) {
            $q->where('author_id', $user->id)
              ->orWhereHas('participants', fn($p) => $p->where('user_id', $user->id))
              ->orWhere(function ($roleQuery) use ($user) {
                  $roleQuery->where('is_invite_only', false)
                      ->whereNotIn('current_stage', ['draft'])
                      ->where(function ($r) use ($user) {
                          $r->whereNull('allowed_roles')
                            ->orWhereJsonContains('allowed_roles', $user->role);
                      });
              });
        });
    }

    public function scopeNeedsVoteFrom($query, User $user) {
        return $query->where('current_stage', 'voting')
            ->whereHas('participants', fn($q) => $q->where('user_id', $user->id)->where('can_vote', true))
            ->whereDoesntHave('votes', fn($q) => $q->where('user_id', $user->id));
    }

    // Accessors
    public function getDecisionTypeConfigAttribute(): array { return self::DECISION_TYPES[$this->decision_type] ?? []; }
    public function getVoteOptionsAttribute(): array { return $this->decision_type_config['votes'] ?? []; }
    public function getVoteOptionsJaAttribute(): array { return $this->decision_type_config['votes_ja'] ?? []; }
    public function getVoteColorsAttribute(): array { return $this->decision_type_config['vote_colors'] ?? []; }
    public function getVoteIconsAttribute(): array { return $this->decision_type_config['vote_icons'] ?? []; }
    public function getStageConfigAttribute(): array { return self::STAGES[$this->current_stage] ?? []; }
    public function getOutcomeConfigAttribute(): ?array { return $this->outcome ? (self::OUTCOMES[$this->outcome] ?? null) : null; }
    
    public function getLocalizedTitleAttribute(): string {
        return (app()->getLocale() === 'en' && $this->title_en) ? $this->title_en : $this->title;
    }
    
    public function getLocalizedDescriptionAttribute(): string {
        return (app()->getLocale() === 'en' && $this->description_en) ? $this->description_en : $this->description;
    }
    
    public function getIsVotingActiveAttribute(): bool {
        return $this->current_stage === 'voting' && ($this->voting_deadline === null || $this->voting_deadline->isFuture());
    }
    
    public function getParticipantCountAttribute(): int { return $this->participants()->count(); }
    public function getVoterCountAttribute(): int { return $this->participants()->wherePivot('can_vote', true)->count(); }
    public function getVoteCountAttribute(): int { return $this->votes()->count(); }
    public function getCommentCountAttribute(): int { return $this->comments()->count(); }
    
    public function getQuorumReachedAttribute(): bool {
        if ($this->voter_count === 0) return false;
        return (($this->vote_count / $this->voter_count) * 100) >= $this->quorum_percentage;
    }
    
    public function getVotePercentageAttribute(): float {
        if ($this->voter_count === 0) return 0;
        return round(($this->vote_count / $this->voter_count) * 100, 1);
    }

    public function getRouteKeyName(): string { return 'uuid'; }

    // Methods
    public function canUserVote(User $user): bool {
        if ($this->current_stage !== 'voting' || !$this->is_voting_active) return false;
        $participant = $this->participants()->where('user_id', $user->id)->first();
        return $participant && $participant->pivot->can_vote;
    }

    public function canUserComment(User $user): bool {
        if (in_array($this->current_stage, ['draft', 'closed', 'archived'])) return false;
        $participant = $this->participants()->where('user_id', $user->id)->first();
        return $participant && $participant->pivot->can_comment;
    }

    public function canUserEdit(User $user): bool {
        return $this->author_id === $user->id && in_array($this->current_stage, ['draft', 'feedback', 'refinement']);
    }

    public function canUserAdvanceStage(User $user): bool { return $this->author_id === $user->id; }
    
    public function canTransitionTo(string $newStage): bool {
        return in_array($newStage, self::VALID_TRANSITIONS[$this->current_stage] ?? []);
    }

    public function getUserVote(User $user): ?Vote { return $this->votes()->where('user_id', $user->id)->first(); }
    public function hasUserVoted(User $user): bool { return $this->votes()->where('user_id', $user->id)->exists(); }
    public function isUserParticipant(User $user): bool { return $this->participants()->where('user_id', $user->id)->exists(); }

    public function getVoteDistribution(): array {
        $votes = $this->votes()->selectRaw('vote_value, COUNT(*) as count')->groupBy('vote_value')->pluck('count', 'vote_value')->toArray();
        $distribution = [];
        foreach ($this->vote_options as $option) { $distribution[$option] = $votes[$option] ?? 0; }
        return $distribution;
    }

    public function getVoteDistributionWithPercentages(): array {
        $distribution = $this->getVoteDistribution();
        $total = array_sum($distribution);
        $result = [];
        foreach ($distribution as $option => $count) {
            $result[$option] = ['count' => $count, 'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0];
        }
        return $result;
    }

    public function calculateOutcome(): ?string {
        if (!$this->quorum_reached) return 'no_quorum';
        $distribution = $this->getVoteDistribution();
        $totalVotes = array_sum($distribution);
        if ($totalVotes === 0) return 'no_quorum';

        switch ($this->decision_type) {
            case 'democratic':
                return (($distribution['yes'] / $totalVotes) * 100) >= $this->pass_threshold ? 'passed' : 'rejected';
            case 'consensus':
                if (($distribution['block'] ?? 0) > 0) return 'blocked';
                if (($distribution['disagree'] ?? 0) > 0) return 'rejected';
                return 'passed';
            case 'consent':
                return (($distribution['object'] ?? 0) > 0) ? 'rejected' : 'passed';
            default:
                return null;
        }
    }

    public function transitionTo(string $newStage, ?User $transitionedBy = null, ?string $notes = null): bool {
        if (!$this->canTransitionTo($newStage)) return false;
        
        if ($newStage === 'withdrawn') {
            $this->outcome = 'withdrawn';
            $newStage = 'closed';
        }

        $this->stages()->where('is_active', true)->update(['is_active' => false, 'ended_at' => now()]);
        $this->stages()->create(['stage_type' => $newStage, 'started_at' => now(), 'is_active' => true, 'notes' => $notes, 'transitioned_by' => $transitionedBy?->id]);
        
        $this->current_stage = $newStage;
        if ($newStage === 'closed' && !$this->outcome) {
            $this->closed_at = now();
            $this->outcome = $this->calculateOutcome();
        }
        return $this->save();
    }

    public function markViewedBy(User $user): void {
        $this->participants()->updateExistingPivot($user->id, ['viewed_at' => now()]);
    }

    public function markRespondedBy(User $user): void {
        $participant = $this->participants()->where('user_id', $user->id)->first();
        if ($participant && !$participant->pivot->responded_at) {
            $this->participants()->updateExistingPivot($user->id, ['responded_at' => now()]);
        }
    }

    protected static function boot() {
        parent::boot();
        static::creating(function ($proposal) { $proposal->uuid = $proposal->uuid ?? (string) Str::uuid(); });
        static::created(function ($proposal) {
            $proposal->stages()->create(['stage_type' => 'draft', 'started_at' => now(), 'is_active' => true]);
            $proposal->participants()->attach($proposal->author_id, ['can_vote' => true, 'can_comment' => true, 'invited_at' => now()]);
        });
    }
}
