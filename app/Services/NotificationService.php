<?php

namespace App\Services;

use App\Models\ProposalNotification;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Comment;

class NotificationService
{
    public function notifyProposalCreated(Proposal $proposal): void
    {
        $participants = $proposal->participants()->where('user_id', '!=', $proposal->author_id)->get();
        foreach ($participants as $participant) {
            ProposalNotification::createForUser($participant, 'proposal_created', [
                'proposal_id' => $proposal->id,
                'title' => '新しい提案が作成されました',
                'title_en' => 'New proposal created',
                'message' => "「{$proposal->title}」が作成されました。",
                'message_en' => "\"{$proposal->title}\" has been created.",
                'action_url' => route('decisions.show', $proposal->uuid),
            ]);
        }
    }

    public function notifyStageChange(Proposal $proposal, string $newStage): void
    {
        $stageConfig = Proposal::STAGES[$newStage];
        $participants = $proposal->participants()->where('user_id', '!=', $proposal->author_id)->get();
        
        foreach ($participants as $participant) {
            ProposalNotification::createForUser($participant, 'proposal_stage_changed', [
                'proposal_id' => $proposal->id,
                'title' => "「{$proposal->title}」のステータスが変更されました",
                'title_en' => "Proposal \"{$proposal->title}\" status changed",
                'message' => "提案が「{$stageConfig['name_ja']}」段階に移行しました。",
                'message_en' => "The proposal has moved to the \"{$stageConfig['name']}\" stage.",
                'action_url' => route('decisions.show', $proposal->uuid),
            ]);
        }
    }

    public function notifyInvitation(Proposal $proposal, User $user): void
    {
        ProposalNotification::createForUser($user, 'invited_to_participate', [
            'proposal_id' => $proposal->id,
            'title' => '新しい提案への参加依頼',
            'title_en' => 'Invitation to participate in proposal',
            'message' => "「{$proposal->title}」への参加が依頼されました。",
            'message_en' => "You have been invited to participate in \"{$proposal->title}\".",
            'action_url' => route('decisions.show', $proposal->uuid),
        ]);
    }

    public function notifyOutcome(Proposal $proposal): void
    {
        $outcomeConfig = Proposal::OUTCOMES[$proposal->outcome] ?? null;
        if (!$outcomeConfig) return;

        foreach ($proposal->participants as $participant) {
            ProposalNotification::createForUser($participant, 'proposal_outcome', [
                'proposal_id' => $proposal->id,
                'title' => "「{$proposal->title}」の投票結果",
                'title_en' => "Voting result for \"{$proposal->title}\"",
                'message' => "結果: {$outcomeConfig['name_ja']}",
                'message_en' => "Result: {$outcomeConfig['name']}",
                'action_url' => route('decisions.show', $proposal->uuid),
            ]);
        }
    }

    public function notifyNewComment(Comment $comment): void
    {
        $proposal = $comment->proposal;
        if ($proposal->author_id !== $comment->user_id) {
            ProposalNotification::createForUser($proposal->author, 'new_comment', [
                'proposal_id' => $proposal->id,
                'title' => '新しいコメント',
                'title_en' => 'New comment',
                'message' => "{$comment->user->name}さんが「{$proposal->title}」にコメントしました。",
                'message_en' => "{$comment->user->name} commented on \"{$proposal->title}\".",
                'action_url' => route('decisions.show', $proposal->uuid) . '#comment-' . $comment->id,
            ]);
        }
    }

    public function notifyCommentReply(Comment $reply): void
    {
        $parentComment = $reply->parent;
        if (!$parentComment || $parentComment->user_id === $reply->user_id) return;

        ProposalNotification::createForUser($parentComment->user, 'comment_reply', [
            'proposal_id' => $reply->proposal_id,
            'title' => 'コメントへの返信',
            'title_en' => 'Reply to your comment',
            'message' => "{$reply->user->name}さんがあなたのコメントに返信しました。",
            'message_en' => "{$reply->user->name} replied to your comment.",
            'action_url' => route('decisions.show', $reply->proposal->uuid) . '#comment-' . $reply->id,
        ]);
    }

    public function sendDeadlineReminders(): int
    {
        $notifiedCount = 0;
        $proposals = Proposal::where('current_stage', 'voting')
            ->whereNotNull('voting_deadline')
            ->whereBetween('voting_deadline', [now(), now()->addHours(24)])
            ->get();

        foreach ($proposals as $proposal) {
            $nonVoters = $proposal->participants()
                ->wherePivot('can_vote', true)
                ->whereDoesntHave('votes', fn($q) => $q->where('proposal_id', $proposal->id))
                ->get();

            foreach ($nonVoters as $participant) {
                $existingReminder = ProposalNotification::where('user_id', $participant->id)
                    ->where('proposal_id', $proposal->id)
                    ->where('type', 'deadline_approaching')
                    ->where('created_at', '>=', now()->subHours(48))
                    ->exists();

                if (!$existingReminder) {
                    ProposalNotification::createForUser($participant, 'deadline_approaching', [
                        'proposal_id' => $proposal->id,
                        'title' => '投票期限が近づいています',
                        'title_en' => 'Voting deadline approaching',
                        'message' => "「{$proposal->title}」の投票期限まで24時間を切りました。",
                        'message_en' => "Less than 24 hours remaining to vote on \"{$proposal->title}\".",
                        'action_url' => route('decisions.show', $proposal->uuid),
                    ]);
                    $notifiedCount++;
                }
            }
        }

        return $notifiedCount;
    }

    public function sendVoteReminders(Proposal $proposal): int
    {
        if ($proposal->current_stage !== 'voting') return 0;

        $notifiedCount = 0;
        $nonVoters = $proposal->participants()
            ->wherePivot('can_vote', true)
            ->whereDoesntHave('votes', fn($q) => $q->where('proposal_id', $proposal->id))
            ->get();

        foreach ($nonVoters as $participant) {
            ProposalNotification::createForUser($participant, 'vote_reminder', [
                'proposal_id' => $proposal->id,
                'title' => '投票のお願い',
                'title_en' => 'Please vote',
                'message' => "「{$proposal->title}」への投票をお待ちしています。",
                'message_en' => "Your vote is requested on \"{$proposal->title}\".",
                'action_url' => route('decisions.show', $proposal->uuid),
            ]);
            $notifiedCount++;
        }

        return $notifiedCount;
    }

    public function getUnreadCount(User $user): int
    {
        return ProposalNotification::getUnreadCountForUser($user);
    }

    public function getRecentNotifications(User $user, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return ProposalNotification::where('user_id', $user->id)
            ->with('proposal')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function markAsRead(ProposalNotification $notification): bool
    {
        return $notification->markAsRead();
    }

    public function markAllAsRead(User $user): int
    {
        return ProposalNotification::markAllAsReadForUser($user);
    }

    public function deleteOldNotifications(int $daysOld = 90): int
    {
        return ProposalNotification::where('created_at', '<', now()->subDays($daysOld))
            ->whereNotNull('read_at')
            ->delete();
    }
}
