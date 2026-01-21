<?php

namespace App\Livewire\Decisions\Components;

use App\Models\Proposal;
use App\Models\Vote;
use App\Services\ProposalService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class VotingWidget extends Component
{
    public Proposal $proposal;
    public ?Vote $userVote = null;
    public ?string $selectedVote = null;
    public string $reason = '';
    public bool $isAnonymous = false;
    public bool $showReasonInput = false;
    public bool $isChangingVote = false;

    protected $listeners = ['refreshVoting' => '$refresh'];

    public function mount(Proposal $proposal)
    {
        $this->proposal = $proposal;
        $this->loadUserVote();
    }

    protected function loadUserVote()
    {
        $this->userVote = $this->proposal->getUserVote(Auth::user());
        if ($this->userVote) {
            $this->selectedVote = $this->userVote->vote_value;
            $this->reason = $this->userVote->reason ?? '';
            $this->isAnonymous = $this->userVote->is_anonymous;
        }
    }

    public function selectVote(string $value) { $this->selectedVote = $value; $this->showReasonInput = true; }
    public function toggleChangeVote() { $this->isChangingVote = !$this->isChangingVote; if ($this->isChangingVote) $this->showReasonInput = true; }

    public function cancelChange()
    {
        $this->isChangingVote = false;
        $this->showReasonInput = false;
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

    public function submitVote(ProposalService $proposalService)
    {
        if (!$this->selectedVote) {
            $this->addError('vote', __('decisions.voting.select_vote'));
            return;
        }

        try {
            $this->userVote = $proposalService->castVote($this->proposal, Auth::user(), $this->selectedVote, $this->reason ?: null, $this->isAnonymous);
            $this->isChangingVote = false;
            $this->showReasonInput = false;
            $this->dispatch('vote-cast');
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.vote_cast')]);
        } catch (\Exception $e) {
            $this->addError('vote', $e->getMessage());
        }
    }

    public function getCanVoteProperty(): bool { return $this->proposal->canUserVote(Auth::user()); }
    public function getHasVotedProperty(): bool { return $this->userVote !== null; }

    public function getVoteOptionsProperty(): array
    {
        $config = $this->proposal->decision_type_config;
        $options = [];
        foreach ($config['votes'] as $index => $value) {
            $options[] = [
                'value' => $value,
                'label' => $config['votes_ja'][$index],
                'label_en' => ucfirst(str_replace('_', ' ', $value)),
                'color' => $config['vote_colors'][$value] ?? 'gray',
                'icon' => $config['vote_icons'][$value] ?? 'question-mark-circle',
            ];
        }
        return $options;
    }

    public function getShowAnonymousOptionProperty(): bool { return $this->proposal->allow_anonymous_voting; }

    public function render()
    {
        return view('livewire.decisions.components.voting-widget', [
            'canVote' => $this->can_vote,
            'hasVoted' => $this->has_voted,
            'voteOptions' => $this->vote_options,
            'showAnonymousOption' => $this->show_anonymous_option,
        ]);
    }
}
