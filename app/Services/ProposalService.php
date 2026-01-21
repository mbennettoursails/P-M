<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\User;
use App\Models\Vote;
use App\Models\Comment;
use App\Models\ProposalDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;

class ProposalService
{
    public function __construct(protected NotificationService $notificationService) {}

    public function create(array $data, User $author): Proposal
    {
        return DB::transaction(function () use ($data, $author) {
            $proposal = Proposal::create([
                'title' => $data['title'],
                'title_en' => $data['title_en'] ?? null,
                'description' => $data['description'],
                'description_en' => $data['description_en'] ?? null,
                'decision_type' => $data['decision_type'],
                'quorum_percentage' => $data['quorum_percentage'] ?? 50,
                'pass_threshold' => $data['pass_threshold'] ?? 50,
                'allow_anonymous_voting' => $data['allow_anonymous_voting'] ?? false,
                'show_results_during_voting' => $data['show_results_during_voting'] ?? false,
                'allowed_roles' => $data['allowed_roles'] ?? null,
                'is_invite_only' => $data['is_invite_only'] ?? false,
                'feedback_deadline' => $data['feedback_deadline'] ?? null,
                'voting_deadline' => $data['voting_deadline'] ?? null,
                'author_id' => $author->id,
                'current_stage' => 'draft',
            ]);

            if (!empty($data['documents'])) {
                $this->attachDocuments($proposal, $data['documents'], $author);
            }

            return $proposal;
        });
    }

    public function update(Proposal $proposal, array $data, User $user): Proposal
    {
        if (!$proposal->canUserEdit($user)) {
            throw new \Exception('You do not have permission to edit this proposal.');
        }

        return DB::transaction(function () use ($proposal, $data, $user) {
            $proposal->update(array_filter([
                'title' => $data['title'] ?? null,
                'title_en' => $data['title_en'] ?? null,
                'description' => $data['description'] ?? null,
                'description_en' => $data['description_en'] ?? null,
                'decision_type' => $data['decision_type'] ?? null,
                'quorum_percentage' => $data['quorum_percentage'] ?? null,
                'pass_threshold' => $data['pass_threshold'] ?? null,
                'allow_anonymous_voting' => $data['allow_anonymous_voting'] ?? null,
                'show_results_during_voting' => $data['show_results_during_voting'] ?? null,
                'allowed_roles' => array_key_exists('allowed_roles', $data) ? $data['allowed_roles'] : null,
                'is_invite_only' => $data['is_invite_only'] ?? null,
                'feedback_deadline' => $data['feedback_deadline'] ?? null,
                'voting_deadline' => $data['voting_deadline'] ?? null,
            ], fn($v) => $v !== null));

            if (!empty($data['new_documents'])) {
                $this->attachDocuments($proposal, $data['new_documents'], $user);
            }

            return $proposal->fresh();
        });
    }

    public function advanceStage(Proposal $proposal, string $newStage, User $user, ?string $notes = null): bool
    {
        if (!$proposal->canUserAdvanceStage($user)) {
            throw new \Exception('You do not have permission to advance this proposal.');
        }

        if (!$proposal->canTransitionTo($newStage)) {
            throw new \Exception("Cannot transition from {$proposal->current_stage} to {$newStage}.");
        }

        $success = $proposal->transitionTo($newStage, $user, $notes);

        if ($success) {
            if ($newStage === 'feedback' && !$proposal->is_invite_only) {
                $this->addParticipantsByRole($proposal);
            }
            $this->notificationService->notifyStageChange($proposal, $newStage);
        }

        return $success;
    }

