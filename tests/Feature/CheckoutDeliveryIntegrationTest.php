<?php

use App\Models\City;
use App\Models\Product;
use App\Models\Schedule;
use App\Models\Shop;
use App\Models\Tower;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a delivery record during checkout if a schedule exists', function () {
    $user = User::factory()->create();
    $city = City::factory()->create();
    $tower = Tower::factory()->create(['city_id' => $city->id]);
    $shop = Shop::factory()->create();
    $product = Product::factory()->create(['shop_id' => $shop->id, 'price' => 1000, 'stock_quantity' => 10]);
    $deliveryPerson = User::factory()->create();

    // Create a positive schedule
    Schedule::create([
        'shop_id' => $shop->id,
        'city_id' => $city->id,
        'delivery_person_id' => $deliveryPerson->id,
        'type' => 'positive',
        'recurrence' => 'daily',
    ]);

    $cart = CartService::getCart($user);
    CartService::switchTower($cart, $tower->id);
    CartService::addItem($user, $product, ['quantity' => 1]);

    $order = OrderService::checkout($user);
    $subOrder = $order->subOrders()->first();

    expect($subOrder->delivery)->not->toBeNull();
    expect($subOrder->delivery->schedule_id)->not->toBeNull();
});

it('does not create a delivery record if no schedule exists', function () {
    $user = User::factory()->create();
    $tower = Tower::factory()->create();
    $shop = Shop::factory()->create();
    $product = Product::factory()->create(['shop_id' => $shop->id, 'price' => 1000, 'stock_quantity' => 10]);

    $cart = CartService::getCart($user);
    CartService::switchTower($cart, $tower->id);
    CartService::addItem($user, $product, ['quantity' => 1]);

    $order = OrderService::checkout($user);
    $subOrder = $order->subOrders()->first();

    expect($subOrder->delivery)->toBeNull();
});
