<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a cart for a user', function () {
    $user = User::factory()->create();

    $cart = CartService::getCart($user);

    expect($cart)->toBeInstanceOf(Cart::class);
    expect($cart->user_id)->toBe($user->id);
    expect(Cart::count())->toBe(1);
});

it('snapshots product data when adding to cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'name' => 'Original Name',
        'price' => 1000,
        'price_type' => 'Unit',
    ]);

    $cartItem = CartService::addItem($user, $product, ['quantity' => 2]);

    expect($cartItem->product_name)->toBe('Original Name');
    expect($cartItem->price)->toBe(1000);
    expect($cartItem->price_type)->toBe('Unit');
    expect($cartItem->quantity)->toBe('2.000');

    // Change product data
    $product->update([
        'name' => 'New Name',
        'price' => 2000,
    ]);

    // Cart item should remain unchanged
    $cartItem->refresh();
    expect($cartItem->product_name)->toBe('Original Name');
    expect($cartItem->price)->toBe(1000);
});

it('updates quantity when adding same product twice', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price_type' => 'Unit']);

    CartService::addItem($user, $product, ['quantity' => 1]);
    CartService::addItem($user, $product, ['quantity' => 2]);

    $cart = CartService::getCart($user);
    expect($cart->items)->toHaveCount(1);
    expect($cart->items->first()->quantity)->toBe('3.000');
});

it('switches tower and clears items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $cart = CartService::getCart($user);
    CartService::addItem($user, $product, ['quantity' => 1]);

    expect($cart->items()->count())->toBe(1);

    CartService::switchTower($cart, 99);

    $cart->refresh();
    expect($cart->tower_id)->toBe(99);
    expect($cart->items()->count())->toBe(0);
});

it('calculates total price correctly for unit items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 150, // 1.50 ILS
        'price_type' => 'Unit',
    ]);

    $cartItem = CartService::addItem($user, $product, ['quantity' => 3]);

    expect($cartItem->totalPrice())->toBe(450); // 150 * 3
});

it('calculates total price correctly for weight items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 1000, // 10.00 ILS/Kg
        'price_type' => 'Weight',
    ]);

    $cartItem = CartService::addItem($user, $product, ['weight' => 1.5]);

    expect($cartItem->totalPrice())->toBe(1500); // 1000 * 1.5
});

it('persists cart between sessions', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    CartService::addItem($user, $product, ['quantity' => 5]);

    // Simulate new session/request
    $cart = Cart::where('user_id', $user->id)->first();
    expect($cart->items)->toHaveCount(1);
    expect($cart->items->first()->quantity)->toBe('5.000');
});

it('clears items without deleting the cart row', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $cart = CartService::getCart($user);
    CartService::addItem($user, $product, ['quantity' => 1]);

    CartService::clearItems($cart);

    expect(Cart::count())->toBe(1);
    expect(CartItem::count())->toBe(0);
});

it('throws an exception if more than one of quantity, weight, or volume is provided', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    CartService::addItem($user, $product, [
        'quantity' => 1,
        'weight' => 2.5,
    ]);
})->throws(InvalidArgumentException::class, 'A cart item must have exactly one of: quantity, weight, or volume.');

it('throws an exception if none of quantity, weight, or volume is provided', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    CartService::addItem($user, $product, []);
})->throws(InvalidArgumentException::class, 'A cart item must have exactly one of: quantity, weight, or volume.');
