<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Role $role): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperUser();
    }

    public function update(User $user, Role $role): bool
    {
        return $user->isSuperUser();
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->isSuperUser();
    }

    public function restore(User $user, Role $role): bool
    {
        return $user->isSuperUser();
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return $user->isSuperUser();
    }
}
