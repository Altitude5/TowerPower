<?php

use App\Enums\OrderStatus;
use App\Enums\SubOrderStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\SubOrder;
use App\Models\Tower;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can checkout a cart and create an order', function () {
    $user = User::factory()->create();
    $tower = Tower::factory()->create();
    $shop1 = Shop::factory()->create();
    $shop2 = Shop::factory()->create();
    
    $product1 = Product::factory()->create(['shop_id' => $shop1->id, 'price' => 1000, 'stock_quantity' => 10]);
    $product2 = Product::factory()->create(['shop_id' => $shop2->id, 'price' => 2000, 'stock_quantity' => 5]);
    
    $cart = CartService::getCart($user);
    CartService::switchTower($cart, $tower->id);
    
    CartService::addItem($user, $product1, ['quantity' => 2]);
    CartService::addItem($user, $product2, ['quantity' => 1]);
    
    $order = OrderService::checkout($user);
    
    expect($order->status)->toBe(OrderStatus::Pending);
    expect($order->user_id)->toBe($user->id);
    expect($order->tower_id)->toBe($tower->id);
    
    expect($order->subOrders)->toHaveCount(2);
    
    $subOrder1 = $order->subOrders()->where('shop_id', $shop1->id)->first();
    expect($subOrder1->status)->toBe(SubOrderStatus::Pending);
    expect($subOrder1->orderItems)->toHaveCount(1);
    expect($subOrder1->orderItems->first()->product_id)->toBe($product1->id);
    expect($subOrder1->orderItems->first()->quantity)->toBe('2.000');
    
    $subOrder2 = $order->subOrders()->where('shop_id', $shop2->id)->first();
    expect($subOrder2->status)->toBe(SubOrderStatus::Pending);
    expect($subOrder2->orderItems)->toHaveCount(1);
    expect($subOrder2->orderItems->first()->product_id)->toBe($product2->id);
    expect($subOrder2->orderItems->first()->quantity)->toBe('1.000');
    
    // Check cart cleared
    expect($cart->items()->count())->toBe(0);
    
    // Check stock decremented
    $product1->refresh();
    $product2->refresh();
    expect((float) $product1->stock_quantity)->toBe(8.0);
    expect((float) $product2->stock_quantity)->toBe(4.0);
});

it('throws exception when checking out an empty cart', function () {
    $user = User::factory()->create();
    OrderService::checkout($user);
})->throws(RuntimeException::class, 'Cart is empty.');

it('throws exception when checking out a cart with no tower', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    
    CartService::addItem($user, $product, ['quantity' => 1]);
    
    OrderService::checkout($user);
})->throws(RuntimeException::class, 'Cart has no tower assigned.');

it('automatically completes order when all suborders are completed', function () {
    $user = User::factory()->create();
    $tower = Tower::factory()->create();
    $shop = Shop::factory()->create();
    $product = Product::factory()->create(['shop_id' => $shop->id]);
    
    $cart = CartService::getCart($user);
    CartService::switchTower($cart, $tower->id);
    CartService::addItem($user, $product, ['quantity' => 1]);
    
    $order = OrderService::checkout($user);
    $subOrder = $order->subOrders->first();
    
    // Transition chain
    $subOrder->update(['status' => SubOrderStatus::Processing]);
    $subOrder->update(['status' => SubOrderStatus::OutForDelivery]);
    $subOrder->update(['status' => SubOrderStatus::Delivered]);
    
    // Set Order to Processing first (as per transition rules)
    $order->update(['status' => OrderStatus::Processing]);
    
    // Complete the suborder
    $subOrder->update(['status' => SubOrderStatus::Completed]);
    
    $order->refresh();
    expect($order->status)->toBe(OrderStatus::Completed);
});

it('respects suborder state transitions', function () {
    $subOrder = SubOrder::factory()->create(['status' => SubOrderStatus::Pending]);
    
    expect($subOrder->canTransitionTo(SubOrderStatus::Processing))->toBeTrue();
    expect($subOrder->canTransitionTo(SubOrderStatus::Cancelled))->toBeTrue();
    expect($subOrder->canTransitionTo(SubOrderStatus::Delivered))->toBeFalse();
    
    $subOrder->status = SubOrderStatus::Processing;
    expect($subOrder->canTransitionTo(SubOrderStatus::OutForDelivery))->toBeTrue();
    expect($subOrder->canTransitionTo(SubOrderStatus::Pending))->toBeFalse();
});
