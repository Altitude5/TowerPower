<?php

use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns correct price as integer', function () {
    $cartItem = new CartItem(['price' => 1250]); // 12.50 ILS
    expect($cartItem->price())->toBe(1250);
});

it('calculates total price for unit quantity', function () {
    $cartItem = new CartItem([
        'price' => 1000,
        'quantity' => '2.500',
    ]);

    // 1000 * 2.500 = 2500
    expect($cartItem->totalPrice())->toBe(2500);
});

it('calculates total price for weight quantity', function () {
    $cartItem = new CartItem([
        'price' => 500,
        'weight' => '1.750',
    ]);

    // 500 * 1.750 = 875
    expect($cartItem->totalPrice())->toBe(875);
});

it('calculates total price for volume quantity', function () {
    $cartItem = new CartItem([
        'price' => 2000,
        'volume' => '0.500',
    ]);

    // 2000 * 0.500 = 1000
    expect($cartItem->totalPrice())->toBe(1000);
});

it('returns zero total price if no quantity is set', function () {
    $cartItem = new CartItem(['price' => 1000]);
    expect($cartItem->totalPrice())->toBe(0);
});

it('returns zero total discount for now', function () {
    $cartItem = new CartItem;
    expect($cartItem->totalDiscount())->toBe(0);
});

it('calculates total final price', function () {
    $cartItem = new CartItem([
        'price' => 1000,
        'quantity' => '3.000',
    ]);

    // (1000 * 3) - 0 + 0 = 3000
    expect($cartItem->totalFinalPrice())->toBe(3000);
});

it('returns volume as string', function () {
    $cartItem = new CartItem(['volume' => '5.123']);
    expect($cartItem->volume())->toBe('5.123');
});
