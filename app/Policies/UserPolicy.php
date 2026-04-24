<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\Services\UserService;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        if ($user->isSuperUser()) {
            return true;
        }

        if ($user->isStaff()) {
            // Staff can view if target has Seller, Customer, or Delivery Person role
            // AND target does NOT have Staff or SuperUser role
            return $model->hasAnyRole([Role::ROLE_SELLER, Role::ROLE_CUSTOMER, Role::ROLE_DELIVERY_PERSON])
                && ! $model->hasAnyRole([Role::ROLE_SUPER_USER, Role::ROLE_STAFF]);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isSuperUser();
    }

    public function update(User $user, User $model): bool
    {
        return $user->isSuperUser();
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isSuperUser() && $user->id !== $model->id;
    }

    public function restore(User $user, User $model): bool
    {
        return $user->isSuperUser();
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->isSuperUser() && UserService::canHardDelete($model);
    }
}
