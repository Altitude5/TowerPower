<?php

namespace App\Policies;

use App\Models\Street;
use App\Models\User;

class StreetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Street $street): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperUser();
    }

    public function update(User $user, Street $street): bool
    {
        return $user->isSuperUser();
    }

    public function delete(User $user, Street $street): bool
    {
        return $user->isSuperUser();
    }
}
