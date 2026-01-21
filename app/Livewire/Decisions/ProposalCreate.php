<?php

namespace App\Livewire\Decisions;

use App\Models\Proposal;
use App\Models\User;
use App\Models\ProposalDocument;
use App\Services\ProposalService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class ProposalCreate extends Component
{
    use WithFileUploads;

    public int $currentStep = 1;
    public int $totalSteps = 4;

    // Step 1: Basic Info
    public string $title = '';
    public string $title_en = '';
    public string $description = '';
    public string $description_en = '';

    // Step 2: Decision Configuration
    public string $decision_type = 'democratic';
    public int $quorum_percentage = 50;
    public int $pass_threshold = 50;
    public bool $allow_anonymous_voting = false;
    public bool $show_results_during_voting = false;

    // Step 3: Participants
    public array $allowed_roles = [];
    public bool $is_invite_only = false;
    public array $invited_user_ids = [];
    public string $userSearch = '';

    // Step 4: Documents & Timeline
    public $uploadedFiles = [];
    public array $external_links = [];
    public ?string $feedback_deadline = null;
    public ?string $voting_deadline = null;

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'description' => 'required|string|min:20',
            'description_en' => 'nullable|string',
            'decision_type' => 'required|in:democratic,consensus,consent',
            'quorum_percentage' => 'required|integer|min:1|max:100',
            'pass_threshold' => 'required|integer|min:1|max:100',
            'allow_anonymous_voting' => 'boolean',
            'show_results_during_voting' => 'boolean',
            'allowed_roles' => 'array',
            'allowed_roles.*' => 'in:reijikai,shokuin,volunteer',
            'is_invite_only' => 'boolean',
            'invited_user_ids' => 'array',
            'uploadedFiles.*' => 'nullable|file|max:10240',
            'external_links.*.url' => 'nullable|url',
            'external_links.*.title' => 'nullable|string|max:255',
            'feedback_deadline' => 'nullable|date|after:now',
            'voting_deadline' => 'nullable|date|after:feedback_deadline',
        ];
    }

    protected $messages = [
        'title.required' => 'タイトルは必須です。',
        'description.required' => '説明は必須です。',
        'description.min' => '説明は20文字以上で入力してください。',
    ];

    public function nextStep()
    {
        $this->validateCurrentStep();
        if ($this->currentStep < $this->totalSteps) $this->currentStep++;
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) $this->currentStep--;
    }

    public function goToStep(int $step)
    {
        if ($step >= 1 && $step <= $this->currentStep) $this->currentStep = $step;
    }

    protected function validateCurrentStep()
    {
        $stepRules = match ($this->currentStep) {
            1 => ['title' => $this->rules()['title'], 'description' => $this->rules()['description']],
            2 => ['decision_type' => $this->rules()['decision_type'], 'quorum_percentage' => $this->rules()['quorum_percentage']],
            3 => ['allowed_roles' => $this->rules()['allowed_roles'], 'is_invite_only' => $this->rules()['is_invite_only']],
            4 => ['feedback_deadline' => $this->rules()['feedback_deadline'], 'voting_deadline' => $this->rules()['voting_deadline']],
            default => [],
        };
        $this->validate($stepRules);
    }

    public function updatedDecisionType($value)
    {
        $this->pass_threshold = ($value !== 'democratic') ? 100 : 50;
    }

    public function toggleRole(string $role)
    {
        if (in_array($role, $this->allowed_roles)) {
            $this->allowed_roles = array_values(array_diff($this->allowed_roles, [$role]));
        } else {
            $this->allowed_roles[] = $role;
        }
    }

    public function toggleUser(int $userId)
    {
        if (in_array($userId, $this->invited_user_ids)) {
            $this->invited_user_ids = array_values(array_diff($this->invited_user_ids, [$userId]));
        } else {
            $this->invited_user_ids[] = $userId;
        }
    }

    public function addExternalLink() { $this->external_links[] = ['url' => '', 'title' => '']; }
    public function removeExternalLink(int $index) { unset($this->external_links[$index]); $this->external_links = array_values($this->external_links); }
    public function removeUploadedFile(int $index) { unset($this->uploadedFiles[$index]); $this->uploadedFiles = array_values($this->uploadedFiles); }

    public function getAvailableUsersProperty()
    {
        $query = User::where('id', '!=', Auth::id());
        if (!empty($this->allowed_roles)) $query->whereIn('role', $this->allowed_roles);
        if ($this->userSearch) $query->where(fn($q) => $q->where('name', 'like', '%' . $this->userSearch . '%')->orWhere('email', 'like', '%' . $this->userSearch . '%'));
        return $query->orderBy('name')->limit(50)->get();
    }

    public function getSelectedUsersProperty()
    {
        return empty($this->invited_user_ids) ? collect([]) : User::whereIn('id', $this->invited_user_ids)->get();
    }

    public function submit(ProposalService $proposalService)
    {
        $this->validate();

        $documents = [];
        foreach ($this->uploadedFiles as $file) {
            if ($file) {
                $path = $file->store('proposal-documents', 'public');
                $documents[] = [
                    'title' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'document_type' => ProposalDocument::determineType($file->getMimeType()),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ];
            }
        }

        foreach ($this->external_links as $link) {
            if (!empty($link['url'])) {
                $documents[] = ['title' => $link['title'] ?: $link['url'], 'external_url' => $link['url'], 'document_type' => 'link'];
            }
        }

        $proposal = $proposalService->create([
            'title' => $this->title,
            'title_en' => $this->title_en ?: null,
            'description' => $this->description,
            'description_en' => $this->description_en ?: null,
            'decision_type' => $this->decision_type,
            'quorum_percentage' => $this->quorum_percentage,
            'pass_threshold' => $this->pass_threshold,
            'allow_anonymous_voting' => $this->allow_anonymous_voting,
            'show_results_during_voting' => $this->show_results_during_voting,
            'allowed_roles' => empty($this->allowed_roles) ? null : $this->allowed_roles,
            'is_invite_only' => $this->is_invite_only,
            'feedback_deadline' => $this->feedback_deadline ?: null,
            'voting_deadline' => $this->voting_deadline ?: null,
            'documents' => $documents,
        ], Auth::user());

        if ($this->is_invite_only && !empty($this->invited_user_ids)) {
            $proposalService->inviteParticipants($proposal, $this->invited_user_ids);
        }

        session()->flash('success', __('decisions.messages.created'));
        return redirect()->route('decisions.show', $proposal->uuid);
    }

    public function render()
    {
        return view('livewire.decisions.proposal-create', [
            'decisionTypes' => Proposal::DECISION_TYPES,
            'availableUsers' => $this->available_users,
            'selectedUsers' => $this->selected_users,
            'roles' => [
                'reijikai' => ['name' => '委員会', 'name_en' => 'Committee'],
                'shokuin' => ['name' => '職員', 'name_en' => 'Staff'],
                'volunteer' => ['name' => 'ボランティア', 'name_en' => 'Volunteer'],
            ],
        ])->layout('layouts.app', ['title' => __('decisions.create.title')]);
    }
}
