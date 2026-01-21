<?php

namespace App\Livewire\Decisions\Components;

use App\Models\Proposal;
use App\Models\Comment;
use App\Services\ProposalService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CommentThread extends Component
{
    public Proposal $proposal;
    public string $newComment = '';
    public ?int $replyingTo = null;
    public string $replyContent = '';
    public ?int $editingComment = null;
    public string $editContent = '';
    public string $filterStage = '';

    protected $listeners = ['comment-added' => '$refresh'];

    public function mount(Proposal $proposal) { $this->proposal = $proposal; }

    public function addComment(ProposalService $proposalService)
    {
        $this->validate(['newComment' => 'required|string|min:1|max:5000']);
        try {
            $proposalService->addComment($this->proposal, Auth::user(), $this->newComment);
            $this->newComment = '';
            $this->proposal->refresh();
            $this->dispatch('comment-added');
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.comment_added')]);
        } catch (\Exception $e) {
            $this->addError('newComment', $e->getMessage());
        }
    }

    public function startReply(int $commentId) { $this->replyingTo = $commentId; $this->replyContent = ''; }
    public function cancelReply() { $this->replyingTo = null; $this->replyContent = ''; }

    public function submitReply(ProposalService $proposalService)
    {
        if (!$this->replyingTo) return;
        $this->validate(['replyContent' => 'required|string|min:1|max:5000']);
        try {
            $proposalService->addComment($this->proposal, Auth::user(), $this->replyContent, $this->replyingTo);
            $this->replyingTo = null;
            $this->replyContent = '';
            $this->proposal->refresh();
            $this->dispatch('comment-added');
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.reply_added')]);
        } catch (\Exception $e) {
            $this->addError('replyContent', $e->getMessage());
        }
    }

    public function startEdit(int $commentId)
    {
        $comment = Comment::find($commentId);
        if (!$comment || !$comment->canBeEditedBy(Auth::user())) {
            $this->dispatch('notify', ['type' => 'error', 'message' => __('decisions.comments.cannot_edit')]);
            return;
        }
        $this->editingComment = $commentId;
        $this->editContent = $comment->content;
    }

    public function cancelEdit() { $this->editingComment = null; $this->editContent = ''; }

    public function submitEdit(ProposalService $proposalService)
    {
        if (!$this->editingComment) return;
        $this->validate(['editContent' => 'required|string|min:1|max:5000']);
        try {
            $comment = Comment::findOrFail($this->editingComment);
            $proposalService->editComment($comment, Auth::user(), $this->editContent);
            $this->editingComment = null;
            $this->editContent = '';
            $this->proposal->refresh();
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.comment_updated')]);
        } catch (\Exception $e) {
            $this->addError('editContent', $e->getMessage());
        }
    }

    public function deleteComment(int $commentId, ProposalService $proposalService)
    {
        try {
            $comment = Comment::findOrFail($commentId);
            $proposalService->deleteComment($comment, Auth::user());
            $this->proposal->refresh();
            $this->dispatch('comment-added');
            $this->dispatch('notify', ['type' => 'success', 'message' => __('decisions.messages.comment_deleted')]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function getCanCommentProperty(): bool { return $this->proposal->canUserComment(Auth::user()); }

    public function getCommentsProperty()
    {
        $query = $this->proposal->rootComments()->with(['user', 'allReplies.user']);
        if ($this->filterStage) $query->where('stage_context', $this->filterStage);
        return $query->orderBy('created_at', 'desc')->get();
    }

    public function render()
    {
        return view('livewire.decisions.components.comment-thread', [
            'canComment' => $this->can_comment,
            'comments' => $this->comments,
        ]);
    }
}
