<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'proposal_id',
        'user_id',
        'vote_value',
        'reason',
        'is_anonymous',
        'previous_vote_value',
        'changed_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'changed_at' => 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────
    // BOOT
    // ─────────────────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($vote) {
            if (empty($vote->uuid)) {
                $vote->uuid = (string) Str::uuid();
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

    // ─────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────

    public function getVoteConfigAttribute(): array
    {
        $decisionConfig = $this->proposal->decision_type_config;
        $votes = $decisionConfig['votes'] ?? [];
        $index = array_search($this->vote_value, $votes);

        if ($index === false) {
            return [
                'value' => $this->vote_value,
                'label' => ucfirst(str_replace('_', ' ', $this->vote_value)),
                'color' => 'gray',
                'icon' => 'question-mark-circle',
            ];
        }

        return [
            'value' => $this->vote_value,
            'label' => $decisionConfig['vote_labels'][$index] ?? ucfirst(str_replace('_', ' ', $this->vote_value)),
            'color' => $decisionConfig['vote_colors'][$this->vote_value] ?? 'gray',
            'icon' => $decisionConfig['vote_icons'][$this->vote_value] ?? 'question-mark-circle',
        ];
    }

    public function getHasChangedAttribute(): bool
    {
        return !is_null($this->previous_vote_value);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }

        return $this->user->name ?? 'Unknown';
    }

    // ─────────────────────────────────────────────────────────────
    // METHODS
    // ─────────────────────────────────────────────────────────────

    public function changeVote(string $newValue, ?string $newReason = null): bool
    {
        if ($this->vote_value === $newValue) {
            // Only update reason if vote didn't change
            if ($newReason !== null && $newReason !== $this->reason) {
                $this->reason = $newReason;
                return $this->save();
            }
            return false;
        }

        $this->previous_vote_value = $this->vote_value;
        $this->vote_value = $newValue;
        $this->changed_at = now();
        
        if ($newReason !== null) {
            $this->reason = $newReason;
        }

        return $this->save();
    }
}
