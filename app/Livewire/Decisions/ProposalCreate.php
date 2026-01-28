<?php

namespace App\Livewire\Decisions;

use App\Models\Proposal;
use App\Models\User;
use App\Services\ProposalService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

#[Layout('layouts.app')]
class ProposalCreate extends Component
{
    use WithFileUploads;

    // ═══════════════════════════════════════════════════════════════════
    // STEP MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════

    #[Locked]
    public int $totalSteps = 4;

    public int $step = 1;

    // ═══════════════════════════════════════════════════════════════════
    // STEP 1: Basic Information
    // ═══════════════════════════════════════════════════════════════════

    public string $title = '';
    public string $description = '';

    // ═══════════════════════════════════════════════════════════════════
    // STEP 2: Decision Settings
    // ═══════════════════════════════════════════════════════════════════

    public string $decision_type = 'democratic';
    public int $quorum_percentage = 50;
    public int $pass_threshold = 50;
    public bool $allow_anonymous_voting = false;
    public bool $show_results_during_voting = true;

    // ═══════════════════════════════════════════════════════════════════
    // STEP 3: Participants
    // ═══════════════════════════════════════════════════════════════════

    public array $allowed_roles = ['reijikai'];
    public bool $is_invite_only = false;
    public array $invited_user_ids = [];
    public string $user_search = '';

    // ═══════════════════════════════════════════════════════════════════
    // STEP 4: Timeline & Documents
    // ═══════════════════════════════════════════════════════════════════

    public ?string $feedback_deadline = null;
    public ?string $voting_deadline = null;
    public array $documents = [];

    // ═══════════════════════════════════════════════════════════════════
    // STATIC DATA (loaded once in mount)
    // ═══════════════════════════════════════════════════════════════════

    public array $decisionTypeOptions = [];
    public array $stepDefinitions = [];
    public array $roleOptions = [];

    // ═══════════════════════════════════════════════════════════════════
    // LIFECYCLE METHODS
    // ═══════════════════════════════════════════════════════════════════

    public function mount(): void
    {
        // Initialize static data once
        $this->decisionTypeOptions = [
            'democratic' => [
                'name' => __('Democratic (Majority Vote)'),
                'name_ja' => '民主的（多数決）',
                'icon' => 'hand-raised',
                'color' => 'blue',
                'short_description' => __('Majority wins. Fast and familiar for operational decisions.'),
                'votes' => ['yes', 'no', 'abstain'],
            ],
            'consensus' => [
                'name' => __('Consensus'),
                'name_ja' => 'コンセンサス',
                'icon' => 'user-group',
                'color' => 'purple',
                'short_description' => __('Everyone agrees or stands aside. For major policy changes.'),
                'votes' => ['agree', 'disagree', 'stand_aside', 'block'],
            ],
            'consent' => [
                'name' => __('Consent (No Objections)'),
                'name_ja' => '同意（異議なし）',
                'icon' => 'shield-check',
                'color' => 'green',
                'short_description' => __('"Safe to try" if no strong objections. Good for experiments.'),
                'votes' => ['no_objection', 'concern', 'object'],
            ],
        ];

        $this->stepDefinitions = [
            1 => [
                'title' => __('Basic Information'),
                'title_ja' => '基本情報',
                'icon' => 'document-text',
                'description' => __('Title and description'),
            ],
            2 => [
                'title' => __('Decision Settings'),
                'title_ja' => '決定設定',
                'icon' => 'cog-6-tooth',
                'description' => __('How will this be decided?'),
            ],
            3 => [
                'title' => __('Participants'),
                'title_ja' => '参加者',
                'icon' => 'users',
                'description' => __('Who can participate?'),
            ],
            4 => [
                'title' => __('Review & Submit'),
                'title_ja' => '確認・提出',
                'icon' => 'check-circle',
                'description' => __('Review and create'),
            ],
        ];

        $this->roleOptions = [
            'reijikai' => [
                'name' => __('Reijikai'),
                'name_ja' => '理事会',
                'description' => __('Committee/Board members'),
                'color' => 'indigo',
            ],
            'shokuin' => [
                'name' => __('Shokuin'),
                'name_ja' => '職員',
                'description' => __('Staff members'),
                'color' => 'blue',
            ],
            'volunteer' => [
                'name' => __('Volunteers'),
                'name_ja' => 'ボランティア',
                'description' => __('General member volunteers'),
                'color' => 'emerald',
            ],
        ];

        // Set default deadlines
        $this->feedback_deadline = now()->addDays(2)->format('Y-m-d\TH:i');
        $this->voting_deadline = now()->addDays(4)->format('Y-m-d\TH:i');
    }

    // ═══════════════════════════════════════════════════════════════════
    // STEP NAVIGATION
    // ═══════════════════════════════════════════════════════════════════

