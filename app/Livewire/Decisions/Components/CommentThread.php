<?php

namespace App\Livewire\Decisions\Components;

use App\Models\Proposal;
use App\Models\ProposalComment;
use App\Services\ProposalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CommentThread extends Component
{
    public Proposal $proposal;
    public string $newComment = '';
    public ?int $replyingTo = null;
    public string $replyContent = '';
    public ?int $editingComment = null;
    public string $editContent = '';

    protected $listeners = ['comment-added' => '$refresh'];

    public function mount(Proposal $proposal): void
    {
        $this->proposal = $proposal;
    }

    // ─────────────────────────────────────────────────────────────
    // ADD COMMENT
    // ─────────────────────────────────────────────────────────────

    public function addComment(): void
    {
        if (!$this->can_comment) {
            return;
        }

        $this->validate([
            'newComment' => 'required|string|max:5000',
        ]);

        try {
            $service = app(ProposalService::class);
            $service->addComment(
                $this->proposal,
                Auth::user(),
                $this->newComment
            );

            $this->newComment = '';
            $this->dispatch('comment-added');
            $this->dispatch('notify', type: 'success', message: 'Comment added.');
            
        } catch (\Exception $e) {
            $this->addError('newComment', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // REPLY
    // ─────────────────────────────────────────────────────────────

    public function startReply(int $commentId): void
    {
        $this->replyingTo = $commentId;
        $this->replyContent = '';
        $this->editingComment = null;
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
        $this->replyContent = '';
    }

    public function submitReply(): void
    {
        if (!$this->can_comment || !$this->replyingTo) {
            return;
        }

        $this->validate([
            'replyContent' => 'required|string|max:5000',
        ]);

        try {
            $service = app(ProposalService::class);
            $service->addComment(
                $this->proposal,
                Auth::user(),
                $this->replyContent,
                $this->replyingTo
            );

            $this->cancelReply();
            $this->dispatch('comment-added');
            $this->dispatch('notify', type: 'success', message: 'Reply added.');
            
        } catch (\Exception $e) {
            $this->addError('replyContent', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────

    public function startEdit(int $commentId): void
    {
        $comment = ProposalComment::find($commentId);
        
        if (!$comment || !$comment->canUserEdit(Auth::user())) {
            return;
        }

        $this->editingComment = $commentId;
        $this->editContent = $comment->content;
        $this->replyingTo = null;
    }

    public function cancelEdit(): void
    {
        $this->editingComment = null;
        $this->editContent = '';
    }

    public function submitEdit(): void
    {
        if (!$this->editingComment) {
            return;
        }

        $this->validate([
            'editContent' => 'required|string|max:5000',
        ]);

        try {
            $comment = ProposalComment::findOrFail($this->editingComment);
            $service = app(ProposalService::class);
            $service->updateComment($comment, Auth::user(), $this->editContent);

            $this->cancelEdit();
            $this->dispatch('notify', type: 'success', message: 'Comment updated.');
            
        } catch (\Exception $e) {
            $this->addError('editContent', $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────────────────────

    public function deleteComment(int $commentId): void
    {
        try {
            $comment = ProposalComment::findOrFail($commentId);
            $service = app(ProposalService::class);
            $service->deleteComment($comment, Auth::user());

            $this->dispatch('comment-added');
            $this->dispatch('notify', type: 'success', message: 'Comment deleted.');
            
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // COMPUTED PROPERTIES
    // ─────────────────────────────────────────────────────────────

    public function getCanCommentProperty(): bool
    {
        return $this->proposal->canUserComment(Auth::user());
    }

    public function getCommentsProperty()
    {
        return $this->proposal->comments()
            ->with(['user', 'replies.user'])
            ->get();
    }

    public function getCommentCountProperty(): int
    {
        return $this->proposal->allComments()->count();
    }

    public function render()
    {
        return view('livewire.decisions.components.comment-thread', [
            'comments' => $this->comments,
            'commentCount' => $this->comment_count,
            'canComment' => $this->can_comment,
        ]);
    }
}
