<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get or create a cart for the user with a pessimistic lock.
     */
    public static function getCart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Add a product to the cart with price and name snapshots.
     */
    public static function addItem(User $user, Product $product, array $data): CartItem
    {
        // Enforce mutual exclusivity of quantity, weight, and volume
        $provided = collect($data)->only(['quantity', 'weight', 'volume'])->filter()->count();
        if ($provided !== 1) {
            throw new \InvalidArgumentException('A cart item must have exactly one of: quantity, weight, or volume.');
        }

        return DB::transaction(function () use ($user, $product, $data) {
            $cart = Cart::where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrCreate(['user_id' => $user->id]);

            // Check if tower needs switching (if tower_id is provided in data or already set)
            if (isset($data['tower_id']) && $cart->tower_id !== (int) $data['tower_id']) {
                self::switchTower($cart, (int) $data['tower_id']);
            }

            // Check if item already exists in cart
            $existingItem = $cart->items()
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => isset($data['quantity']) ? bcadd($existingItem->quantity ?? '0', (string) $data['quantity'], 3) : $existingItem->quantity,
                    'weight' => isset($data['weight']) ? bcadd($existingItem->weight ?? '0', (string) $data['weight'], 3) : $existingItem->weight,
                    'volume' => isset($data['volume']) ? bcadd($existingItem->volume ?? '0', (string) $data['volume'], 3) : $existingItem->volume,
                    // Snapshots are updated to current product state
                    'price' => $product->price,
                    'price_type' => $product->price_type,
                    'product_name' => $product->name,
                ]);

                return $existingItem;
            }

            return $cart->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,       // snapshot
                'price' => $product->price,             // snapshot
                'price_type' => $product->price_type,   // snapshot
                'quantity' => $data['quantity'] ?? null,
                'weight' => $data['weight'] ?? null,
                'volume' => $data['volume'] ?? null,
            ]);
        });
    }

    /**
     * Update a cart item's quantity, weight, or volume.
     */
    public static function updateItem(CartItem $item, array $data): bool
    {
        return DB::transaction(function () use ($item, $data) {
            // Lock the parent cart
            Cart::where('id', $item->cart_id)->lockForUpdate()->first();

            return $item->update([
                'quantity' => $data['quantity'] ?? $item->quantity,
                'weight' => $data['weight'] ?? $item->weight,
                'volume' => $data['volume'] ?? $item->volume,
            ]);
        });
    }

    /**
     * Remove an item from the cart.
     */
    public static function removeItem(CartItem $item): bool
    {
        return DB::transaction(function () use ($item) {
            Cart::where('id', $item->cart_id)->lockForUpdate()->first();

            return $item->delete();
        });
    }

    /**
     * Clear all items from the cart.
     */
    public static function clearItems(Cart $cart): void
    {
        DB::transaction(function () use ($cart) {
            $cart->lockForUpdate();
            $cart->items()->delete();
        });
    }

    /**
     * Switch the cart's tower and clear items if the tower changes.
     */
    public static function switchTower(Cart $cart, ?int $towerId): void
    {
        DB::transaction(function () use ($cart, $towerId) {
            $cart->lockForUpdate();

            if ($cart->tower_id !== $towerId) {
                $cart->items()->delete();
                $cart->tower_id = $towerId;
                $cart->save();
            }
        });
    }
}
