<?php

namespace App\Livewire\Decisions;

use App\Models\Proposal;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Decisions')]
class ProposalList extends Component
{
    use WithPagination;

    public string $tab = 'active';
    public string $search = '';
    public string $decisionType = '';
    public string $sortBy = 'updated_at';
    public string $sortDirection = 'desc';

    protected $queryString = [
        'tab' => ['except' => 'active'],
        'search' => ['except' => ''],
        'decisionType' => ['except' => ''],
        'sortBy' => ['except' => 'updated_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        // Get tab from query string if provided
        $this->tab = request()->query('tab', 'active');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTab(): void
    {
        $this->resetPage();
    }

    public function updatingDecisionType(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function getProposalsProperty()
    {
        $query = Proposal::query()->with(['author', 'votes']);

        // Apply tab filter
        switch ($this->tab) {
            case 'active':
                $query->whereNotIn('current_stage', ['closed', 'archived']);
                break;
            case 'voting':
                $query->where('current_stage', 'voting');
                break;
            case 'needs_vote':
                $query->where('current_stage', 'voting')
                    ->whereHas('participants', fn($q) => $q->where('user_id', Auth::id())->where('can_vote', true))
                    ->whereDoesntHave('votes', fn($q) => $q->where('user_id', Auth::id()));
                break;
            case 'drafts':
                $query->where('author_id', Auth::id())->where('current_stage', 'draft');
                break;
            case 'my_proposals':
                $query->where('author_id', Auth::id());
                break;
            case 'closed':
                $query->whereIn('current_stage', ['closed', 'archived']);
                break;
            case 'all':
            default:
                // Show all non-draft proposals user can see
                $query->where(function ($q) {
                    $q->where('author_id', Auth::id())
                      ->orWhere(function ($sub) {
                          $sub->whereNotIn('current_stage', ['draft']);
                      });
                });
                break;
        }

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'ilike', '%' . $this->search . '%')
                  ->orWhere('title_en', 'ilike', '%' . $this->search . '%')
                  ->orWhere('description', 'ilike', '%' . $this->search . '%');
            });
        }

        // Apply decision type filter
        if ($this->decisionType) {
            $query->where('decision_type', $this->decisionType);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(10);
    }

    public function getTabCountsProperty(): array
    {
        $user = Auth::user();
        
        return [
            'active' => Proposal::whereNotIn('current_stage', ['closed', 'archived'])->count(),
            'voting' => Proposal::where('current_stage', 'voting')->count(),
            'needs_vote' => Proposal::where('current_stage', 'voting')
                ->whereHas('participants', fn($q) => $q->where('user_id', $user->id)->where('can_vote', true))
                ->whereDoesntHave('votes', fn($q) => $q->where('user_id', $user->id))
                ->count(),
            'drafts' => Proposal::where('author_id', $user->id)->where('current_stage', 'draft')->count(),
            'my_proposals' => Proposal::where('author_id', $user->id)->count(),
            'closed' => Proposal::whereIn('current_stage', ['closed', 'archived'])->count(),
        ];
    }

    public function render()
    {
        return view('livewire.decisions.proposal-list', [
            'proposals' => $this->proposals,
            'tabCounts' => $this->tabCounts,
        ]);
    }
}