<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isSeller() || $user->isDeliveryPerson();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Schedule $schedule): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isSeller()) {
            return $user->id === $schedule->shop->user_id || $schedule->shop->isOwner($user);
        }

        if ($user->isDeliveryPerson()) {
            return $user->id === $schedule->delivery_person_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSuperUser() || $user->isSeller();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Schedule $schedule): bool
    {
        if ($user->isSuperUser()) {
            return true;
        }

        if ($user->isSeller()) {
            return $schedule->shop->isOwner($user);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Schedule $schedule): bool
    {
        if ($user->isSuperUser()) {
            return true;
        }

        if ($user->isSeller()) {
            return $schedule->shop->isOwner($user);
        }

        return false;
    }
}
