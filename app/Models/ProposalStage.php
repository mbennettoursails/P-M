<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposal_id',
        'stage_type',
        'started_at',
        'ended_at',
        'is_active',
        'notes',
        'transitioned_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function transitioner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transitioned_by');
    }

    // ─────────────────────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────────────────────

    public function getStageConfigAttribute(): array
    {
        return Proposal::STAGES[$this->stage_type] ?? [];
    }

    public function getDurationAttribute(): ?string
    {
        if (!$this->ended_at) {
            return $this->started_at->diffForHumans(['parts' => 2]);
        }

        return $this->started_at->diffForHumans($this->ended_at, ['parts' => 2]);
    }

    // ─────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCompleted($query)
    {
        return $query->where('is_active', false)->whereNotNull('ended_at');
    }
}
