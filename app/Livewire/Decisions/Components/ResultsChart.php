<?php

namespace App\Livewire\Decisions\Components;

use App\Models\Proposal;
use App\Services\ProposalService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ResultsChart extends Component
{
    public Proposal $proposal;
    public array $results = [];

    protected $listeners = ['vote-cast' => 'refreshResults'];

    public function mount(Proposal $proposal)
    {
        $this->proposal = $proposal;
        $this->refreshResults();
    }

    public function refreshResults()
    {
        $this->results = app(ProposalService::class)->getVoteResults($this->proposal, Auth::user());
    }

    public function getVoteOptionsProperty(): array
    {
        $config = $this->proposal->decision_type_config;
        $options = [];
        foreach ($config['votes'] as $index => $value) {
            $options[$value] = [
                'label' => $config['votes_ja'][$index],
                'label_en' => ucfirst(str_replace('_', ' ', $value)),
                'color' => $config['vote_colors'][$value] ?? 'gray',
            ];
        }
        return $options;
    }

    public function render()
    {
        return view('livewire.decisions.components.results-chart', ['voteOptions' => $this->vote_options]);
    }
}
