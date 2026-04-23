<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CommentPolicy
{
    /**
     * Can the user view comments on a given commentable model?
     */
    public function viewAny(User $auth, Model $commentable): bool
    {
        return $this->canAccessCommentable($auth, $commentable);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Comment $comment): bool
    {
        return $this->canAccessCommentable($user, $comment->commentable);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Model $commentable): bool
    {
        return $this->canCommentOn($user, $commentable);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id
            && $this->canAccessCommentable($user, $comment->commentable);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        if ($comment->user_id === $user->id) {
            return $this->canAccessCommentable($user, $comment->commentable);
        }

        return $user->isSuperUser();
    }

    /**
     * SuperUser bypass.
     */
    public function before(User $user): ?bool
    {
        if ($user->isSuperUser()) {
            return true;
        }

        return null;
    }

    // --- Private helpers ---

    private function canCommentOn(User $auth, Model $commentable): bool
    {
        if ($auth->isAdmin()) {
            return true;
        }

        if ($auth->isSeller()) {
            if ($commentable instanceof Product) {
                return $commentable->shop->owner_id === $auth->id;
            }

            // For future: Schedule check
            if (get_class($commentable) === 'App\Models\Schedule') {
                return $commentable->shop->owner_id === $auth->id;
            }
        }

        if ($auth->isDeliveryPerson()) {
            // For future: Delivery check
            if (get_class($commentable) === 'App\Models\Delivery') {
                return $commentable->delivery_person_id === $auth->id;
            }
        }

        return false;
    }

    private function canAccessCommentable(User $auth, ?Model $commentable): bool
    {
        if ($commentable === null) {
            return false;
        }

        if ($auth->isAdmin()) {
            return true;
        }

        if ($auth->isSeller()) {
            if ($commentable instanceof Product) {
                return $commentable->shop->owner_id === $auth->id;
            }

            if (get_class($commentable) === 'App\Models\Schedule') {
                return $commentable->shop->owner_id === $auth->id;
            }
        }

        if ($auth->isDeliveryPerson()) {
            if (get_class($commentable) === 'App\Models\Delivery') {
                return $commentable->delivery_person_id === $auth->id;
            }
        }

        return false;
    }
}
