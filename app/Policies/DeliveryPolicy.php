<?php

namespace App\Policies;

use App\Models\Delivery;
use App\Models\User;

class DeliveryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isCustomer() || $user->isDeliveryPerson();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Delivery $delivery): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $delivery->customer_id || $user->id === $delivery->delivery_person_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Delivery $delivery): bool
    {
        if ($user->isSuperUser()) {
            return true;
        }

        // Delivery person can update status and image
        if ($user->isDeliveryPerson() && $user->id === $delivery->delivery_person_id) {
            return true;
        }

        // Customer can cancel their own delivery
        if ($user->isCustomer() && $user->id === $delivery->customer_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Delivery $delivery): bool
    {
        return $user->isSuperUser();
    }
}
