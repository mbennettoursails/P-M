<?php

namespace App\Livewire\Decisions;

use App\Models\Proposal;
use App\Models\User;
use App\Services\ProposalService;
use App\Services\NotificationService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ProposalShow extends Component
{
    public Proposal $proposal;
    public string $activeTab = 'overview';
    public bool $showStageModal = false;
    public bool $showWithdrawModal = false;
    public bool $showInviteModal = false;
    public string $withdrawReason = '';
    public string $stageNotes = '';
    public string $targetStage = '';
    public string $inviteSearch = '';
    public array $selectedInviteUsers = [];

    protected $listeners = ['vote-cast' => '$refresh', 'comment-added' => '$refresh', 'document-uploaded' => '$refresh'];

    public function mount(Proposal $proposal)
    {
        $this->proposal = $proposal;
        if (Auth::check()) $this->proposal->markViewedBy(Auth::user());
    }

    public function setTab(string $tab) { $this->activeTab = $tab; }

    public function openStageModal(string $stage)
    {
        $this->targetStage = $stage;
        $this->stageNotes = '';
        $this->showStageModal = true;
    }

    public function closeStageModal()
    {
        $this->showStageModal = false;
        $this->targetStage = '';
        $this->stageNotes = '';
    }

    public function advanceStage(ProposalService $proposalService)
    {
        if (!$this->targetStage) return;
        try {
            $proposalService->advanceStage($this->proposal, $this->targetStage, Auth::user(), $this->stageNotes ?: null);
            $this->proposal->refresh();
            $this->closeStageModal();
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.stage_changed')]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function openWithdrawModal() { $this->withdrawReason = ''; $this->showWithdrawModal = true; }
    public function closeWithdrawModal() { $this->showWithdrawModal = false; $this->withdrawReason = ''; }

    public function withdraw(ProposalService $proposalService)
    {
        try {
            $proposalService->withdraw($this->proposal, Auth::user(), $this->withdrawReason ?: null);
            $this->proposal->refresh();
            $this->closeWithdrawModal();
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.withdrawn')]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function closeVoting(ProposalService $proposalService)
    {
        try {
            $proposalService->closeVoting($this->proposal, Auth::user());
            $this->proposal->refresh();
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.voting_closed')]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function openInviteModal() { $this->inviteSearch = ''; $this->selectedInviteUsers = []; $this->showInviteModal = true; }
    public function closeInviteModal() { $this->showInviteModal = false; $this->inviteSearch = ''; $this->selectedInviteUsers = []; }

    public function toggleInviteUser(int $userId)
    {
        if (in_array($userId, $this->selectedInviteUsers)) {
            $this->selectedInviteUsers = array_values(array_diff($this->selectedInviteUsers, [$userId]));
        } else {
            $this->selectedInviteUsers[] = $userId;
        }
    }

    public function inviteUsers(ProposalService $proposalService)
    {
        if (empty($this->selectedInviteUsers)) return;
        try {
            $count = $proposalService->inviteParticipants($this->proposal, $this->selectedInviteUsers);
            $this->proposal->refresh();
            $this->closeInviteModal();
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.users_invited', ['count' => $count])]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function sendReminders(NotificationService $notificationService)
    {
        try {
            $count = $notificationService->sendVoteReminders($this->proposal);
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.reminders_sent', ['count' => $count])]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getCanEditProperty(): bool { return $this->proposal->canUserEdit(Auth::user()); }
    public function getCanAdvanceStageProperty(): bool { return $this->proposal->canUserAdvanceStage(Auth::user()); }
    public function getCanVoteProperty(): bool { return $this->proposal->canUserVote(Auth::user()); }
    public function getCanCommentProperty(): bool { return $this->proposal->canUserComment(Auth::user()); }
    public function getUserVoteProperty() { return $this->proposal->getUserVote(Auth::user()); }
    
    public function getAvailableTransitionsProperty(): array
    {
        $valid = Proposal::VALID_TRANSITIONS[$this->proposal->current_stage] ?? [];
        return array_filter($valid, fn($stage) => $stage !== 'withdrawn');
    }

    public function getCanWithdrawProperty(): bool
    {
        return $this->proposal->author_id === Auth::id() && !in_array($this->proposal->current_stage, ['closed', 'archived']);
    }

    public function getVoteResultsProperty(): array { return app(ProposalService::class)->getVoteResults($this->proposal, Auth::user()); }
    public function getStatisticsProperty(): array { return app(ProposalService::class)->getStatistics($this->proposal); }

    public function getInvitableUsersProperty()
    {
        $existingIds = $this->proposal->participants()->pluck('users.id')->toArray();
        $query = User::whereNotIn('id', $existingIds);
        if ($this->inviteSearch) $query->where(fn($q) => $q->where('name', 'like', '%' . $this->inviteSearch . '%')->orWhere('email', 'like', '%' . $this->inviteSearch . '%'));
        return $query->orderBy('name')->limit(20)->get();
    }

    public function render()
    {
        return view('livewire.decisions.proposal-show', [
            'canEdit' => $this->can_edit,
            'canAdvanceStage' => $this->can_advance_stage,
            'canVote' => $this->can_vote,
            'canComment' => $this->can_comment,
            'canWithdraw' => $this->can_withdraw,
            'userVote' => $this->user_vote,
            'voteResults' => $this->vote_results,
            'statistics' => $this->statistics,
            'availableTransitions' => $this->available_transitions,
            'invitableUsers' => $this->invitable_users,
        ])->layout('layouts.app', ['title' => $this->proposal->localized_title]);
    }
}
