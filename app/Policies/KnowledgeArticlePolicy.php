<?php

namespace App\Policies;

use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class KnowledgeArticlePolicy
{
    use HandlesAuthorization;

    /**
     * Roles that can manage knowledge base content
     */
    protected array $managerRoles = ['admin', 'shokuin', 'reijikai'];

    /**
     * Everyone can view the knowledge base list
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Everyone can view published articles
     * Only managers can view drafts/archived
     */
    public function view(User $user, KnowledgeArticle $article): bool
    {
        if ($article->status === 'published') {
            return true;
        }

        return $article->author_id === $user->id 
            || $user->hasAnyRole($this->managerRoles);
    }

    /**
     * Only managers can create articles
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole($this->managerRoles);
    }

    /**
     * Author or managers can update
     */
    public function update(User $user, KnowledgeArticle $article): bool
    {
        return $article->author_id === $user->id 
            || $user->hasAnyRole($this->managerRoles);
    }

    /**
     * Author or managers can delete
     */
    public function delete(User $user, KnowledgeArticle $article): bool
    {
        return $article->author_id === $user->id 
            || $user->hasAnyRole($this->managerRoles);
    }

    /**
     * Only managers can restore
     */
    public function restore(User $user, KnowledgeArticle $article): bool
    {
        return $user->hasAnyRole($this->managerRoles);
    }

    /**
     * Only admin can force delete
     */
    public function forceDelete(User $user, KnowledgeArticle $article): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Author or managers can publish
     */
    public function publish(User $user, KnowledgeArticle $article): bool
    {
        return $article->author_id === $user->id 
            || $user->hasAnyRole($this->managerRoles);
    }

    /**
     * Only managers can manage attachments
     */
    public function manageAttachments(User $user, KnowledgeArticle $article): bool
    {
        return $article->author_id === $user->id 
            || $user->hasAnyRole($this->managerRoles);
    }

    /**
     * All authenticated users can give feedback
     */
    public function giveFeedback(User $user, KnowledgeArticle $article): bool
    {
        return $article->status === 'published';
    }
}