    public function nextStep(): void
    {
        Log::info('ProposalCreate::nextStep called', ['current_step' => $this->step]);
        
        $this->validateCurrentStep();

        if ($this->step < $this->totalSteps) {
            $this->step++;
            Log::info('ProposalCreate::nextStep - advanced to step', ['new_step' => $this->step]);
        }
    }

    public function previousStep(): void
    {
        Log::info('ProposalCreate::previousStep called', ['current_step' => $this->step]);
        
        if ($this->step > 1) {
            $this->step--;
            Log::info('ProposalCreate::previousStep - went back to step', ['new_step' => $this->step]);
        }
    }

    public function goToStep(int $targetStep): void
    {
        Log::info('ProposalCreate::goToStep called', ['target' => $targetStep, 'current' => $this->step]);
        
        // Can only go back to completed steps
        if ($targetStep >= 1 && $targetStep < $this->step) {
            $this->step = $targetStep;
            Log::info('ProposalCreate::goToStep - changed to step', ['new_step' => $this->step]);
        }
    }
    
    /**
     * Livewire lifecycle hook - called on every request
     * Use this to debug unexpected resets
     */
    public function hydrate(): void
    {
        Log::debug('ProposalCreate::hydrate', ['step' => $this->step]);
    }
    
    public function dehydrate(): void
    {
        Log::debug('ProposalCreate::dehydrate', ['step' => $this->step]);
    }

    protected function validateCurrentStep(): void
    {
        match ($this->step) {
            1 => $this->validateStep1(),
            2 => $this->validateStep2(),
            3 => $this->validateStep3(),
            default => null,
        };
    }

