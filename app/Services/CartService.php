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

        // Validation: If amount is provided, ensure only one is present.
        if (! $isAbsolute) {
            $providedKeys = collect($data)->only(['quantity', 'weight', 'volume'])->filter(fn ($v) => ! is_null($v))->keys();
            if ($providedKeys->count() > 1) {
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
                $newQuantity = null;
                $newWeight = null;
                $newVolume = null;

                if ($product->price_type === 'Unit') {
                    $increment = isset($data['quantity']) ? (string) $data['quantity'] : '1';
                    $newQuantity = $isAbsolute ? $increment : bcadd($existingItem->quantity ?? '0', $increment, 3);
                } elseif ($product->price_type === 'Weight') {
                    $increment = isset($data['weight']) ? (string) $data['weight'] : '0.25';
                    $newWeight = $isAbsolute ? $increment : bcadd($existingItem->weight ?? '0', $increment, 3);
                } elseif ($product->price_type === 'Volume') {
                    $increment = isset($data['volume']) ? (string) $data['volume'] : '0.25';
                    $newVolume = $isAbsolute ? $increment : bcadd($existingItem->volume ?? '0', $increment, 3);
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
                    'category_name' => $product->category?->name,
                    'shop_name' => $product->shop->name,
                ]);

                return $existingItem;
            }

            // Creating new item
            $quantity = null;
            $weight = null;
            $volume = null;

            if ($product->price_type === 'Unit') {
                $quantity = isset($data['quantity']) ? $data['quantity'] : 1;
            } elseif ($product->price_type === 'Weight') {
                $weight = isset($data['weight']) ? $data['weight'] : 0.5;
            } elseif ($product->price_type === 'Volume') {
                $volume = isset($data['volume']) ? $data['volume'] : 0.5;
            }

            return $cart->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'category_name' => $product->category?->name,
                'shop_name' => $product->shop->name,
                'price' => $product->price,
                'price_type' => $product->price_type,
                'quantity' => $quantity,
                'weight' => $weight,
                'volume' => $volume,
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
