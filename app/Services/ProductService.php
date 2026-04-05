<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\OrderItem;
use App\Models\Product;
use Exception;

class ProductService
{
    /**
     * Attempt to force-delete a Product.
     * Enforces the business rules: no OrderItems, no CartItems.
     *
     * @throws Exception
     */
    public function forceDelete(Product $product): bool
    {
        // OrderItem check (Unit 5 integration)
        if (class_exists(OrderItem::class) && method_exists($product, 'orderItems') && $product->orderItems()->exists()) {
            throw new Exception('Cannot permanently delete product because it has order items.');
        }

        // CartItem check (Unit 4 integration)
        if (class_exists(CartItem::class) && method_exists($product, 'cartItems') && $product->cartItems()->exists()) {
            throw new Exception('Cannot permanently delete product because it has cart items.');
        }

        return $product->forceDelete();
    }
}
