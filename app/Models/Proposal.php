<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Proposal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'decision_type',
        'current_stage',
        'quorum_percentage',
        'pass_threshold',
        'allow_anonymous_voting',
        'show_results_during_voting',
        'allowed_roles',
        'is_invite_only',
        'feedback_deadline',
        'voting_deadline',
        'closed_at',
        'outcome',
        'outcome_summary',
        'author_id',
    ];

    protected $casts = [
        'allowed_roles' => 'array',
        'allow_anonymous_voting' => 'boolean',
        'show_results_during_voting' => 'boolean',
        'is_invite_only' => 'boolean',
        'feedback_deadline' => 'datetime',
        'voting_deadline' => 'datetime',
        'closed_at' => 'datetime',
        'quorum_percentage' => 'integer',
        'pass_threshold' => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────
    // CONSTANTS
    // ─────────────────────────────────────────────────────────────

    const DECISION_TYPES = [
        'democratic' => [
            'name' => 'Democratic (Majority)',
            'icon' => 'users',
            'color' => 'blue',
            'votes' => ['yes', 'no', 'abstain'],
            'vote_labels' => ['Yes', 'No', 'Abstain'],
            'vote_colors' => ['yes' => 'green', 'no' => 'red', 'abstain' => 'gray'],
            'vote_icons' => ['yes' => 'check-circle', 'no' => 'x-circle', 'abstain' => 'minus-circle'],
            'description' => 'Simple majority wins. Quick operational decisions.',
        ],
        'consensus' => [
            'name' => 'Consensus',
            'icon' => 'user-group',
            'color' => 'purple',
            'votes' => ['agree', 'disagree', 'stand_aside', 'block'],
            'vote_labels' => ['Agree', 'Disagree', 'Stand Aside', 'Block'],
            'vote_colors' => ['agree' => 'green', 'disagree' => 'red', 'stand_aside' => 'yellow', 'block' => 'red'],
            'vote_icons' => ['agree' => 'check-circle', 'disagree' => 'x-circle', 'stand_aside' => 'pause-circle', 'block' => 'hand-raised'],
            'description' => 'All must agree or stand aside. For major policy changes.',
        ],
        'consent' => [
            'name' => 'Consent',
            'icon' => 'shield-check',
            'color' => 'teal',
            'votes' => ['no_objection', 'concern', 'object'],
            'vote_labels' => ['No Objection', 'Concern', 'Object'],
            'vote_colors' => ['no_objection' => 'green', 'concern' => 'yellow', 'object' => 'red'],
            'vote_icons' => ['no_objection' => 'check-circle', 'concern' => 'exclamation-triangle', 'object' => 'x-circle'],
            'description' => 'No meaningful objections. "Safe to try" decisions.',
        ],
    ];

    const STAGES = [
        'draft' => ['name' => 'Draft', 'order' => 1, 'color' => 'gray', 'icon' => 'pencil'],
        'feedback' => ['name' => 'Feedback', 'order' => 2, 'color' => 'blue', 'icon' => 'chat-bubble-left-right'],
        'refinement' => ['name' => 'Refinement', 'order' => 3, 'color' => 'purple', 'icon' => 'pencil-square'],
        'voting' => ['name' => 'Voting', 'order' => 4, 'color' => 'amber', 'icon' => 'hand-raised'],
        'closed' => ['name' => 'Closed', 'order' => 5, 'color' => 'green', 'icon' => 'check-circle'],
        'archived' => ['name' => 'Archived', 'order' => 6, 'color' => 'slate', 'icon' => 'archive-box'],
    ];

    const OUTCOMES = [
        'passed' => ['name' => 'Passed', 'color' => 'green', 'icon' => 'check-circle'],
        'rejected' => ['name' => 'Rejected', 'color' => 'red', 'icon' => 'x-circle'],
        'no_quorum' => ['name' => 'No Quorum', 'color' => 'yellow', 'icon' => 'exclamation-triangle'],
        'blocked' => ['name' => 'Blocked', 'color' => 'red', 'icon' => 'hand-raised'],
        'withdrawn' => ['name' => 'Withdrawn', 'color' => 'gray', 'icon' => 'arrow-uturn-left'],
    ];

    const VALID_TRANSITIONS = [
        'draft' => ['feedback', 'withdrawn'],
        'feedback' => ['refinement', 'voting', 'withdrawn'],
        'refinement' => ['feedback', 'voting', 'withdrawn'],
        'voting' => ['closed'],
        'closed' => ['archived'],
        'archived' => [],
        'withdrawn' => [],
    ];

    // ─────────────────────────────────────────────────────────────
    // BOOT
    // ─────────────────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($proposal) {
            if (empty($proposal->uuid)) {
                $proposal->uuid = (string) Str::uuid();
            }
        });
    }

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProposalComment::class)->whereNull('parent_id')->orderBy('created_at', 'asc');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(ProposalComment::class)->orderBy('created_at', 'asc');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ProposalStage::class)->orderBy('started_at', 'asc');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProposalDocument::class)->orderBy('sort_order', 'asc');
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────

    public function getDecisionTypeConfigAttribute(): array
    {
        return self::DECISION_TYPES[$this->decision_type] ?? [];
    }

    public function getStageConfigAttribute(): array
    {
        return self::STAGES[$this->current_stage] ?? [];
    }

    public function getOutcomeConfigAttribute(): ?array
    {
        return $this->outcome ? (self::OUTCOMES[$this->outcome] ?? null) : null;
    }

    public function getVoteOptionsAttribute(): array
    {
        $config = $this->decision_type_config;
        $options = [];
        
        foreach ($config['votes'] ?? [] as $index => $value) {
            $options[] = [
                'value' => $value,
                'label' => $config['vote_labels'][$index] ?? ucfirst(str_replace('_', ' ', $value)),
                'color' => $config['vote_colors'][$value] ?? 'gray',
                'icon' => $config['vote_icons'][$value] ?? 'question-mark-circle',
            ];
        }
        
        return $options;
    }

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->current_stage, ['feedback', 'refinement', 'voting']);
    }

    public function getIsVotingAttribute(): bool
    {
        return $this->current_stage === 'voting';
    }

    public function getIsClosedAttribute(): bool
    {
        return in_array($this->current_stage, ['closed', 'archived']);
    }

    public function getTimeRemainingAttribute(): ?string
    {
        $deadline = $this->current_stage === 'voting' ? $this->voting_deadline : $this->feedback_deadline;
        
        if (!$deadline || $deadline->isPast()) {
            return null;
        }

        return $deadline->diffForHumans(['parts' => 2, 'short' => true]);
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->current_stage === 'voting' && $this->voting_deadline) {
            return $this->voting_deadline->isPast();
        }
        
        if (in_array($this->current_stage, ['feedback', 'refinement']) && $this->feedback_deadline) {
            return $this->feedback_deadline->isPast();
        }
        
        return false;
    }

    // ─────────────────────────────────────────────────────────────
    // VOTE CALCULATIONS
    // ─────────────────────────────────────────────────────────────

    public function getVoteCountsAttribute(): array
    {
        $counts = [];
        $config = $this->decision_type_config;
        
        foreach ($config['votes'] ?? [] as $vote) {
            $counts[$vote] = $this->votes()->where('vote_value', $vote)->count();
        }
        
        return $counts;
    }

    public function getTotalVotesAttribute(): int
    {
        return $this->votes()->count();
    }

    public function getEligibleVotersCountAttribute(): int
    {
        // For MVC, we'll count all users with 'reijikai' role
        // This should be customized based on your User model's role implementation
        return User::whereHas('roles', function ($query) {
            $query->whereIn('name', $this->allowed_roles ?? ['reijikai']);
        })->count();
    }

    public function getQuorumMetAttribute(): bool
    {
        $eligible = $this->eligible_voters_count;
        if ($eligible === 0) return false;
        
        $percentage = ($this->total_votes / $eligible) * 100;
        return $percentage >= $this->quorum_percentage;
    }

    public function getVotesNeededForQuorumAttribute(): int
    {
        $eligible = $this->eligible_voters_count;
        $needed = ceil($eligible * ($this->quorum_percentage / 100));
        return max(0, $needed - $this->total_votes);
    }

    public function getVoteResultsAttribute(): array
    {
        $counts = $this->vote_counts;
        $total = $this->total_votes;
        $config = $this->decision_type_config;
        $results = [];

        foreach ($config['votes'] ?? [] as $index => $vote) {
            $count = $counts[$vote] ?? 0;
            $results[$vote] = [
                'value' => $vote,
                'label' => $config['vote_labels'][$index] ?? ucfirst(str_replace('_', ' ', $vote)),
                'count' => $count,
                'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                'color' => $config['vote_colors'][$vote] ?? 'gray',
                'icon' => $config['vote_icons'][$vote] ?? 'question-mark-circle',
            ];
        }

        return $results;
    }

    // ─────────────────────────────────────────────────────────────
    // OUTCOME CALCULATION
    // ─────────────────────────────────────────────────────────────

    public function calculateOutcome(): string
    {
        if (!$this->quorum_met) {
            return 'no_quorum';
        }

        $counts = $this->vote_counts;
        $total = $this->total_votes;

        switch ($this->decision_type) {
            case 'democratic':
                $yesPercentage = $total > 0 ? ($counts['yes'] / $total) * 100 : 0;
                return $yesPercentage >= $this->pass_threshold ? 'passed' : 'rejected';

            case 'consensus':
                if (($counts['block'] ?? 0) > 0) {
                    return 'blocked';
                }
                if (($counts['disagree'] ?? 0) > 0) {
                    return 'rejected';
                }
                return 'passed';

            case 'consent':
                if (($counts['object'] ?? 0) > 0) {
                    return 'rejected';
                }
                return 'passed';

            default:
                return 'rejected';
        }
    }

    // ─────────────────────────────────────────────────────────────
    // PERMISSION HELPERS
    // ─────────────────────────────────────────────────────────────

    public function canUserView(User $user): bool
    {
        // Author can always view
        if ($user->id === $this->author_id) {
            return true;
        }

        // Draft is only visible to author
        if ($this->current_stage === 'draft') {
            return false;
        }

        // Check role-based access
        $userRoles = $user->roles->pluck('name')->toArray();
        $allowedRoles = $this->allowed_roles ?? ['reijikai'];

        return !empty(array_intersect($userRoles, $allowedRoles));
    }

    public function canUserVote(User $user): bool
    {
        // Must be in voting stage
        if ($this->current_stage !== 'voting') {
            return false;
        }

        // Check if deadline passed
        if ($this->voting_deadline && $this->voting_deadline->isPast()) {
            return false;
        }

        // Must have permission to view
        if (!$this->canUserView($user)) {
            return false;
        }

        // Must have reijikai role (only reijikai can vote in MVC)
        $userRoles = $user->roles->pluck('name')->toArray();
        if (!in_array('reijikai', $userRoles)) {
            return false;
        }

        return true;
    }

    public function canUserComment(User $user): bool
    {
        // Cannot comment on draft or archived
        if (in_array($this->current_stage, ['draft', 'archived'])) {
            return false;
        }

        // Must have permission to view
        return $this->canUserView($user);
    }

    public function canUserEdit(User $user): bool
    {
        // Only author can edit
        if ($user->id !== $this->author_id) {
            return false;
        }

        // Can only edit in draft or feedback stages
        return in_array($this->current_stage, ['draft', 'feedback', 'refinement']);
    }

    public function canTransitionTo(string $newStage): bool
    {
        $validTransitions = self::VALID_TRANSITIONS[$this->current_stage] ?? [];
        return in_array($newStage, $validTransitions);
    }

    public function getUserVote(User $user): ?Vote
    {
        return $this->votes()->where('user_id', $user->id)->first();
    }

    // ─────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereIn('current_stage', ['feedback', 'refinement', 'voting']);
    }

    public function scopeVoting($query)
    {
        return $query->where('current_stage', 'voting');
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('current_stage', ['closed', 'archived']);
    }

    public function scopeDraft($query)
    {
        return $query->where('current_stage', 'draft');
    }

    public function scopeByAuthor($query, User $user)
    {
        return $query->where('author_id', $user->id);
    }

    public function scopeVisibleTo($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            // Author can see all their proposals
            $q->where('author_id', $user->id)
              // Or non-draft proposals where user has role access
              ->orWhere(function ($q2) use ($user) {
                  $q2->where('current_stage', '!=', 'draft');
                  // Role check would need to be implemented based on your role system
              });
        });
    }

    public function scopeNeedsVoteFrom($query, User $user)
    {
        return $query->voting()
            ->whereDoesntHave('votes', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
    }
}