    protected function validateStep1(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:10000'],
        ], [
            'title.required' => __('Please enter a title for your proposal.'),
            'title.min' => __('Title must be at least 5 characters.'),
            'title.max' => __('Title cannot exceed 255 characters.'),
            'description.required' => __('Please provide a description of your proposal.'),
            'description.min' => __('Description must be at least 20 characters to be meaningful.'),
            'description.max' => __('Description cannot exceed 10,000 characters.'),
        ]);
    }

    protected function validateStep2(): void
    {
        $this->validate([
            'decision_type' => ['required', 'in:democratic,consensus,consent'],
            'quorum_percentage' => ['required', 'integer', 'min:25', 'max:100'],
            'pass_threshold' => ['required', 'integer', 'min:50', 'max:100'],
        ], [
            'decision_type.required' => __('Please select a decision type.'),
            'decision_type.in' => __('Invalid decision type selected.'),
            'quorum_percentage.min' => __('Quorum must be at least 25%.'),
            'pass_threshold.min' => __('Pass threshold must be at least 50%.'),
        ]);
    }

    protected function validateStep3(): void
    {
        $rules = [
            'allowed_roles' => ['required', 'array', 'min:1'],
            'allowed_roles.*' => ['in:reijikai,shokuin,volunteer'],
        ];

        $messages = [
            'allowed_roles.required' => __('Please select at least one role that can participate.'),
            'allowed_roles.min' => __('Please select at least one role.'),
        ];

        if ($this->is_invite_only) {
            $rules['invited_user_ids'] = ['required', 'array', 'min:1'];
            $messages['invited_user_ids.required'] = __('Please invite at least one member for invite-only proposals.');
            $messages['invited_user_ids.min'] = __('Please invite at least one member.');
        }

        $this->validate($rules, $messages);
    }

    // ═══════════════════════════════════════════════════════════════════
    // DECISION TYPE SELECTION
    // ═══════════════════════════════════════════════════════════════════

    public function selectDecisionType(string $type): void
    {
        if (array_key_exists($type, $this->decisionTypeOptions)) {
            $this->decision_type = $type;

            // Reset pass_threshold for non-democratic types
            if ($type !== 'democratic') {
                $this->pass_threshold = 50;
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // ROLE MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════

    public function toggleRole(string $role): void
    {
        if (!array_key_exists($role, $this->roleOptions)) {
            return;
        }

        if (in_array($role, $this->allowed_roles)) {
            // Don't allow removing last role
            if (count($this->allowed_roles) > 1) {
                $this->allowed_roles = array_values(
                    array_filter($this->allowed_roles, fn($r) => $r !== $role)
                );
            }
        } else {
            $this->allowed_roles[] = $role;
        }

        // Reset invited users when roles change (they may no longer be valid)
        if ($this->is_invite_only) {
            $this->invited_user_ids = [];
            $this->user_search = '';
        }
    }

    public function toggleInviteOnly(): void
    {
        $this->is_invite_only = !$this->is_invite_only;

        if (!$this->is_invite_only) {
            $this->invited_user_ids = [];
            $this->user_search = '';
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // USER SEARCH & INVITATION
    // ═══════════════════════════════════════════════════════════════════

    #[Computed]
    public function searchResults(): Collection
    {
        if (!$this->is_invite_only || strlen($this->user_search) < 2) {
            return collect();
        }

        $query = User::query()
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->user_search . '%')
                    ->orWhere('email', 'like', '%' . $this->user_search . '%');
            })
            ->whereNotIn('id', $this->invited_user_ids)
            ->where('id', '!=', Auth::id())
            ->limit(8);

        // Filter by allowed roles if using Spatie
        try {
            $query->role($this->allowed_roles);
        } catch (\Exception $e) {
            // Role method not available, skip filtering
        }

        return $query->get(['id', 'name', 'email']);
    }

    #[Computed]
    public function invitedUsers(): Collection
    {
        if (empty($this->invited_user_ids)) {
            return collect();
        }

        return User::whereIn('id', $this->invited_user_ids)
            ->get(['id', 'name', 'email']);
    }

    public function addInvitedUser(int $userId): void
    {
        if (!in_array($userId, $this->invited_user_ids)) {
            $this->invited_user_ids[] = $userId;
        }
        $this->user_search = '';
    }

    public function removeInvitedUser(int $userId): void
    {
        $this->invited_user_ids = array_values(
            array_filter($this->invited_user_ids, fn($id) => $id !== $userId)
        );
    }

    // ═══════════════════════════════════════════════════════════════════
    // DOCUMENT MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════

    public function updatedDocuments(): void
    {
        $this->validate([
            'documents.*' => [
                'file',
                'max:10240', // 10MB
                'mimes:pdf,doc,docx,xls,xlsx,csv,ppt,pptx,txt,jpg,jpeg,png,gif,webp',
            ],
        ], [
            'documents.*.max' => __('Each file must be less than 10MB.'),
            'documents.*.mimes' => __('File type not allowed. Use PDF, Office documents, or images.'),
        ]);
    }

    public function removeDocument(int $index): void
    {
        if (isset($this->documents[$index])) {
            unset($this->documents[$index]);
            $this->documents = array_values($this->documents);
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // FORM SUBMISSION
    // ═══════════════════════════════════════════════════════════════════

    public function submit()
    {
        Log::info('ProposalCreate::submit - Starting submission', [
            'user_id' => Auth::id(),
            'title' => $this->title,
        ]);

        // Validate all steps
        $this->validate([
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:10000'],
            'decision_type' => ['required', 'in:democratic,consensus,consent'],
            'quorum_percentage' => ['required', 'integer', 'min:25', 'max:100'],
            'pass_threshold' => ['required', 'integer', 'min:50', 'max:100'],
            'allowed_roles' => ['required', 'array', 'min:1'],
            'allowed_roles.*' => ['in:reijikai,shokuin,volunteer'],
            'feedback_deadline' => ['nullable', 'date', 'after:now'],
            'voting_deadline' => ['nullable', 'date', 'after:feedback_deadline'],
            'documents.*' => ['nullable', 'file', 'max:10240'],
        ]);

        try {
            $service = app(ProposalService::class);

            // Create proposal
            $proposal = $service->createProposal([
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
            ], Auth::user());

            Log::info('ProposalCreate::submit - Proposal created', [
                'proposal_id' => $proposal->id,
                'uuid' => $proposal->uuid,
            ]);

            // Upload documents
            if (!empty($this->documents)) {
                foreach ($this->documents as $document) {
                    try {
                        $service->uploadDocument($proposal, Auth::user(), $document);
                    } catch (\Exception $e) {
                        Log::warning('ProposalCreate::submit - Document upload failed', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            session()->flash('success', __('Proposal created successfully!'));

            return $this->redirect(
                route('decisions.show', ['proposal' => $proposal->uuid]),
                navigate: true
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('ProposalCreate::submit - Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->addError('submit', __('Failed to create proposal. Please try again.'));
        }
    }

    public function saveDraft()
    {
        Log::info('ProposalCreate::saveDraft - Starting');

        // Minimal validation for draft
        $this->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
        ], [
            'title.required' => __('Please enter at least a title to save as draft.'),
            'title.min' => __('Title must be at least 3 characters.'),
        ]);

        try {
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

            // Upload any documents
            if (!empty($this->documents)) {
                foreach ($this->documents as $document) {
                    try {
                        $service->uploadDocument($proposal, Auth::user(), $document);
                    } catch (\Exception $e) {
                        Log::warning('ProposalCreate::saveDraft - Document upload failed', [
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            session()->flash('success', __('Draft saved successfully!'));

            return $this->redirect(
                route('decisions.show', ['proposal' => $proposal->uuid]),
                navigate: true
            );

        } catch (\Exception $e) {
            Log::error('ProposalCreate::saveDraft - Failed', [
                'error' => $e->getMessage(),
            ]);

            $this->addError('submit', __('Failed to save draft. Please try again.'));
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // RENDER
    // ═══════════════════════════════════════════════════════════════════

    public function render()
    {
        return view('livewire.decisions.proposal-create');
    }
}