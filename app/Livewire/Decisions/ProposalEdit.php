<?php

namespace App\Livewire\Decisions;

use App\Models\Proposal;
use App\Services\ProposalService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class ProposalEdit extends Component
{
    use WithFileUploads;

    public Proposal $proposal;

    // Form fields
    public string $title = '';
    public string $title_en = '';
    public string $description = '';
    public string $description_en = '';
    public string $decision_type = 'democratic';
    public int $quorum_percentage = 50;
    public int $pass_threshold = 50;
    public bool $allow_anonymous_voting = false;
    public bool $show_results_during_voting = false;
    public array $allowed_roles = [];
    public bool $is_invite_only = false;
    public ?string $feedback_deadline = null;
    public ?string $voting_deadline = null;

    // File uploads
    public $newDocuments = [];
    public array $externalLinks = [];

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
            'is_invite_only' => 'boolean',
            'feedback_deadline' => 'nullable|date',
            'voting_deadline' => 'nullable|date',
        ];
    }

    public function mount(Proposal $proposal)
    {
        // Check authorization
        if (!$proposal->canUserEdit(Auth::user())) {
            abort(403, __('decisions.errors.cannot_edit'));
        }

        $this->proposal = $proposal;
        
        // Load existing values
        $this->title = $proposal->title;
        $this->title_en = $proposal->title_en ?? '';
        $this->description = $proposal->description;
        $this->description_en = $proposal->description_en ?? '';
        $this->decision_type = $proposal->decision_type;
        $this->quorum_percentage = $proposal->quorum_percentage;
        $this->pass_threshold = $proposal->pass_threshold;
        $this->allow_anonymous_voting = $proposal->allow_anonymous_voting;
        $this->show_results_during_voting = $proposal->show_results_during_voting;
        $this->allowed_roles = $proposal->allowed_roles ?? [];
        $this->is_invite_only = $proposal->is_invite_only;
        $this->feedback_deadline = $proposal->feedback_deadline?->format('Y-m-d\TH:i');
        $this->voting_deadline = $proposal->voting_deadline?->format('Y-m-d\TH:i');
    }

    public function updatedDecisionType($value)
    {
        if ($value !== 'democratic') {
            $this->pass_threshold = 100;
        } else {
            $this->pass_threshold = 50;
        }
    }

    public function toggleRole(string $role)
    {
        if (in_array($role, $this->allowed_roles)) {
            $this->allowed_roles = array_values(array_diff($this->allowed_roles, [$role]));
        } else {
            $this->allowed_roles[] = $role;
        }
    }

    public function addExternalLink()
    {
        $this->externalLinks[] = ['url' => '', 'title' => ''];
    }

    public function removeExternalLink(int $index)
    {
        unset($this->externalLinks[$index]);
        $this->externalLinks = array_values($this->externalLinks);
    }

    public function save(ProposalService $proposalService)
    {
        $this->validate();

        try {
            // Prepare documents
            $documents = [];

            foreach ($this->newDocuments as $file) {
                if ($file) {
                    $path = $file->store('proposal-documents', 'public');
                    $documents[] = [
                        'title' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'document_type' => \App\Models\ProposalDocument::determineType($file->getMimeType()),
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ];
                }
            }

            foreach ($this->externalLinks as $link) {
                if (!empty($link['url'])) {
                    $documents[] = [
                        'title' => $link['title'] ?: $link['url'],
                        'external_url' => $link['url'],
                        'document_type' => 'link',
                    ];
                }
            }

            $proposalService->update($this->proposal, [
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
                'new_documents' => $documents,
            ], Auth::user());

            session()->flash('success', __('decisions.messages.updated'));

            return redirect()->route('decisions.show', $this->proposal->uuid);

        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function cancel()
    {
        return redirect()->route('decisions.show', $this->proposal->uuid);
    }

    public function getCanChangeDecisionTypeProperty(): bool
    {
        // Can only change decision type in draft stage
        return $this->proposal->current_stage === 'draft';
    }

    public function render()
    {
        return view('livewire.decisions.proposal-edit', [
            'decisionTypes' => Proposal::DECISION_TYPES,
            'canChangeDecisionType' => $this->can_change_decision_type,
            'roles' => [
                'reijikai' => ['name' => '委員会', 'name_en' => 'Committee'],
                'shokuin' => ['name' => '職員', 'name_en' => 'Staff'],
                'volunteer' => ['name' => 'ボランティア', 'name_en' => 'Volunteer'],
            ],
        ])->layout('layouts.app', ['title' => __('decisions.actions.edit') . ': ' . $this->proposal->title]);
    }
}
