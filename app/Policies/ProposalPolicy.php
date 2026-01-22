<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProposalPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any proposals.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the proposal.
     */
    public function view(User $user, Proposal $proposal): bool
    {
        // For now, any authenticated user can view any non-draft proposal
        // Drafts can only be viewed by the author
        if ($proposal->current_stage === 'draft') {
            return $user->id === $proposal->author_id;
        }
        
        return true;
    }

    /**
     * Determine whether the user can create proposals.
     * 
     * TODO: Change this to use roles when role system is implemented
     * return $user->hasRole('reijikai');
     */
    public function create(User $user): bool
    {
        // TEMPORARY: Allow any authenticated user to create
        // In production, this should check: return $user->hasRole('reijikai');
        return true;
    }

    /**
     * Determine whether the user can update the proposal.
     */
    public function update(User $user, Proposal $proposal): bool
    {
        // Only author can update, and only in draft/feedback/refinement stages
        if ($user->id !== $proposal->author_id) {
            return false;
        }
        
        return in_array($proposal->current_stage, ['draft', 'feedback', 'refinement']);
    }

    /**
     * Determine whether the user can delete the proposal.
     */
    public function delete(User $user, Proposal $proposal): bool
    {
        return $user->id === $proposal->author_id && $proposal->current_stage === 'draft';
    }

    /**
     * Determine whether the user can vote on the proposal.
     */
    public function vote(User $user, Proposal $proposal): bool
    {
        // Can only vote during voting stage
        if ($proposal->current_stage !== 'voting') {
            return false;
        }
        
        // Check if user hasn't already voted (or can change vote)
        return true;
    }

    /**
     * Determine whether the user can comment on the proposal.
     */
    public function comment(User $user, Proposal $proposal): bool
    {
        // Can comment on active proposals (not draft, closed, archived, or withdrawn)
        return in_array($proposal->current_stage, ['feedback', 'refinement', 'voting']);
    }

    /**
     * Determine whether the user can advance the proposal stage.
     */
    public function advanceStage(User $user, Proposal $proposal): bool
    {
        return $user->id === $proposal->author_id;
    }

    /**
     * Determine whether the user can withdraw the proposal.
     */
    public function withdraw(User $user, Proposal $proposal): bool
    {
        return $user->id === $proposal->author_id 
            && !in_array($proposal->current_stage, ['closed', 'archived', 'withdrawn']);
    }

    /**
     * Determine whether the user can upload documents.
     */
    public function uploadDocument(User $user, Proposal $proposal): bool
    {
        return $user->id === $proposal->author_id;
    }

    /**
     * Determine whether the user can manage participants.
     */
    public function manageParticipants(User $user, Proposal $proposal): bool
    {
        return $user->id === $proposal->author_id;
    }
}