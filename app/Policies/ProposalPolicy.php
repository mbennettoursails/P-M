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
        return true; // All authenticated users can view the list
    }

    /**
     * Determine whether the user can view the proposal.
     */
    public function view(User $user, Proposal $proposal): bool
    {
        // Author can always view
        if ($proposal->author_id === $user->id) {
            return true;
        }

        // Participants can view
        if ($proposal->isUserParticipant($user)) {
            return true;
        }

        // Draft proposals are only visible to author
        if ($proposal->current_stage === 'draft') {
            return false;
        }

        // Invite-only proposals are only visible to participants
        if ($proposal->is_invite_only) {
            return false;
        }

        // Check role restrictions
        if ($proposal->allowed_roles) {
            return in_array($user->role, $proposal->allowed_roles);
        }

        return true;
    }

    /**
     * Determine whether the user can create proposals.
     */
    public function create(User $user): bool
    {
        // All authenticated users can create proposals
        return true;
    }

    /**
     * Determine whether the user can update the proposal.
     */
    public function update(User $user, Proposal $proposal): bool
    {
        // Only author can update
        if ($proposal->author_id !== $user->id) {
            return false;
        }

        // Can only update in draft, feedback, or refinement stages
        return in_array($proposal->current_stage, ['draft', 'feedback', 'refinement']);
    }

    /**
     * Determine whether the user can delete the proposal.
     */
    public function delete(User $user, Proposal $proposal): bool
    {
        // Only author can delete
        if ($proposal->author_id !== $user->id) {
            return false;
        }

        // Can only delete drafts
        return $proposal->current_stage === 'draft';
    }

    /**
     * Determine whether the user can vote on the proposal.
     */
    public function vote(User $user, Proposal $proposal): bool
    {
        return $proposal->canUserVote($user);
    }

    /**
     * Determine whether the user can comment on the proposal.
     */
    public function comment(User $user, Proposal $proposal): bool
    {
        return $proposal->canUserComment($user);
    }

    /**
     * Determine whether the user can advance the proposal stage.
     */
    public function advanceStage(User $user, Proposal $proposal): bool
    {
        return $proposal->canUserAdvanceStage($user);
    }

    /**
     * Determine whether the user can withdraw the proposal.
     */
    public function withdraw(User $user, Proposal $proposal): bool
    {
        // Only author can withdraw
        if ($proposal->author_id !== $user->id) {
            return false;
        }

        // Cannot withdraw closed or archived proposals
        return !in_array($proposal->current_stage, ['closed', 'archived']);
    }

    /**
     * Determine whether the user can invite participants.
     */
    public function invite(User $user, Proposal $proposal): bool
    {
        // Only author can invite
        return $proposal->author_id === $user->id;
    }

    /**
     * Determine whether the user can close voting.
     */
    public function closeVoting(User $user, Proposal $proposal): bool
    {
        // Only author can close voting
        if ($proposal->author_id !== $user->id) {
            return false;
        }

        return $proposal->current_stage === 'voting';
    }

    /**
     * Determine whether the user can restore the proposal.
     */
    public function restore(User $user, Proposal $proposal): bool
    {
        return $proposal->author_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the proposal.
     */
    public function forceDelete(User $user, Proposal $proposal): bool
    {
        // Only admins can force delete (you may add admin role check here)
        return false;
    }
}
