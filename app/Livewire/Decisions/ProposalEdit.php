<?php

namespace App\Livewire\Decisions;

use App\Models\Proposal;
use App\Services\ProposalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProposalEdit extends Component
{
    use WithFileUploads;

    public Proposal $proposal;

    public string $title = '';
    public string $description = '';
    public string $decision_type = 'democratic';
    public int $quorum_percentage = 50;
    public int $pass_threshold = 50;
    public bool $allow_anonymous_voting = false;
    public bool $show_results_during_voting = true;
    public array $allowed_roles = ['reijikai'];
    public bool $is_invite_only = false;
    public ?string $feedback_deadline = null;
    public ?string $voting_deadline = null;
    public array $newDocuments = [];

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:10000',
            'decision_type' => 'required|in:democratic,consensus,consent',
            'quorum_percentage' => 'required|integer|min:25|max:100',
            'pass_threshold' => 'required|integer|min:50|max:100',
            'allow_anonymous_voting' => 'boolean',
            'show_results_during_voting' => 'boolean',
            'allowed_roles' => 'required|array|min:1',
            'allowed_roles.*' => 'in:reijikai,shokuin,volunteer',
            'is_invite_only' => 'boolean',
            'feedback_deadline' => 'nullable|date',
            'voting_deadline' => 'nullable|date|after:feedback_deadline',
            'newDocuments.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,txt,jpg,jpeg,png,gif,webp',
        ];
    }

    public function mount(Proposal $proposal): void
    {
        $this->authorize('update', $proposal);
        
        $this->proposal = $proposal;
        $this->title = $proposal->title;
        $this->description = $proposal->description;
        $this->decision_type = $proposal->decision_type;
        $this->quorum_percentage = $proposal->quorum_percentage;
        $this->pass_threshold = $proposal->pass_threshold;
        $this->allow_anonymous_voting = $proposal->allow_anonymous_voting;
        $this->show_results_during_voting = $proposal->show_results_during_voting;
        $this->allowed_roles = $proposal->allowed_roles ?? ['reijikai'];
        $this->is_invite_only = $proposal->is_invite_only;
        $this->feedback_deadline = $proposal->feedback_deadline?->format('Y-m-d\TH:i');
        $this->voting_deadline = $proposal->voting_deadline?->format('Y-m-d\TH:i');
    }

    public function save()
    {
        $this->authorize('update', $this->proposal);
        $this->validate();

        try {
            $service = app(ProposalService::class);

            $service->updateProposal($this->proposal, [
                'title' => $this->title,
                'description' => $this->description,
                'decision_type' => $this->decision_type,
                'quorum_percentage' => $this->quorum_percentage,
                'pass_threshold' => $this->pass_threshold,
                'allow_anonymous_voting' => $this->allow_anonymous_voting,
                'show_results_during_voting' => $this->show_results_during_voting,
                'allowed_roles' => $this->allowed_roles,
                'is_invite_only' => $this->is_invite_only,
                'feedback_deadline' => $this->feedback_deadline,
                'voting_deadline' => $this->voting_deadline,
            ], Auth::user());

            foreach ($this->newDocuments as $document) {
                $service->uploadDocument($this->proposal, Auth::user(), $document);
            }

            session()->flash('success', 'Proposal updated successfully.');
            
            $this->redirect(route('decisions.show', $this->proposal), navigate: true);

        } catch (\Exception $e) {
            $this->addError('save', $e->getMessage());
        }
    }

    public function deleteDocument(int $documentId): void
    {
        try {
            $document = $this->proposal->documents()->findOrFail($documentId);
            $service = app(ProposalService::class);
            $service->deleteDocument($document, Auth::user());
            
            $this->proposal->refresh();
            session()->flash('success', 'Document deleted.');
            
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function removeNewDocument(int $index): void
    {
        if (isset($this->newDocuments[$index])) {
            unset($this->newDocuments[$index]);
            $this->newDocuments = array_values($this->newDocuments);
        }
    }

    public function getDecisionTypesProperty(): array
    {
        return Proposal::DECISION_TYPES;
    }

    public function getCanEditSettingsProperty(): bool
    {
        return $this->proposal->current_stage === 'draft';
    }

    public function render()
    {
        return view('livewire.decisions.proposal-edit', [
            'decisionTypes' => $this->decision_types,
            'canEditSettings' => $this->can_edit_settings,
        ]);
    }
}