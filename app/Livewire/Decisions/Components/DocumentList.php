<?php

namespace App\Livewire\Decisions\Components;

use App\Models\Proposal;
use App\Models\ProposalDocument;
use App\Services\ProposalService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class DocumentList extends Component
{
    use WithFileUploads;

    public Proposal $proposal;
    public bool $showUploadModal = false;
    public bool $showLinkModal = false;
    public $uploadFile;
    public string $uploadTitle = '';
    public string $linkUrl = '';
    public string $linkTitle = '';

    protected $listeners = ['document-uploaded' => '$refresh'];

    public function mount(Proposal $proposal) { $this->proposal = $proposal; }

    public function openUploadModal() { $this->reset(['uploadFile', 'uploadTitle']); $this->showUploadModal = true; }
    public function closeUploadModal() { $this->showUploadModal = false; $this->reset(['uploadFile', 'uploadTitle']); }

    public function uploadDocument(ProposalService $proposalService)
    {
        $this->validate(['uploadFile' => 'required|file|max:10240', 'uploadTitle' => 'nullable|string|max:255']);
        try {
            $proposalService->uploadDocument($this->proposal, $this->uploadFile, Auth::user(), $this->uploadTitle ?: null);
            $this->proposal->refresh();
            $this->closeUploadModal();
            $this->dispatch('document-uploaded');
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.document_uploaded')]);
        } catch (\Exception $e) {
            $this->addError('uploadFile', $e->getMessage());
        }
    }

    public function openLinkModal() { $this->reset(['linkUrl', 'linkTitle']); $this->showLinkModal = true; }
    public function closeLinkModal() { $this->showLinkModal = false; $this->reset(['linkUrl', 'linkTitle']); }

    public function addLink(ProposalService $proposalService)
    {
        $this->validate(['linkUrl' => 'required|url', 'linkTitle' => 'required|string|max:255']);
        try {
            $proposalService->addExternalLink($this->proposal, $this->linkUrl, $this->linkTitle, Auth::user());
            $this->proposal->refresh();
            $this->closeLinkModal();
            $this->dispatch('document-uploaded');
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.link_added')]);
        } catch (\Exception $e) {
            $this->addError('linkUrl', $e->getMessage());
        }
    }

    public function deleteDocument(int $documentId, ProposalService $proposalService)
    {
        try {
            $document = ProposalDocument::findOrFail($documentId);
            $proposalService->deleteDocument($document, Auth::user());
            $this->proposal->refresh();
            $this->dispatch('document-uploaded');
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.document_deleted')]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getCanUploadProperty(): bool { return $this->proposal->canUserEdit(Auth::user()); }
    public function getDocumentsProperty() { return $this->proposal->documents()->with('uploader')->get(); }

    public function render()
    {
        return view('livewire.decisions.components.document-list', ['canUpload' => $this->can_upload, 'documents' => $this->documents]);
    }
}
