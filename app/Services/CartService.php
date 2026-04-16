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
        $defaultTowerId = $user->towers()->wherePivot('is_default', true)->value('towers.id');

        return Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['tower_id' => $defaultTowerId]
        );
    }

    /**
     * Add or update a product in the cart.
     *
     * @param  array  $data  ['quantity' => 1, 'absolute' => true] or ['quantity' => 1] (default: increment)
     */
    public static function addItem(User $user, Product $product, array $data): CartItem
    {
        $isAbsolute = $data['absolute'] ?? false;

        // Relax validation: only check for required fields if it's NOT an absolute update,
        // or if we are not attempting to set the value to 0.
        if (! $isAbsolute) {
            $provided = collect($data)->only(['quantity', 'weight', 'volume'])->filter()->count();
            if ($provided !== 1) {
                throw new \InvalidArgumentException('A cart item must have exactly one of: quantity, weight, or volume.');
            }
        }

        return DB::transaction(function () use ($user, $product, $data, $isAbsolute) {
            $defaultTowerId = $user->towers()->wherePivot('is_default', true)->value('towers.id');

            $cart = Cart::where('user_id', $user->id)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['user_id' => $user->id],
                    ['tower_id' => $defaultTowerId]
                );

            if (isset($data['tower_id']) && $cart->tower_id !== (int) $data['tower_id']) {
                self::switchTower($cart, (int) $data['tower_id']);
            }

            $existingItem = $cart->items()
                ->where('product_id', $product->id)
                ->first();

            if ($existingItem) {
                $isAbsolute = $data['absolute'] ?? false;

                $newQuantity = null;
                $newWeight = null;
                $newVolume = null;

                if ($product->price_type === 'Unit') {
                    $newQuantity = $isAbsolute
                        ? (array_key_exists('quantity', $data) ? $data['quantity'] : $existingItem->quantity)
                        : bcadd($existingItem->quantity ?? '0', (string) ($data['quantity'] ?? '0'), 3);
                } elseif ($product->price_type === 'Weight') {
                    $newWeight = $isAbsolute
                        ? (array_key_exists('weight', $data) ? $data['weight'] : $existingItem->weight)
                        : bcadd($existingItem->weight ?? '0', (string) ($data['weight'] ?? '0'), 3);
                } elseif ($product->price_type === 'Volume') {
                    $newVolume = $isAbsolute
                        ? (array_key_exists('volume', $data) ? $data['volume'] : $existingItem->volume)
                        : bcadd($existingItem->volume ?? '0', (string) ($data['volume'] ?? '0'), 3);
                }

                // Check for deletion
                $shouldDelete = false;
                if ($product->price_type === 'Unit' && bccomp((string) ($newQuantity ?? '0'), '0', 3) <= 0) {
                    $shouldDelete = true;
                }
                if ($product->price_type === 'Weight' && bccomp((string) ($newWeight ?? '0'), '0', 3) <= 0) {
                    $shouldDelete = true;
                }
                if ($product->price_type === 'Volume' && bccomp((string) ($newVolume ?? '0'), '0', 3) <= 0) {
                    $shouldDelete = true;
                }

                if ($shouldDelete) {
                    $existingItem->delete();

                    return $existingItem;
                }

                $existingItem->update([
                    'quantity' => $newQuantity,
                    'weight' => $newWeight,
                    'volume' => $newVolume,
                    'price' => $product->price,
                    'price_type' => $product->price_type,
                    'product_name' => $product->name,
                ]);

                return $existingItem;
            }

            return $cart->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'price_type' => $product->price_type,
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
