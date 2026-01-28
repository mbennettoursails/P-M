<?php

namespace App\Livewire\Decisions;

use App\Models\Proposal;
use App\Models\Vote;
use App\Services\ProposalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;

#[Layout('layouts.app')]
class ProposalShow extends Component
{
    public Proposal $proposal;
    
    public string $activeTab = 'overview';
    
    // Actions menu (header dropdown)
    public bool $showActionsMenu = false;
    
    // Stage transition modal
    public bool $showStageModal = false;
    public ?string $targetStage = null;
    public string $stageNotes = '';
    
    // Withdraw modal
    public bool $showWithdrawModal = false;
    public string $withdrawReason = '';

    protected $listeners = [
        'vote-cast' => '$refresh',
        'comment-added' => '$refresh',
        'document-uploaded' => '$refresh',
    ];

    public function mount(Proposal $proposal): void
    {
        $this->authorize('view', $proposal);
        $this->proposal = $proposal;

        // Default to vote tab if user can vote and hasn't voted yet
        if ($proposal->is_voting && $proposal->canUserVote(Auth::user())) {
            $existingVote = $proposal->getUserVote(Auth::user());
            if (!$existingVote) {
                $this->activeTab = 'vote';
            }
        }
        
        Log::info('ProposalShow::mount', [
            'proposal_id' => $proposal->id,
            'activeTab' => $this->activeTab,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════
    // TAB NAVIGATION
    // ═══════════════════════════════════════════════════════════════════

    public function setTab(string $tab): void
    {
        Log::info('ProposalShow::setTab', ['from' => $this->activeTab, 'to' => $tab]);
        
        $validTabs = ['overview', 'discussion', 'vote', 'documents', 'history'];
        
        if (in_array($tab, $validTabs)) {
            $this->activeTab = $tab;
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // STAGE TRANSITIONS
    // ═══════════════════════════════════════════════════════════════════

    public function getAvailableTransitionsProperty(): array
    {
        $transitions = Proposal::VALID_TRANSITIONS[$this->proposal->current_stage] ?? [];
        return array_filter($transitions, fn($t) => $t !== 'withdrawn');
    }

    public function openStageModal(string $stage): void
    {
        $this->authorize('advanceStage', $this->proposal);
        $this->targetStage = $stage;
        $this->stageNotes = '';
        $this->showStageModal = true;
    }

    public function closeStageModal(): void
    {
        $this->showStageModal = false;
        $this->targetStage = null;
        $this->stageNotes = '';
    }

    public function confirmAdvanceStage(): void
    {
        $this->authorize('advanceStage', $this->proposal);

        try {
            $service = app(ProposalService::class);
            $service->advanceStage(
                $this->proposal,
                $this->targetStage,
                Auth::user(),
                $this->stageNotes ?: null
            );

            $this->proposal->refresh();
            $this->closeStageModal();
            session()->flash('success', __('Stage updated successfully.'));
        } catch (\Exception $e) {
            $this->addError('stage', $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // WITHDRAW
    // ═══════════════════════════════════════════════════════════════════

    public function openWithdrawModal(): void
    {
        $this->authorize('withdraw', $this->proposal);
        $this->withdrawReason = '';
        $this->showWithdrawModal = true;
    }

    public function closeWithdrawModal(): void
    {
        $this->showWithdrawModal = false;
        $this->withdrawReason = '';
    }

    public function confirmWithdraw()
    {
        $this->authorize('withdraw', $this->proposal);

        try {
            $service = app(ProposalService::class);
            $service->withdrawProposal(
                $this->proposal,
                Auth::user(),
                $this->withdrawReason ?: null
            );

            session()->flash('success', __('Proposal withdrawn.'));
            return $this->redirect(route('decisions.index'), navigate: true);
        } catch (\Exception $e) {
            $this->addError('withdraw', $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // COMPUTED PROPERTIES
    // ═══════════════════════════════════════════════════════════════════

    public function getCanVoteProperty(): bool
    {
        return $this->proposal->canUserVote(Auth::user());
    }

    public function getCanCommentProperty(): bool
    {
        return $this->proposal->canUserComment(Auth::user());
    }

    public function getCanEditProperty(): bool
    {
        return $this->proposal->canUserEdit(Auth::user());
    }

    public function getIsAuthorProperty(): bool
    {
        return Auth::id() === $this->proposal->author_id;
    }

    public function getUserVoteProperty(): ?Vote
    {
        return $this->proposal->getUserVote(Auth::user());
    }

    public function getStageHistoryProperty()
    {
        return $this->proposal->stages()->with('transitioner')->orderBy('started_at', 'asc')->get();
    }

    public function getCommentsCountProperty(): int
    {
        return $this->proposal->allComments()->count();
    }

    public function getDocumentsCountProperty(): int
    {
        return $this->proposal->documents()->count();
    }

    // ═══════════════════════════════════════════════════════════════════
    // RENDER
    // ═══════════════════════════════════════════════════════════════════

    public function render()
    {
        return view('livewire.decisions.proposal-show', [
            'canVote' => $this->can_vote,
            'canComment' => $this->can_comment,
            'canEdit' => $this->can_edit,
            'isAuthor' => $this->is_author,
            'userVote' => $this->user_vote,
            'availableTransitions' => $this->available_transitions,
            'stageHistory' => $this->stage_history,
            'commentsCount' => $this->comments_count,
            'documentsCount' => $this->documents_count,
        ]);
    }
}