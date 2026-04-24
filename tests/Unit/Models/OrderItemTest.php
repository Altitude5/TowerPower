<?php

namespace Tests\Unit\Models;

use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns correct price as integer', function () {
    $orderItem = new OrderItem(['price' => 1250]);
    expect($orderItem->price())->toBe(1250);
});

it('calculates total price for unit quantity', function () {
    $orderItem = new OrderItem([
        'price' => 1000,
        'quantity' => '2.500',
    ]);
    expect($orderItem->totalPrice())->toBe(2500);
});

it('calculates total price for weight quantity', function () {
    $orderItem = new OrderItem([
        'price' => 500,
        'weight' => '1.750',
    ]);
    expect($orderItem->totalPrice())->toBe(875);
});

it('calculates total price for volume quantity', function () {
    $orderItem = new OrderItem([
        'price' => 2000,
        'volume' => '0.500',
    ]);
    expect($orderItem->totalPrice())->toBe(1000);
});

it('calculates total final price', function () {
    $orderItem = new OrderItem([
        'price' => 1000,
        'quantity' => '3.000',
    ]);
    expect($orderItem->totalFinalPrice())->toBe(3000);
});

it('returns total weight correctly', function () {
    $orderItem = new OrderItem(['weight' => '1.500']);
    expect($orderItem->totalWeight())->toBe('1.500');

    $noWeightItem = new OrderItem;
    expect($noWeightItem->totalWeight())->toBe('0.000');
});

it('returns total volume correctly', function () {
    $orderItem = new OrderItem(['volume' => '0.750']);
    expect($orderItem->totalVolume())->toBe('0.750');

    $noVolumeItem = new OrderItem;
    expect($noVolumeItem->totalVolume())->toBe('0.000');
});
