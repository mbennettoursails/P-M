<?php

namespace App\Livewire\Decisions;

use App\Models\Proposal;
use App\Services\ProposalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

#[Layout('layouts.app')]
class ProposalList extends Component
{
    use WithPagination;

    #[Url]
    public string $activeTab = 'active';

    #[Url]
    public string $search = '';

    public array $tabs = [
        'active' => ['label' => 'Active', 'icon' => 'clock'],
        'voting' => ['label' => 'Voting', 'icon' => 'hand-raised'],
        'needs_vote' => ['label' => 'Needs Vote', 'icon' => 'exclamation-circle'],
        'drafts' => ['label' => 'Drafts', 'icon' => 'pencil'],
        'closed' => ['label' => 'Closed', 'icon' => 'check-circle'],
    ];

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function getProposalsProperty()
    {
        $query = Proposal::with(['author', 'votes'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('title', 'ilike', "%{$this->search}%")
                       ->orWhere('description', 'ilike', "%{$this->search}%");
                });
            });

        switch ($this->activeTab) {
            case 'active':
                $query->whereIn('current_stage', ['feedback', 'refinement', 'voting']);
                break;
            case 'voting':
                $query->where('current_stage', 'voting');
                break;
            case 'needs_vote':
                $query->where('current_stage', 'voting')
                      ->whereDoesntHave('votes', fn($q) => $q->where('user_id', Auth::id()));
                break;
            case 'drafts':
                $query->where('current_stage', 'draft')
                      ->where('author_id', Auth::id());
                break;
            case 'closed':
                $query->whereIn('current_stage', ['closed', 'archived']);
                break;
        }

        return $query->latest('updated_at')->paginate(10);
    }

    public function getTabCountsProperty(): array
    {
        $user = Auth::user();
        
        return [
            'active' => Proposal::whereIn('current_stage', ['feedback', 'refinement', 'voting'])->count(),
            'voting' => Proposal::where('current_stage', 'voting')->count(),
            'needs_vote' => Proposal::where('current_stage', 'voting')
                ->whereDoesntHave('votes', fn($q) => $q->where('user_id', $user->id))
                ->count(),
            'drafts' => Proposal::where('current_stage', 'draft')
                ->where('author_id', $user->id)
                ->count(),
            'closed' => Proposal::whereIn('current_stage', ['closed', 'archived'])->count(),
        ];
    }

    public function render()
    {
        return view('livewire.decisions.proposal-list', [
            'proposals' => $this->proposals,
            'tabCounts' => $this->tab_counts,
        ]);
    }
}