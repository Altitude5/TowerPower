<?php
$user = App\Models\User::first();
$product = App\Models\Product::where('price_type', 'Unit')->first();

if (!$user || !$product) {
    echo "User or Product not found\n";
    exit;
}

echo "Testing with Product ID: {$product->id} ({$product->name})\n";

// Clear previous items
App\Models\CartItem::where('product_id', $product->id)->delete();

App\Services\CartService::addItem($user, $product, ['quantity' => 1]);
$item = App\Models\CartItem::where('product_id', $product->id)->first();
echo "Initial (add 1): " . ($item->quantity ?? 'null') . "\n";

App\Services\CartService::addItem($user, $product, ['quantity' => 1]);
$item->refresh();
echo "After add 1 more: " . ($item->quantity ?? 'null') . "\n";

App\Services\CartService::addItem($user, $product, ['quantity' => -1]);
$item->refresh();
echo "After subtract 1: " . ($item->quantity ?? 'null') . "\n";

App\Services\CartService::addItem($user, $product, ['quantity' => -1]);
$exists = App\Models\CartItem::where('product_id', $product->id)->exists();
echo "After subtract 1 more (exists?): " . ($exists ? 'Yes' : 'No') . "\n";
