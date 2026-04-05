<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\User;

class CartItemPolicy
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
    public function view(User $user, CartItem $cartItem): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $cartItem->cart->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only customers create cart items via service.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CartItem $cartItem): bool
    {
        // Edit CartItem V V V V X (Super User, Staff, Seller, Customer)
        if ($user->isAdmin() || $user->isSeller()) {
            return true;
        }

        return $user->id === $cartItem->cart->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CartItem $cartItem): bool
    {
        // Hard Delete CartItem V V V V V (Everyone except Delivery Person)
        if ($user->isAdmin() || $user->isSeller() || $user->id === $cartItem->cart->user_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CartItem $cartItem): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CartItem $cartItem): bool
    {
        return $this->delete($user, $cartItem);
    }
}
