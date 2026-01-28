<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsPolicy
{
    use HandlesAuthorization;

    /**
     * Roles that can manage news
     */
    protected array $managerRoles = ['admin', 'shokuin', 'reijikai'];

    /**
     * Determine whether the user can view any news.
     * All authenticated users can view the news list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the news.
     * Users can view published news, or drafts they authored, or if they're managers.
     */
    public function view(User $user, News $news): bool
    {
        // Published articles are visible to all (respecting role restrictions)
        if ($news->status === 'published') {
            // Check role visibility restrictions
            if ($news->visible_to_roles && count($news->visible_to_roles) > 0) {
                return $user->hasAnyRole($news->visible_to_roles);
            }
            return true;
        }

        // Drafts/archived: only author or managers can view
        return $news->author_id === $user->id 
            || $user->hasAnyRole($this->managerRoles);
    }

    /**
     * Determine whether the user can create news.
     * Only shokuin, reijikai, and admin can create news.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole($this->managerRoles);
    }

    /**
     * Determine whether the user can update the news.
     * Author or managers can update.
     */
    public function update(User $user, News $news): bool
    {
        return $news->author_id === $user->id 
            || $user->hasAnyRole($this->managerRoles);
    }

    /**
     * Determine whether the user can delete the news.
     * Author or managers can delete.
     */
    public function delete(User $user, News $news): bool
    {
        return $news->author_id === $user->id 
            || $user->hasAnyRole($this->managerRoles);
    }

    /**
     * Determine whether the user can restore the news.
     */
    public function restore(User $user, News $news): bool
    {
        return $user->hasAnyRole($this->managerRoles);
    }

    /**
     * Determine whether the user can permanently delete the news.
     */
    public function forceDelete(User $user, News $news): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can publish news.
     */
    public function publish(User $user, News $news): bool
    {
        return $news->author_id === $user->id 
            || $user->hasAnyRole($this->managerRoles);
    }

    /**
     * Determine whether the user can pin/feature news.
     */
    public function pin(User $user, News $news): bool
    {
        return $user->hasAnyRole($this->managerRoles);
    }
}
