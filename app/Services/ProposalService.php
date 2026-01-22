<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\ProposalComment;
use App\Models\ProposalDocument;
use App\Models\ProposalStage;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class ProposalService
{
    // ═══════════════════════════════════════════════════════════════
    // PROPOSAL CRUD
    // ═══════════════════════════════════════════════════════════════

    /**
     * Create a new proposal
     */
    public function createProposal(array $data, User $author): Proposal
    {
        Log::info('ProposalService::createProposal', ['author' => $author->id, 'title' => $data['title'] ?? '']);

        return DB::transaction(function () use ($data, $author) {
            $proposal = Proposal::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'decision_type' => $data['decision_type'] ?? 'democratic',
                'current_stage' => 'draft',
                'quorum_percentage' => $data['quorum_percentage'] ?? 50,
                'pass_threshold' => $data['pass_threshold'] ?? 50,
                'allow_anonymous_voting' => $data['allow_anonymous_voting'] ?? false,
                'show_results_during_voting' => $data['show_results_during_voting'] ?? true,
                'allowed_roles' => $data['allowed_roles'] ?? ['reijikai'],
                'is_invite_only' => $data['is_invite_only'] ?? false,
                'feedback_deadline' => $data['feedback_deadline'] ?? null,
                'voting_deadline' => $data['voting_deadline'] ?? null,
                'author_id' => $author->id,
            ]);

            Log::info('Proposal created', ['id' => $proposal->id, 'uuid' => $proposal->uuid]);

            // Create initial stage record
            $this->createStageRecord($proposal, 'draft', $author);

            return $proposal;
        });
    }

    /**
     * Update an existing proposal
     */
    public function updateProposal(Proposal $proposal, array $data, User $user): Proposal
    {
        // Check if user can edit
        if ($user->id !== $proposal->author_id) {
            throw new Exception('Only the author can edit this proposal.');
        }

        if (!in_array($proposal->current_stage, ['draft', 'feedback', 'refinement'])) {
            throw new Exception('Cannot edit proposal in current stage.');
        }

        $proposal->update([
            'title' => $data['title'] ?? $proposal->title,
            'description' => $data['description'] ?? $proposal->description,
            'decision_type' => $data['decision_type'] ?? $proposal->decision_type,
            'quorum_percentage' => $data['quorum_percentage'] ?? $proposal->quorum_percentage,
            'pass_threshold' => $data['pass_threshold'] ?? $proposal->pass_threshold,
            'allow_anonymous_voting' => $data['allow_anonymous_voting'] ?? $proposal->allow_anonymous_voting,
            'show_results_during_voting' => $data['show_results_during_voting'] ?? $proposal->show_results_during_voting,
            'allowed_roles' => $data['allowed_roles'] ?? $proposal->allowed_roles,
            'is_invite_only' => $data['is_invite_only'] ?? $proposal->is_invite_only,
            'feedback_deadline' => $data['feedback_deadline'] ?? $proposal->feedback_deadline,
            'voting_deadline' => $data['voting_deadline'] ?? $proposal->voting_deadline,
        ]);

        return $proposal->fresh();
    }

    /**
     * Delete a proposal (draft only)
     */
    public function deleteProposal(Proposal $proposal, User $user): bool
    {
        if ($user->id !== $proposal->author_id) {
            throw new Exception('Only the author can delete this proposal.');
        }

        if ($proposal->current_stage !== 'draft') {
            throw new Exception('Only draft proposals can be deleted.');
        }

        return $proposal->delete();
    }

    // ═══════════════════════════════════════════════════════════════
    // STAGE TRANSITIONS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Valid stage transitions
     */
    protected array $validTransitions = [
        'draft' => ['feedback', 'voting', 'withdrawn'],
        'feedback' => ['refinement', 'voting', 'withdrawn'],
        'refinement' => ['feedback', 'voting', 'withdrawn'],
        'voting' => ['closed', 'withdrawn'],
        'closed' => ['archived'],
        'archived' => [],
        'withdrawn' => [],
    ];

    /**
     * Advance proposal to next stage
     */
    public function advanceStage(Proposal $proposal, string $newStage, User $user, ?string $notes = null): bool
    {
        // Validate user permission
        if ($user->id !== $proposal->author_id) {
            throw new Exception('Only the author can change the proposal stage.');
        }

        // Validate transition
        $allowedTransitions = $this->validTransitions[$proposal->current_stage] ?? [];
        if (!in_array($newStage, $allowedTransitions)) {
            throw new Exception("Cannot transition from '{$proposal->current_stage}' to '{$newStage}'.");
        }

        return DB::transaction(function () use ($proposal, $newStage, $user, $notes) {
            // End current stage
            ProposalStage::where('proposal_id', $proposal->id)
                ->where('is_active', true)
                ->update([
                    'ended_at' => now(),
                    'is_active' => false,
                ]);

            // Create new stage record
            $this->createStageRecord($proposal, $newStage, $user, $notes);

            // Update proposal
            $updateData = ['current_stage' => $newStage];

            // Set closed_at and outcome if moving to closed
            if ($newStage === 'closed') {
                $updateData['closed_at'] = now();
                $updateData['outcome'] = $this->calculateOutcome($proposal);
            }

            $proposal->update($updateData);

            return true;
        });
    }

    /**
     * Withdraw a proposal
     */
    public function withdrawProposal(Proposal $proposal, User $user, ?string $reason = null): bool
    {
        if ($user->id !== $proposal->author_id) {
            throw new Exception('Only the author can withdraw this proposal.');
        }

        if (in_array($proposal->current_stage, ['closed', 'archived', 'withdrawn'])) {
            throw new Exception('This proposal cannot be withdrawn.');
        }

        return DB::transaction(function () use ($proposal, $user, $reason) {
            // End current stage
            ProposalStage::where('proposal_id', $proposal->id)
                ->where('is_active', true)
                ->update([
                    'ended_at' => now(),
                    'is_active' => false,
                ]);

            // Create withdrawn stage record
            $this->createStageRecord($proposal, 'withdrawn', $user, $reason);

            // Update proposal
            $proposal->update([
                'current_stage' => 'archived',
                'closed_at' => now(),
                'outcome' => 'withdrawn',
                'outcome_summary' => $reason,
            ]);

            return true;
        });
    }

    /**
     * Close voting manually
     */
    public function closeVoting(Proposal $proposal, User $user): bool
    {
        if ($proposal->current_stage !== 'voting') {
            throw new Exception('Proposal is not in voting stage.');
        }

        if ($user->id !== $proposal->author_id) {
            throw new Exception('Only the author can close voting.');
        }

        return $this->advanceStage($proposal, 'closed', $user);
    }

    /**
     * Create a stage transition record
     */
    protected function createStageRecord(Proposal $proposal, string $stage, ?User $user = null, ?string $notes = null): ProposalStage
    {
        return ProposalStage::create([
            'proposal_id' => $proposal->id,
            'stage_type' => $stage,
            'started_at' => now(),
            'is_active' => true,
            'notes' => $notes,
            'transitioned_by' => $user?->id,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // VOTING
    // ═══════════════════════════════════════════════════════════════

    /**
     * Cast a vote on a proposal
     */
    public function castVote(Proposal $proposal, User $user, string $voteValue, ?string $reason = null, bool $isAnonymous = false): Vote
    {
        // Check if voting is open
        if ($proposal->current_stage !== 'voting') {
            throw new Exception('Voting is not open for this proposal.');
        }

        // Validate vote value based on decision type
        $validVotes = $this->getValidVotes($proposal->decision_type);
        if (!in_array($voteValue, $validVotes)) {
            throw new Exception("Invalid vote value: {$voteValue}");
        }

        // Check for existing vote
        $existingVote = Vote::where('proposal_id', $proposal->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingVote) {
            // Update existing vote
            $existingVote->update([
                'previous_vote' => $existingVote->vote_value,
                'vote_value' => $voteValue,
                'reason' => $reason,
                'is_anonymous' => $isAnonymous && $proposal->allow_anonymous_voting,
                'changed_at' => now(),
            ]);
            return $existingVote->fresh();
        }

        // Create new vote
        return Vote::create([
            'proposal_id' => $proposal->id,
            'user_id' => $user->id,
            'vote_value' => $voteValue,
            'reason' => $reason,
            'is_anonymous' => $isAnonymous && $proposal->allow_anonymous_voting,
        ]);
    }

    /**
     * Remove a user's vote
     */
    public function removeVote(Proposal $proposal, User $user): bool
    {
        $vote = Vote::where('proposal_id', $proposal->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$vote) {
            return false;
        }

        if ($proposal->current_stage !== 'voting') {
            throw new Exception('Cannot remove vote - voting has ended.');
        }

        return $vote->delete();
    }

    /**
     * Get valid vote options for a decision type
     */
    protected function getValidVotes(string $decisionType): array
    {
        return match ($decisionType) {
            'democratic' => ['yes', 'no', 'abstain'],
            'consensus' => ['agree', 'disagree', 'stand_aside', 'block'],
            'consent' => ['no_objection', 'concern', 'object'],
            default => ['yes', 'no', 'abstain'],
        };
    }

    /**
     * Calculate the outcome of a proposal based on votes
     */
    public function calculateOutcome(Proposal $proposal): string
    {
        $votes = Vote::where('proposal_id', $proposal->id)->get();
        $totalVotes = $votes->count();

        // Check quorum
        // For simplicity, assume all authenticated users are eligible
        // In production, you'd check against actual eligible voters
        $quorumMet = $totalVotes >= 1; // At least one vote for POC

        if (!$quorumMet) {
            return 'no_quorum';
        }

        return match ($proposal->decision_type) {
            'democratic' => $this->calculateDemocraticOutcome($votes, $proposal->pass_threshold),
            'consensus' => $this->calculateConsensusOutcome($votes),
            'consent' => $this->calculateConsentOutcome($votes),
            default => 'no_quorum',
        };
    }

    protected function calculateDemocraticOutcome($votes, int $threshold): string
    {
        $yes = $votes->where('vote_value', 'yes')->count();
        $no = $votes->where('vote_value', 'no')->count();
        $total = $yes + $no; // Abstains don't count

        if ($total === 0) {
            return 'no_quorum';
        }

        $yesPercentage = ($yes / $total) * 100;
        return $yesPercentage >= $threshold ? 'passed' : 'rejected';
    }

    protected function calculateConsensusOutcome($votes): string
    {
        $blocks = $votes->where('vote_value', 'block')->count();
        
        if ($blocks > 0) {
            return 'blocked';
        }

        $disagrees = $votes->where('vote_value', 'disagree')->count();
        
        if ($disagrees > 0) {
            return 'rejected';
        }

        return 'passed';
    }

    protected function calculateConsentOutcome($votes): string
    {
        $objects = $votes->where('vote_value', 'object')->count();
        
        if ($objects > 0) {
            return 'blocked';
        }

        return 'passed';
    }

    // ═══════════════════════════════════════════════════════════════
    // COMMENTS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Add a comment to a proposal
     */
    public function addComment(Proposal $proposal, User $user, string $content, ?int $parentId = null): ProposalComment
    {
        // Check if commenting is allowed
        if (!in_array($proposal->current_stage, ['feedback', 'refinement', 'voting'])) {
            throw new Exception('Comments are not allowed at this stage.');
        }

        // Validate parent if provided
        if ($parentId) {
            $parent = ProposalComment::find($parentId);
            if (!$parent || $parent->proposal_id !== $proposal->id) {
                throw new Exception('Invalid parent comment.');
            }
            // Check max depth (3 levels)
            if ($parent->depth >= 2) {
                throw new Exception('Maximum comment depth reached.');
            }
        }

        return ProposalComment::create([
            'proposal_id' => $proposal->id,
            'user_id' => $user->id,
            'parent_id' => $parentId,
            'content' => $content,
        ]);
    }

    /**
     * Update a comment
     */
    public function updateComment(ProposalComment $comment, User $user, string $content): ProposalComment
    {
        // Only author can edit, within time window
        if ($comment->user_id !== $user->id) {
            throw new Exception('You can only edit your own comments.');
        }

        if ($comment->created_at->diffInMinutes(now()) > 15) {
            throw new Exception('Edit window has expired (15 minutes).');
        }

        $comment->update([
            'content' => $content,
            'edited_at' => now(),
        ]);

        return $comment->fresh();
    }

    /**
     * Delete a comment (soft delete)
     */
    public function deleteComment(ProposalComment $comment, User $user): bool
    {
        // Author or proposal author can delete
        if ($comment->user_id !== $user->id && $comment->proposal->author_id !== $user->id) {
            throw new Exception('You cannot delete this comment.');
        }

        return $comment->delete();
    }

    // ═══════════════════════════════════════════════════════════════
    // DOCUMENTS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Allowed file extensions
     */
    protected array $allowedExtensions = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'ppt', 'pptx', 'txt',
        'jpg', 'jpeg', 'png', 'gif', 'webp'
    ];

    /**
     * Upload a document to a proposal
     */
    public function uploadDocument(Proposal $proposal, User $user, UploadedFile $file, ?string $title = null): ProposalDocument
    {
        if ($user->id !== $proposal->author_id) {
            throw new Exception('Only the author can upload documents.');
        }

        // Validate file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedExtensions)) {
            throw new Exception("File type '{$extension}' is not allowed.");
        }

        // Check file size (10MB max, 5MB for images)
        $maxSize = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']) 
            ? 5 * 1024 * 1024 
            : 10 * 1024 * 1024;

        if ($file->getSize() > $maxSize) {
            throw new Exception('File size exceeds the maximum allowed.');
        }

        // Store file
        $path = $file->store("proposals/{$proposal->uuid}/documents", 'public');

        // Get next sort order
        $sortOrder = ProposalDocument::where('proposal_id', $proposal->id)->max('sort_order') ?? 0;
        $sortOrder++;

        return ProposalDocument::create([
            'proposal_id' => $proposal->id,
            'uploaded_by' => $user->id,
            'title' => $title ?? $file->getClientOriginalName(),
            'file_path' => $path,
            'document_type' => 'file',
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Delete a document
     */
    public function deleteDocument(ProposalDocument $document, User $user): bool
    {
        $proposal = $document->proposal;

        if ($user->id !== $document->uploaded_by && $user->id !== $proposal->author_id) {
            throw new Exception('You cannot delete this document.');
        }

        // Delete file from storage
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        return $document->delete();
    }

    // ═══════════════════════════════════════════════════════════════
    // DEADLINE HANDLING
    // ═══════════════════════════════════════════════════════════════

    /**
     * Process proposals with expired deadlines
     */
    public function processExpiredDeadlines(): int
    {
        $count = 0;

        // Find proposals with expired voting deadlines
        $expiredVoting = Proposal::where('current_stage', 'voting')
            ->whereNotNull('voting_deadline')
            ->where('voting_deadline', '<=', now())
            ->get();

        foreach ($expiredVoting as $proposal) {
            try {
                $this->autoCloseVoting($proposal);
                $count++;
            } catch (Exception $e) {
                Log::error("Failed to auto-close proposal {$proposal->id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Auto-close voting when deadline expires
     */
    protected function autoCloseVoting(Proposal $proposal): void
    {
        DB::transaction(function () use ($proposal) {
            // End current stage
            ProposalStage::where('proposal_id', $proposal->id)
                ->where('is_active', true)
                ->update([
                    'ended_at' => now(),
                    'is_active' => false,
                ]);

            // Create closed stage record (system-triggered)
            ProposalStage::create([
                'proposal_id' => $proposal->id,
                'stage_type' => 'closed',
                'started_at' => now(),
                'is_active' => true,
                'notes' => 'Automatically closed due to deadline.',
                'transitioned_by' => null, // System action
            ]);

            // Calculate and set outcome
            $proposal->update([
                'current_stage' => 'closed',
                'closed_at' => now(),
                'outcome' => $this->calculateOutcome($proposal),
            ]);
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // QUERY HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get proposals for a specific tab
     */
    public function getProposalsForUser(User $user, ?string $tab = null, int $perPage = 10)
    {
        $query = Proposal::with(['author', 'votes'])->latest('updated_at');

        switch ($tab) {
            case 'active':
                $query->whereIn('current_stage', ['feedback', 'refinement', 'voting']);
                break;
            case 'voting':
                $query->where('current_stage', 'voting');
                break;
            case 'needs_vote':
                $query->where('current_stage', 'voting')
                    ->whereDoesntHave('votes', fn($q) => $q->where('user_id', $user->id));
                break;
            case 'drafts':
                $query->where('current_stage', 'draft')
                    ->where('author_id', $user->id);
                break;
            case 'closed':
                $query->whereIn('current_stage', ['closed', 'archived']);
                break;
        }

        return $query->paginate($perPage);
    }

    /**
     * Get counts for each tab
     */
    public function getTabCounts(User $user): array
    {
        return [
            'active' => Proposal::whereIn('current_stage', ['feedback', 'refinement', 'voting'])->count(),
            'voting' => Proposal::where('current_stage', 'voting')->count(),
            'needs_vote' => Proposal::where('current_stage', 'voting')
                ->whereDoesntHave('votes', fn($q) => $q->where('user_id', $user->id))
                ->count(),
            'drafts' => Proposal::where('current_stage', 'draft')
                ->where('author_id', $user->id)
                ->count(),
            'closed' => Proposal::whereIn('current_stage', ['closed', 'archived'])->count(),
        ];
    }
}