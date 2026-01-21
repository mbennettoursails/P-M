<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProposalStage extends Model
{
    protected $fillable = ['proposal_id', 'stage_type', 'started_at', 'ended_at', 'is_active', 'notes', 'transitioned_by'];

    protected $casts = ['started_at' => 'datetime', 'ended_at' => 'datetime', 'is_active' => 'boolean'];

    public function proposal(): BelongsTo { return $this->belongsTo(Proposal::class); }
    public function transitioner(): BelongsTo { return $this->belongsTo(User::class, 'transitioned_by'); }

    public function getStageConfigAttribute(): array { return Proposal::STAGES[$this->stage_type] ?? []; }
    
    public function getDurationAttribute(): ?int {
        return $this->started_at->diffInMinutes($this->ended_at ?? now());
    }

    public function getDurationFormattedAttribute(): string {
        $minutes = $this->duration;
        if ($minutes < 60) return $minutes . '分';
        $hours = floor($minutes / 60);
        if ($hours < 24) return $hours . '時間';
        return floor($hours / 24) . '日';
    }
}
