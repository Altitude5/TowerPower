<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\SubOrderStatus;
use App\Enums\TransactionStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SubOrder;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Checkout the user's cart and create an order.
     */
    public static function checkout(User $user): Order
    {
        return DB::transaction(function () use ($user) {
            $cart = Cart::where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $cart || $cart->items()->count() === 0) {
                throw new \RuntimeException('Cart is empty.');
            }

            if ($cart->tower_id === null) {
                throw new \RuntimeException('Cart has no tower assigned.');
            }

            // Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'tower_id' => $cart->tower_id,
                'status' => OrderStatus::Pending,
            ]);

            // Group items by shop_id
            $itemsByShop = $cart->items->groupBy(fn ($item) => $item->product->shop_id);

            foreach ($itemsByShop as $shopId => $cartItems) {
                // Create SubOrder
                $subOrder = SubOrder::create([
                    'order_id' => $order->id,
                    'shop_id' => $shopId,
                    'status' => SubOrderStatus::Pending,
                ]);

                foreach ($cartItems as $cartItem) {
                    $product = Product::where('id', $cartItem->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $product->available()) {
                        throw new \RuntimeException("Product {$product->name} is not available.");
                    }

                    // Stock validation
                    if ($product->stock_quantity !== null && (float) $product->stock_quantity < (float) $cartItem->quantity) {
                        throw new InsufficientStockException($product);
                    }
                    if ($product->stock_weight !== null && (float) $product->stock_weight < (float) $cartItem->weight) {
                        throw new InsufficientStockException($product);
                    }
                    if ($product->stock_volume !== null && (float) $product->stock_volume < (float) $cartItem->volume) {
                        throw new InsufficientStockException($product);
                    }

                    // Create OrderItem
                    OrderItem::create([
                        'sub_order_id' => $subOrder->id,
                        'product_id' => $cartItem->product_id,
                        'product_name' => $cartItem->product_name,
                        'price' => $cartItem->price,
                        'price_type' => $cartItem->price_type,
                        'quantity' => $cartItem->quantity,
                        'weight' => $cartItem->weight,
                        'volume' => $cartItem->volume,
                    ]);

                    // Decrement stock
                    if ($product->stock_quantity !== null) {
                        $product->decrement('stock_quantity', (float) $cartItem->quantity);
                    }
                    if ($product->stock_weight !== null) {
                        $product->decrement('stock_weight', (float) $cartItem->weight);
                    }
                    if ($product->stock_volume !== null) {
                        $product->decrement('stock_volume', (float) $cartItem->volume);
                    }
                }
            }

            // Create Transaction
            Transaction::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'amount' => $order->totalFinalPrice(),
                'status' => TransactionStatus::Pending,
                'currency' => 'ILS',
                'gateway' => config('payments.default_gateway'),
                'transaction_reference' => null,
            ]);

            // Clear CartItems
            $cart->items()->delete();

            return $order;
        });
    }
}
