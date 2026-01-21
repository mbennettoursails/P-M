<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\ProposalDocument;
use App\Services\ProposalService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DecisionController extends Controller
{
    public function __construct(
        protected ProposalService $proposalService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Cast a vote on a proposal
     */
    public function vote(Request $request, Proposal $proposal): JsonResponse
    {
        $request->validate([
            'vote_value' => 'required|string',
            'reason' => 'nullable|string|max:1000',
            'is_anonymous' => 'boolean',
        ]);

        try {
            $vote = $this->proposalService->castVote(
                $proposal,
                Auth::user(),
                $request->vote_value,
                $request->reason,
                $request->boolean('is_anonymous')
            );

            return response()->json([
                'success' => true,
                'message' => __('decisions.messages.vote_cast'),
                'vote' => [
                    'id' => $vote->id,
                    'value' => $vote->vote_value,
                    'label' => $vote->vote_value_label,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Advance proposal to next stage
     */
    public function advanceStage(Request $request, Proposal $proposal): JsonResponse
    {
        $request->validate([
            'stage' => 'required|string',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $success = $this->proposalService->advanceStage(
                $proposal,
                $request->stage,
                Auth::user(),
                $request->notes
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => __('decisions.messages.stage_changed'),
                    'stage' => $proposal->fresh()->current_stage,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __('decisions.errors.invalid_stage'),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Close voting on a proposal
     */
    public function close(Proposal $proposal): JsonResponse
    {
        try {
            $success = $this->proposalService->closeVoting($proposal, Auth::user());

            return response()->json([
                'success' => $success,
                'message' => __('decisions.messages.voting_closed'),
                'outcome' => $proposal->fresh()->outcome,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Withdraw a proposal
     */
    public function withdraw(Request $request, Proposal $proposal): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $success = $this->proposalService->withdraw(
                $proposal,
                Auth::user(),
                $request->reason
            );

            return response()->json([
                'success' => $success,
                'message' => __('decisions.messages.withdrawn'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Add a comment to a proposal
     */
    public function addComment(Request $request, Proposal $proposal): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|min:1|max:5000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        try {
            $comment = $this->proposalService->addComment(
                $proposal,
                Auth::user(),
                $request->content,
                $request->parent_id
            );

            return response()->json([
                'success' => true,
                'message' => $request->parent_id 
                    ? __('decisions.messages.reply_added')
                    : __('decisions.messages.comment_added'),
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => $comment->user->name,
                    'created_at' => $comment->created_at->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Upload a document to a proposal
     */
    public function uploadDocument(Request $request, Proposal $proposal): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240',
            'title' => 'nullable|string|max:255',
        ]);

        try {
            $document = $this->proposalService->uploadDocument(
                $proposal,
                $request->file('file'),
                Auth::user(),
                $request->title
            );

            return response()->json([
                'success' => true,
                'message' => __('decisions.messages.document_uploaded'),
                'document' => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'url' => $document->url,
                    'type' => $document->document_type,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a document
     */
    public function deleteDocument(ProposalDocument $document): JsonResponse
    {
        try {
            $this->proposalService->deleteDocument($document, Auth::user());

            return response()->json([
                'success' => true,
                'message' => __('decisions.messages.document_deleted'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Invite participants to a proposal
     */
    public function invite(Request $request, Proposal $proposal): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'can_vote' => 'boolean',
        ]);

        try {
            $count = $this->proposalService->inviteParticipants(
                $proposal,
                $request->user_ids,
                $request->boolean('can_vote', true)
            );

            return response()->json([
                'success' => true,
                'message' => __('decisions.messages.users_invited', ['count' => $count]),
                'invited_count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Send vote reminders
     */
    public function sendReminders(Proposal $proposal): JsonResponse
    {
        try {
            $count = $this->notificationService->sendVoteReminders($proposal);

            return response()->json([
                'success' => true,
                'message' => __('decisions.messages.reminders_sent', ['count' => $count]),
                'sent_count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
