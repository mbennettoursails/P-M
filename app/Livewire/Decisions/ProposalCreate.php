<?php

namespace App\Livewire\Decisions;

use App\Models\Proposal;
use App\Models\User;
use App\Services\ProposalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

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
    public array $invited_user_ids = [];
    public string $user_search = '';

    // Step 4: Timeline & Documents
    public ?string $feedback_deadline = null;
    public ?string $voting_deadline = null;
    public array $documents = [];

    // UI State
    public bool $isSubmitting = false;
    public bool $showUserSearch = false;

    // ─────────────────────────────────────────────────────────────
    // LIFECYCLE
    // ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        Log::info('ProposalCreate::mount called', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name,
        ]);
    }

    /**
     * Hydrate hook - runs on every subsequent request
     */
    public function hydrate(): void
    {
        Log::debug('ProposalCreate::hydrate', [
            'step' => $this->step,
            'isSubmitting' => $this->isSubmitting,
        ]);
        
        // Ensure step stays within bounds
        if ($this->step < 1) {
            $this->step = 1;
        }
        if ($this->step > $this->totalSteps) {
            $this->step = $this->totalSteps;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // VALIDATION
    // ─────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        $rules = [
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
            'feedback_deadline' => 'nullable|date|after:now',
            'voting_deadline' => 'nullable|date|after:feedback_deadline',
            'documents.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,txt,jpg,jpeg,png,gif,webp',
        ];

        if ($this->is_invite_only) {
            $rules['invited_user_ids'] = 'required|array|min:1';
            $rules['invited_user_ids.*'] = 'exists:users,id';
        }

        return $rules;
    }

    protected $messages = [
        'title.required' => 'Please enter a title for your proposal.',
        'title.max' => 'Title cannot exceed 255 characters.',
        'description.required' => 'Please provide a description.',
        'description.max' => 'Description cannot exceed 10,000 characters.',
        'allowed_roles.required' => 'Please select at least one role.',
        'allowed_roles.min' => 'Please select at least one role.',
        'invited_user_ids.required' => 'Please invite at least one user when using invite-only mode.',
        'invited_user_ids.min' => 'Please invite at least one user when using invite-only mode.',
        'feedback_deadline.after' => 'Feedback deadline must be in the future.',
        'voting_deadline.after' => 'Voting deadline must be after the feedback deadline.',
    ];

    // ─────────────────────────────────────────────────────────────
    // COMPUTED PROPERTIES
    // ─────────────────────────────────────────────────────────────

    #[Computed]
    public function decisionTypes(): array
    {
        return [
            'democratic' => [
                'name' => 'Democratic (Majority Vote)',
                'icon' => 'hand-raised',
                'color' => 'blue',
                'votes' => ['yes', 'no', 'abstain'],
                'vote_labels' => ['Yes', 'No', 'Abstain'],
                'vote_colors' => ['yes' => 'green', 'no' => 'red', 'abstain' => 'gray'],
                'vote_icons' => ['yes' => 'check-circle', 'no' => 'x-circle', 'abstain' => 'minus-circle'],
                'short_description' => 'Majority wins. Fast and familiar.',
                'description' => 'The option with the most votes wins. Best for everyday decisions where speed matters.',
                'best_for' => 'Routine decisions, time-sensitive matters, large groups',
                'examples' => 'Event scheduling, budget approvals, operational changes',
                'considerations' => 'May leave minority voices unheard. Set appropriate pass threshold.',
            ],
            'consensus' => [
                'name' => 'Consensus',
                'icon' => 'user-group',
                'color' => 'purple',
                'votes' => ['agree', 'disagree', 'stand_aside', 'block'],
                'vote_labels' => ['Agree', 'Disagree', 'Stand Aside', 'Block'],
                'vote_colors' => ['agree' => 'green', 'disagree' => 'red', 'stand_aside' => 'yellow', 'block' => 'red'],
                'vote_icons' => ['agree' => 'check-circle', 'disagree' => 'x-circle', 'stand_aside' => 'pause-circle', 'block' => 'hand-raised'],
                'short_description' => 'Everyone agrees or stands aside.',
                'description' => 'Requires all participants to agree or "stand aside". Any member can block.',
                'best_for' => 'Major policy changes, constitutional amendments, core values',
                'examples' => 'Bylaw changes, mission statements, long-term strategic plans',
                'considerations' => 'Takes longer but builds stronger commitment. One block stops the proposal.',
            ],
            'consent' => [
                'name' => 'Consent (No Objections)',
                'icon' => 'shield-check',
                'color' => 'green',
                'votes' => ['no_objection', 'concern', 'object'],
                'vote_labels' => ['No Objection', 'Concern', 'Object'],
                'vote_colors' => ['no_objection' => 'green', 'concern' => 'yellow', 'object' => 'red'],
                'vote_icons' => ['no_objection' => 'check-circle', 'concern' => 'exclamation-circle', 'object' => 'x-circle'],
                'short_description' => '"Safe to try" if no strong objections.',
                'description' => 'Passes if no one has a principled objection. Concerns are noted but don\'t block.',
                'best_for' => 'Experimental initiatives, reversible decisions, innovation',
                'examples' => 'Pilot programs, trial periods, new committee proposals',
                'considerations' => 'Faster than consensus. Good for decisions that can be revisited.',
            ],
        ];
    }

    #[Computed]
    public function selectedDecisionType(): array
    {
        return $this->decisionTypes[$this->decision_type] ?? $this->decisionTypes['democratic'];
    }

    #[Computed]
    public function steps(): array
    {
        return [
            1 => ['title' => 'Basic Information', 'icon' => 'document-text'],
            2 => ['title' => 'Decision Settings', 'icon' => 'cog-6-tooth'],
            3 => ['title' => 'Participants', 'icon' => 'users'],
            4 => ['title' => 'Timeline & Documents', 'icon' => 'calendar'],
        ];
    }

    #[Computed]
    public function invitedUsers(): \Illuminate\Support\Collection
    {
        if (empty($this->invited_user_ids)) {
            return collect();
        }

        return User::whereIn('id', $this->invited_user_ids)->get(['id', 'name', 'email']);
    }

    // ─────────────────────────────────────────────────────────────
    // USER SEARCH
    // ─────────────────────────────────────────────────────────────

    public function searchUsers(): \Illuminate\Support\Collection
    {
        if (strlen($this->user_search) < 2) {
            return collect();
        }

        return User::query()
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->user_search . '%')
                    ->orWhere('email', 'like', '%' . $this->user_search . '%');
            })
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', $this->allowed_roles);
            })
            ->whereNotIn('id', $this->invited_user_ids)
            ->where('id', '!=', Auth::id())
            ->limit(10)
            ->get(['id', 'name', 'email']);
    }

    public function updatedUserSearch(): void
    {
        $this->showUserSearch = strlen($this->user_search) >= 2;
    }

    // ─────────────────────────────────────────────────────────────
    // STEP NAVIGATION
    // ─────────────────────────────────────────────────────────────

    public function nextStep(): void
    {
        Log::info('ProposalCreate::nextStep called', ['current_step' => $this->step]);
        
        $this->validateCurrentStep();

        if (!$this->getErrorBag()->any() && $this->step < $this->totalSteps) {
            $this->step++;
            Log::info('ProposalCreate::nextStep success', ['new_step' => $this->step]);
        } else {
            Log::warning('ProposalCreate::nextStep blocked', [
                'errors' => $this->getErrorBag()->toArray(),
                'step' => $this->step,
            ]);
        }
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
            Log::info('ProposalCreate::previousStep', ['new_step' => $this->step]);
        }
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step < $this->step) {
            $this->step = $step;
            Log::info('ProposalCreate::goToStep', ['new_step' => $this->step]);
        }
    }

    protected function validateCurrentStep(): void
    {
        $this->resetErrorBag();

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
                $rules = ['allowed_roles' => 'required|array|min:1'];
                if ($this->is_invite_only) {
                    $rules['invited_user_ids'] = 'required|array|min:1';
                }
                $this->validate($rules);
                break;
        }
    }

    // ─────────────────────────────────────────────────────────────
    // ACTIONS
    // ─────────────────────────────────────────────────────────────

    public function selectDecisionType(string $type): void
    {
        if (in_array($type, ['democratic', 'consensus', 'consent'])) {
            $this->decision_type = $type;
            Log::debug('ProposalCreate::selectDecisionType', ['type' => $type]);
        }
    }

    public function addInvitedUser(int $userId): void
    {
        if (!in_array($userId, $this->invited_user_ids)) {
            $this->invited_user_ids[] = $userId;
        }
        $this->user_search = '';
        $this->showUserSearch = false;
    }

    public function removeInvitedUser(int $userId): void
    {
        $this->invited_user_ids = array_values(
            array_filter($this->invited_user_ids, fn($id) => $id !== $userId)
        );
    }

    public function toggleInviteOnly(): void
    {
        $this->is_invite_only = !$this->is_invite_only;

        if (!$this->is_invite_only) {
            $this->invited_user_ids = [];
            $this->user_search = '';
            $this->showUserSearch = false;
        }
    }

    public function toggleRole(string $role): void
    {
        if (in_array($role, $this->allowed_roles)) {
            if (count($this->allowed_roles) > 1) {
                $this->allowed_roles = array_values(
                    array_filter($this->allowed_roles, fn($r) => $r !== $role)
                );
            }
        } else {
            $this->allowed_roles[] = $role;
        }

        if ($this->is_invite_only) {
            $this->invited_user_ids = [];
        }
    }

    public function removeDocument(int $index): void
    {
        if (isset($this->documents[$index])) {
            unset($this->documents[$index]);
            $this->documents = array_values($this->documents);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // SUBMISSION
    // ─────────────────────────────────────────────────────────────

    public function submit()
    {
        Log::info('═══════════════════════════════════════════════════════');
        Log::info('ProposalCreate::submit STARTED', [
            'user_id' => Auth::id(),
            'user_name' => Auth::user()?->name,
            'isSubmitting_before' => $this->isSubmitting,
            'step' => $this->step,
        ]);
        
        // Check if already submitting
        if ($this->isSubmitting) {
            Log::warning('ProposalCreate::submit BLOCKED - already submitting');
            return;
        }

        $this->isSubmitting = true;
        $this->resetErrorBag();

        Log::info('ProposalCreate::submit - State after initial setup', [
            'isSubmitting' => $this->isSubmitting,
            'title' => $this->title,
            'description_length' => strlen($this->description),
            'decision_type' => $this->decision_type,
            'quorum_percentage' => $this->quorum_percentage,
            'pass_threshold' => $this->pass_threshold,
            'allowed_roles' => $this->allowed_roles,
            'is_invite_only' => $this->is_invite_only,
            'invited_user_ids' => $this->invited_user_ids,
            'feedback_deadline' => $this->feedback_deadline,
            'voting_deadline' => $this->voting_deadline,
            'documents_count' => count($this->documents),
        ]);

        try {
            Log::info('ProposalCreate::submit - Starting validation');
            $this->validate();
            Log::info('ProposalCreate::submit - Validation PASSED');

            Log::info('ProposalCreate::submit - Resolving ProposalService');
            $service = app(ProposalService::class);
            Log::info('ProposalCreate::submit - ProposalService resolved: ' . get_class($service));

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
                'invited_user_ids' => $this->is_invite_only ? $this->invited_user_ids : [],
                'feedback_deadline' => $this->feedback_deadline ?: null,
                'voting_deadline' => $this->voting_deadline ?: null,
            ];

            Log::info('ProposalCreate::submit - Calling createProposal', [
                'data' => $proposalData,
                'user' => Auth::user()?->toArray(),
            ]);

            $proposal = $service->createProposal($proposalData, Auth::user());

            Log::info('ProposalCreate::submit - Proposal created', [
                'proposal_id' => $proposal->id,
                'proposal_uuid' => $proposal->uuid,
            ]);

            // Handle document uploads
            if (count($this->documents) > 0) {
                Log::info('ProposalCreate::submit - Uploading documents', [
                    'count' => count($this->documents),
                ]);
                
                foreach ($this->documents as $index => $document) {
                    Log::debug('ProposalCreate::submit - Uploading document', [
                        'index' => $index,
                        'name' => $document->getClientOriginalName(),
                        'size' => $document->getSize(),
                    ]);
                    $service->uploadDocument($proposal, Auth::user(), $document);
                }
                
                Log::info('ProposalCreate::submit - Documents uploaded successfully');
            }

            session()->flash('success', __('Proposal created successfully.'));

            $redirectUrl = route('decisions.show', ['proposal' => $proposal->uuid]);
            Log::info('ProposalCreate::submit - Redirecting', ['url' => $redirectUrl]);

            return $this->redirect($redirectUrl, navigate: true);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ProposalCreate::submit - VALIDATION FAILED', [
                'errors' => $e->errors(),
            ]);
            $this->isSubmitting = false;
            throw $e;
        } catch (\Exception $e) {
            Log::error('ProposalCreate::submit - EXCEPTION', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->isSubmitting = false;
            $this->addError('submit', __('Failed to create proposal: ') . $e->getMessage());
        }
        
        Log::info('ProposalCreate::submit ENDED');
        Log::info('═══════════════════════════════════════════════════════');
    }

    public function saveDraft()
    {
        Log::info('ProposalCreate::saveDraft STARTED', [
            'user_id' => Auth::id(),
            'isSubmitting' => $this->isSubmitting,
        ]);
        
        if ($this->isSubmitting) {
            Log::warning('ProposalCreate::saveDraft BLOCKED - already submitting');
            return;
        }

        $this->isSubmitting = true;
        $this->resetErrorBag();

        try {
            Log::info('ProposalCreate::saveDraft - Validating title only');
            $this->validate(['title' => 'required|string|max:255']);
            Log::info('ProposalCreate::saveDraft - Validation passed');

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
                'invited_user_ids' => $this->is_invite_only ? $this->invited_user_ids : [],
                'feedback_deadline' => $this->feedback_deadline ?: null,
                'voting_deadline' => $this->voting_deadline ?: null,
            ], Auth::user());

            Log::info('ProposalCreate::saveDraft - Draft created', [
                'proposal_id' => $proposal->id,
                'proposal_uuid' => $proposal->uuid,
            ]);

            foreach ($this->documents as $document) {
                $service->uploadDocument($proposal, Auth::user(), $document);
            }

            session()->flash('success', __('Draft saved successfully.'));

            return $this->redirect(route('decisions.show', ['proposal' => $proposal->uuid]), navigate: true);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ProposalCreate::saveDraft - Validation failed', ['errors' => $e->errors()]);
            $this->isSubmitting = false;
            throw $e;
        } catch (\Exception $e) {
            Log::error('ProposalCreate::saveDraft - Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->isSubmitting = false;
            $this->addError('submit', __('Failed to save draft: ') . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // RENDER
    // ─────────────────────────────────────────────────────────────

    public function render()
    {
        Log::debug('ProposalCreate::render', [
            'step' => $this->step,
            'isSubmitting' => $this->isSubmitting,
        ]);
        
        return view('livewire.decisions.proposal-create', [
            'searchedUsers' => $this->showUserSearch ? $this->searchUsers() : collect(),
        ]);
    }
}