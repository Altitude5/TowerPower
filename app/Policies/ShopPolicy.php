<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Shop $shop): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperUser();
    }

    public function update(User $user, Shop $shop): bool
    {
        return $user->isSuperUser();
    }

    public function delete(User $user, Shop $shop): bool
    {
        return $user->isSuperUser();
    }
}
