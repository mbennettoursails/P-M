<?php

namespace App\Livewire\Decisions;

use App\Models\Proposal;
use App\Services\ProposalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ProposalCreate extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public int $totalSteps = 4;

    // Step 1: Basic Information
    public string $title = '';
    public string $description = '';

    // Step 2: Decision Settings
    public string $decision_type = 'democratic';
    public int $quorum_percentage = 50;
    public int $pass_threshold = 50;
    public bool $allow_anonymous_voting = false;
    public bool $show_results_during_voting = true;

    // Step 3: Participants
    public array $allowed_roles = ['reijikai'];
    public bool $is_invite_only = false;

    // Step 4: Timeline & Documents
    public ?string $feedback_deadline = null;
    public ?string $voting_deadline = null;
    public array $documents = [];

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
            'voting_deadline' => 'nullable|date',
            'documents.*' => 'nullable|file|max:10240',
        ];
    }

    protected $messages = [
        'title.required' => 'Please enter a title for your proposal.',
        'description.required' => 'Please provide a description.',
    ];

    public function mount(): void
    {
        // Skip authorization for now to debug
        // $this->authorize('create', Proposal::class);
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();
        
        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->step) {
            $this->step = $step;
        }
    }

    protected function validateCurrentStep(): void
    {
        switch ($this->step) {
            case 1:
                $this->validate([
                    'title' => 'required|string|max:255',
                    'description' => 'required|string|max:10000',
                ]);
                break;
            case 2:
                $this->validate([
                    'decision_type' => 'required|in:democratic,consensus,consent',
                    'quorum_percentage' => 'required|integer|min:25|max:100',
                    'pass_threshold' => 'required|integer|min:50|max:100',
                ]);
                break;
            case 3:
                $this->validate([
                    'allowed_roles' => 'required|array|min:1',
                ]);
                break;
        }
    }

    public function removeDocument(int $index): void
    {
        if (isset($this->documents[$index])) {
            unset($this->documents[$index]);
            $this->documents = array_values($this->documents);
        }
    }

    public function submit()
    {
        Log::info('ProposalCreate::submit called', [
            'user_id' => Auth::id(),
            'title' => $this->title,
        ]);

        try {
            $this->validate();
            
            Log::info('Validation passed');

            $service = app(ProposalService::class);

            $proposalData = [
                'title' => $this->title,
                'description' => $this->description,
                'decision_type' => $this->decision_type,
                'quorum_percentage' => $this->quorum_percentage,
                'pass_threshold' => $this->pass_threshold,
                'allow_anonymous_voting' => $this->allow_anonymous_voting,
                'show_results_during_voting' => $this->show_results_during_voting,
                'allowed_roles' => $this->allowed_roles,
                'is_invite_only' => $this->is_invite_only,
                'feedback_deadline' => $this->feedback_deadline ?: null,
                'voting_deadline' => $this->voting_deadline ?: null,
            ];

            Log::info('Creating proposal with data', $proposalData);

            $proposal = $service->createProposal($proposalData, Auth::user());

            Log::info('Proposal created', ['proposal_id' => $proposal->id, 'uuid' => $proposal->uuid]);

            foreach ($this->documents as $document) {
                $service->uploadDocument($proposal, Auth::user(), $document);
            }

            session()->flash('success', 'Proposal created successfully.');
            
            return $this->redirect(route('decisions.show', ['proposal' => $proposal->uuid]), navigate: true);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Proposal creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('submit', 'Failed to create proposal: ' . $e->getMessage());
        }
    }

    public function saveDraft()
    {
        Log::info('ProposalCreate::saveDraft called');

        try {
            $this->validate([
                'title' => 'required|string|max:255',
            ]);

            $service = app(ProposalService::class);

            $proposal = $service->createProposal([
                'title' => $this->title,
                'description' => $this->description ?: '',
                'decision_type' => $this->decision_type,
                'quorum_percentage' => $this->quorum_percentage,
                'pass_threshold' => $this->pass_threshold,
                'allow_anonymous_voting' => $this->allow_anonymous_voting,
                'show_results_during_voting' => $this->show_results_during_voting,
                'allowed_roles' => $this->allowed_roles,
                'is_invite_only' => $this->is_invite_only,
                'feedback_deadline' => $this->feedback_deadline ?: null,
                'voting_deadline' => $this->voting_deadline ?: null,
            ], Auth::user());

            session()->flash('success', 'Draft saved.');
            
            return $this->redirect(route('decisions.show', ['proposal' => $proposal->uuid]), navigate: true);

        } catch (\Exception $e) {
            Log::error('Draft save failed', ['error' => $e->getMessage()]);
            $this->addError('submit', 'Failed to save draft: ' . $e->getMessage());
        }
    }

    public function getDecisionTypesProperty(): array
    {
        return Proposal::DECISION_TYPES;
    }

    public function getSelectedDecisionTypeProperty(): array
    {
        return Proposal::DECISION_TYPES[$this->decision_type] ?? [];
    }

    public function getStepsProperty(): array
    {
        return [
            1 => ['title' => 'Basic Information', 'icon' => 'document-text'],
            2 => ['title' => 'Decision Settings', 'icon' => 'cog-6-tooth'],
            3 => ['title' => 'Participants', 'icon' => 'users'],
            4 => ['title' => 'Timeline & Documents', 'icon' => 'calendar'],
        ];
    }

    public function render()
    {
        return view('livewire.decisions.proposal-create', [
            'decisionTypes' => $this->decision_types,
            'selectedDecisionType' => $this->selected_decision_type,
            'steps' => $this->steps,
        ]);
    }
}