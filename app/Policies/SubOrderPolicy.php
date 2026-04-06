<?php

namespace App\Policies;

use App\Models\SubOrder;
use App\Models\User;

class SubOrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SubOrder $subOrder): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $subOrder->order->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // SubOrders created via OrderService
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SubOrder $subOrder): bool
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SubOrder $subOrder): bool
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SubOrder $subOrder): bool
    {
        return $user->isSuperUser();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SubOrder $subOrder): bool
    {
        return false;
    }
}