    public function castVote(Proposal $proposal, User $user, string $voteValue, ?string $reason = null, bool $anonymous = false): Vote
    {
        if (!$proposal->canUserVote($user)) {
            throw new \Exception('You cannot vote on this proposal.');
        }

        if (!in_array($voteValue, $proposal->vote_options)) {
            throw new \Exception('Invalid vote value.');
        }

        $existingVote = $proposal->getUserVote($user);

        if ($existingVote) {
            $existingVote->updateVote($voteValue, $reason);
            return $existingVote;
        }

        $proposal->markRespondedBy($user);

        return Vote::create([
            'proposal_id' => $proposal->id,
            'user_id' => $user->id,
            'vote_value' => $voteValue,
            'reason' => $reason,
            'is_anonymous' => $anonymous && $proposal->allow_anonymous_voting,
            'voted_at' => now(),
        ]);
    }

    public function closeVoting(Proposal $proposal, User $user): bool
    {
        if ($proposal->current_stage !== 'voting') {
            throw new \Exception('Proposal is not in voting stage.');
        }

        if (!$proposal->canUserAdvanceStage($user)) {
            throw new \Exception('You do not have permission to close voting.');
        }

        $proposal->outcome = $proposal->calculateOutcome();
        $success = $proposal->transitionTo('closed', $user);

        if ($success) {
            $this->notificationService->notifyOutcome($proposal);
        }

        return $success;
    }

    public function withdraw(Proposal $proposal, User $user, ?string $reason = null): bool
    {
        if ($proposal->author_id !== $user->id) {
            throw new \Exception('Only the author can withdraw a proposal.');
        }

        if (in_array($proposal->current_stage, ['closed', 'archived'])) {
            throw new \Exception('Cannot withdraw a closed or archived proposal.');
        }

        $proposal->outcome = 'withdrawn';
        $proposal->outcome_summary = $reason;
        
        return $proposal->transitionTo('closed', $user, $reason);
    }

    public function getVoteResults(Proposal $proposal, User $viewer): array
    {
        $canSeeResults = $proposal->show_results_during_voting 
            || $proposal->current_stage === 'closed'
            || $proposal->author_id === $viewer->id;

        if (!$canSeeResults) {
            return [
                'visible' => false,
                'total_votes' => $proposal->vote_count,
                'voter_count' => $proposal->voter_count,
                'vote_percentage' => $proposal->vote_percentage,
                'quorum_percentage' => $proposal->quorum_percentage,
                'quorum_reached' => $proposal->quorum_reached,
            ];
        }

        $distribution = $proposal->getVoteDistributionWithPercentages();

        $results = [
            'visible' => true,
            'total_votes' => $proposal->vote_count,
            'voter_count' => $proposal->voter_count,
            'vote_percentage' => $proposal->vote_percentage,
            'quorum_percentage' => $proposal->quorum_percentage,
            'quorum_reached' => $proposal->quorum_reached,
            'distribution' => $distribution,
        ];

        if ($proposal->outcome) {
            $results['outcome'] = $proposal->outcome;
            $results['outcome_config'] = $proposal->outcome_config;
        }

        return $results;
    }

    public function addParticipantsByRole(Proposal $proposal): int
    {
        $query = User::query();
        if ($proposal->allowed_roles && count($proposal->allowed_roles) > 0) {
            $query->whereIn('role', $proposal->allowed_roles);
        }

        $users = $query->get();
        $addedCount = 0;

        foreach ($users as $user) {
            if (!$proposal->isUserParticipant($user)) {
                $proposal->participants()->attach($user->id, [
                    'can_vote' => true, 'can_comment' => true, 'invited_at' => now()
                ]);
                $this->notificationService->notifyInvitation($proposal, $user);
                $addedCount++;
            }
        }

        return $addedCount;
    }

