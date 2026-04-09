<?php

namespace App\Policies;

use App\Models\Tower;
use App\Models\User;

class TowerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Tower $tower): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperUser();
    }

    public function update(User $user, Tower $tower): bool
    {
        return $user->isSuperUser();
    }

    public function delete(User $user, Tower $tower): bool
    {
        return $user->isSuperUser();
    }
}
