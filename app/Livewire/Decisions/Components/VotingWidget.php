<?php

namespace App\Livewire\Decisions\Components;

use App\Models\Proposal;
use App\Models\Vote;
use App\Services\ProposalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VotingWidget extends Component
{
    public Proposal $proposal;
    public ?Vote $userVote = null;
    public ?string $selectedVote = null;
    public string $reason = '';
    public bool $isAnonymous = false;
    public bool $showReasonInput = false;
    public bool $isChangingVote = false;

    public function mount(Proposal $proposal): void
    {
        $this->proposal = $proposal;
        $this->loadUserVote();
    }

    protected function loadUserVote(): void
    {
        $this->userVote = $this->proposal->getUserVote(Auth::user());
        
        if ($this->userVote) {
            $this->selectedVote = $this->userVote->vote_value;
            $this->reason = $this->userVote->reason ?? '';
            $this->isAnonymous = $this->userVote->is_anonymous;
        } else {
            $this->selectedVote = null;
            $this->reason = '';
            $this->isAnonymous = false;
        }
    }

    public function selectVote(string $value): void
    {
        if (!$this->can_vote) {
            return;
        }

        $this->selectedVote = $value;
        $this->showReasonInput = true;
    }

    public function submitVote(): void
    {
        if (!$this->selectedVote) {
            $this->addError('vote', 'Please select a vote option.');
            return;
        }

        if (!$this->can_vote) {
            $this->addError('vote', 'You cannot vote on this proposal.');
            return;
        }

        try {
            $service = app(ProposalService::class);
            
            $this->userVote = $service->castVote(
                $this->proposal,
                Auth::user(),
                $this->selectedVote,
                $this->reason ?: null,
                $this->isAnonymous
            );

            $this->isChangingVote = false;
            $this->showReasonInput = false;
            $this->proposal->refresh();
            
            $this->dispatch('vote-cast');
            $this->dispatch('notify', type: 'success', message: 'Vote submitted successfully.');
            
        } catch (\Exception $e) {
            $this->addError('vote', $e->getMessage());
        }
    }

    public function changeVote(): void
    {
        $this->isChangingVote = true;
        $this->showReasonInput = true;
    }

    public function cancelChange(): void
    {
        $this->isChangingVote = false;
        $this->showReasonInput = false;
        $this->loadUserVote();
    }

    // ─────────────────────────────────────────────────────────────
    // COMPUTED PROPERTIES
    // ─────────────────────────────────────────────────────────────

    public function getCanVoteProperty(): bool
    {
        return $this->proposal->canUserVote(Auth::user());
    }

    public function getHasVotedProperty(): bool
    {
        return $this->userVote !== null;
    }

    public function getVoteOptionsProperty(): array
    {
        return $this->proposal->vote_options;
    }

    public function getShowAnonymousOptionProperty(): bool
    {
        return $this->proposal->allow_anonymous_voting;
    }

    public function getVoteResultsProperty(): array
    {
        return $this->proposal->vote_results;
    }

    public function getShowResultsProperty(): bool
    {
        // Show results if:
        // - Voting is closed
        // - Or show_results_during_voting is enabled
        // - Or user has already voted
        return $this->proposal->is_closed 
            || $this->proposal->show_results_during_voting 
            || $this->has_voted;
    }

    public function getQuorumStatusProperty(): array
    {
        return [
            'met' => $this->proposal->quorum_met,
            'current' => $this->proposal->total_votes,
            'required' => ceil($this->proposal->eligible_voters_count * ($this->proposal->quorum_percentage / 100)),
            'eligible' => $this->proposal->eligible_voters_count,
            'percentage' => $this->proposal->eligible_voters_count > 0 
                ? round(($this->proposal->total_votes / $this->proposal->eligible_voters_count) * 100, 1) 
                : 0,
            'needed' => $this->proposal->votes_needed_for_quorum,
        ];
    }

    public function render()
    {
        return view('livewire.decisions.components.voting-widget', [
            'canVote' => $this->can_vote,
            'hasVoted' => $this->has_voted,
            'voteOptions' => $this->vote_options,
            'showAnonymousOption' => $this->show_anonymous_option,
            'voteResults' => $this->vote_results,
            'showResults' => $this->show_results,
            'quorumStatus' => $this->quorum_status,
        ]);
    }
}
