<?php

namespace App\Policies;

use App\Models\CMS\Article;
use App\Models\User;

class ArticlePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('website-article-read');
    }

    /**
     * Determine whether the user can view the model.
     * 
     * IDOR Prevention: User hanya bisa melihat article jika:
     * - Administrator bisa melihat semua article
     * - User dengan permission read bisa melihat semua article
     */
    public function view(User $user, Article $article): bool
    {
        return $user->hasPermissionTo('website-article-read');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('website-article-create');
    }

    /**
     * Determine whether the user can update the model.
     * 
     * IDOR Prevention: User hanya bisa update article jika memiliki permission edit
     */
    public function update(User $user, Article $article): bool
    {
        return $user->hasPermissionTo('website-article-edit');
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * IDOR Prevention: User hanya bisa delete article jika memiliki permission delete
     */
    public function delete(User $user, Article $article): bool
    {
        return $user->hasPermissionTo('website-article-delete');
    }
}
