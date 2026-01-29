<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, Event $event): bool
    {
        // Published events are viewable by anyone
        if ($event->status === 'published') {
            // Check role-based visibility
            if ($event->visible_to_roles) {
                $userRoles = $user->roles->pluck('name')->toArray();
                return count(array_intersect($userRoles, $event->visible_to_roles)) > 0;
            }
            return true;
        }

        // Draft/cancelled events only viewable by creators, organizers, or admins
        return $this->canManage($user, $event);
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        // Reijikai and Shokuin can create events
        return $user->hasAnyRole(['admin', 'reijikai', 'shokuin']);
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        return $this->canManage($user, $event);
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        return $this->canManage($user, $event);
    }

    /**
     * Determine whether the user can restore the event.
     */
    public function restore(User $user, Event $event): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the event.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can register for the event.
     */
    public function register(User $user, Event $event): bool
    {
        // Can't register for unpublished events
        if ($event->status !== 'published') {
            return false;
        }

        // Can't register if already registered
        if ($event->isUserRegistered($user)) {
            return false;
        }

        // Check if registration is open
        if (!$event->is_registration_open) {
            return false;
        }

        // Check capacity (unless waitlist is enabled)
        if ($event->is_full && !$event->waitlist_enabled) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can unregister from the event.
     */
    public function unregister(User $user, Event $event): bool
    {
        // Must be registered to unregister
        $status = $event->getUserRegistrationStatus($user);
        
        return in_array($status, ['registered', 'waitlisted']);
    }

    /**
     * Determine whether the user can manage attendees.
     */
    public function manageAttendees(User $user, Event $event): bool
    {
        return $this->canManage($user, $event);
    }

    /**
     * Determine whether the user can export attendees.
     */
    public function exportAttendees(User $user, Event $event): bool
    {
        return $this->canManage($user, $event);
    }

    /**
     * Check if user can manage the event (create, update, delete)
     */
    protected function canManage(User $user, Event $event): bool
    {
        // Admins can manage all
        if ($user->hasRole('admin')) {
            return true;
        }

        // Organizer can manage their own events
        if ($event->organizer_id === $user->id) {
            return true;
        }

        // Creator can manage their own events
        if ($event->created_by === $user->id) {
            return true;
        }

        // Reijikai and Shokuin can manage all events
        return $user->hasAnyRole(['reijikai', 'shokuin']);
    }
}
