<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ShopPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // View All Shops: Super User, Staff
        // View Owned Shops: Seller (handled inside global query scope in Resource, but here we can return true for Sellers to see the panel)
        return $user->isAdmin() || $user->isSeller();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Shop $shop): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSeller()) {
            return $shop->isOwner($user);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Shop $shop): bool
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Shop $shop): bool
    {
        // Soft Delete Shops: SuperUser ONLY (and c1, handled by deleting event)
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Shop $shop): bool
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Shop $shop): bool
    {
        // Hard Delete Shops: SuperUser ONLY (and c1, handled by deleting event)
        return $user->isSuperUser();
    }
}