    public function inviteParticipants(Proposal $proposal, array $userIds, bool $canVote = true, bool $canComment = true): int
    {
        $invitedCount = 0;
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user && !$proposal->isUserParticipant($user)) {
                $proposal->participants()->attach($userId, [
                    'can_vote' => $canVote, 'can_comment' => $canComment, 'invited_at' => now()
                ]);
                $this->notificationService->notifyInvitation($proposal, $user);
                $invitedCount++;
            }
        }
        return $invitedCount;
    }

    public function attachDocuments(Proposal $proposal, array $documents, User $uploader): void
    {
        $maxSort = $proposal->documents()->max('sort_order') ?? -1;
        foreach ($documents as $index => $document) {
            $proposal->documents()->create([
                'uploaded_by' => $uploader->id,
                'title' => $document['title'],
                'title_en' => $document['title_en'] ?? null,
                'file_path' => $document['file_path'] ?? null,
                'external_url' => $document['external_url'] ?? null,
                'document_type' => $document['document_type'],
                'mime_type' => $document['mime_type'] ?? null,
                'file_size' => $document['file_size'] ?? null,
                'sort_order' => $maxSort + $index + 1,
            ]);
        }
    }

    public function uploadDocument(Proposal $proposal, UploadedFile $file, User $uploader, ?string $title = null): ProposalDocument
    {
        $path = $file->store('proposal-documents/' . $proposal->uuid, 'public');
        return $proposal->documents()->create([
            'uploaded_by' => $uploader->id,
            'title' => $title ?? $file->getClientOriginalName(),
            'file_path' => $path,
            'document_type' => ProposalDocument::determineType($file->getMimeType()),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'sort_order' => ($proposal->documents()->max('sort_order') ?? 0) + 1,
        ]);
    }

    public function addExternalLink(Proposal $proposal, string $url, string $title, User $uploader): ProposalDocument
    {
        return $proposal->documents()->create([
            'uploaded_by' => $uploader->id,
            'title' => $title,
            'external_url' => $url,
            'document_type' => 'link',
            'sort_order' => ($proposal->documents()->max('sort_order') ?? 0) + 1,
        ]);
    }

    public function deleteDocument(ProposalDocument $document, User $user): bool
    {
        if (!$document->canBeDeletedBy($user)) {
            throw new \Exception('You do not have permission to delete this document.');
        }
        return $document->delete();
    }

    public function addComment(Proposal $proposal, User $user, string $content, ?int $parentId = null): Comment
    {
        if (!$proposal->canUserComment($user)) {
            throw new \Exception('You cannot comment on this proposal.');
        }

        if ($parentId) {
            $parent = $proposal->comments()->find($parentId);
            if (!$parent) throw new \Exception('Parent comment not found.');
            if (!$parent->can_reply) throw new \Exception('Maximum reply depth reached.');
        }

        $comment = $proposal->comments()->create([
            'user_id' => $user->id,
            'parent_id' => $parentId,
            'content' => $content,
            'stage_context' => $proposal->current_stage,
        ]);

        $proposal->markRespondedBy($user);

        if ($parentId) {
            $this->notificationService->notifyCommentReply($comment);
        } else {
            $this->notificationService->notifyNewComment($comment);
        }

        return $comment;
    }

    public function editComment(Comment $comment, User $user, string $newContent): Comment
    {
        if (!$comment->canBeEditedBy($user)) {
            throw new \Exception('You cannot edit this comment.');
        }
        $comment->edit($newContent);
        return $comment;
    }

    public function deleteComment(Comment $comment, User $user): bool
    {
        if (!$comment->canBeDeletedBy($user)) {
            throw new \Exception('You cannot delete this comment.');
        }
        return $comment->delete();
    }

    public function getStatistics(Proposal $proposal): array
    {
        return [
            'participants' => $proposal->participant_count,
            'voters' => $proposal->voter_count,
            'votes_cast' => $proposal->vote_count,
            'vote_percentage' => $proposal->vote_percentage,
            'quorum_percentage' => $proposal->quorum_percentage,
            'quorum_reached' => $proposal->quorum_reached,
            'comments' => $proposal->comment_count,
            'documents' => $proposal->documents()->count(),
            'stage' => $proposal->current_stage,
            'stage_config' => $proposal->stage_config,
            'decision_type' => $proposal->decision_type,
            'decision_type_config' => $proposal->decision_type_config,
        ];
    }
}
