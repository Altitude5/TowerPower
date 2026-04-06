<?php

namespace Tests\Unit\Models;

use App\Enums\OrderStatus;
use App\Enums\SubOrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SubOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('aggregates total price from suborders', function () {
    $order = Order::factory()->create();
    $subOrder1 = SubOrder::factory()->create(['order_id' => $order->id]);
    $subOrder2 = SubOrder::factory()->create(['order_id' => $order->id]);
    
    OrderItem::factory()->create(['sub_order_id' => $subOrder1->id, 'price' => 1000, 'quantity' => 1]);
    OrderItem::factory()->create(['sub_order_id' => $subOrder2->id, 'price' => 2000, 'quantity' => 2]);

    // 1000 + 4000 = 5000
    expect($order->totalPrice())->toBe(5000);
});

it('aggregates total quantity from suborders', function () {
    $order = Order::factory()->create();
    $subOrder1 = SubOrder::factory()->create(['order_id' => $order->id]);
    $subOrder2 = SubOrder::factory()->create(['order_id' => $order->id]);
    
    OrderItem::factory()->create(['sub_order_id' => $subOrder1->id, 'quantity' => 2]);
    OrderItem::factory()->create(['sub_order_id' => $subOrder2->id, 'quantity' => 3]);

    expect($order->totalQuantity())->toBe(5);
});

it('validates state transitions', function () {
    $order = new Order(['status' => OrderStatus::Pending]);
    
    expect($order->canTransitionTo(OrderStatus::Processing))->toBeTrue();
    expect($order->canTransitionTo(OrderStatus::Cancelled))->toBeTrue();
    expect($order->canTransitionTo(OrderStatus::Completed))->toBeFalse();
    
    $order->status = OrderStatus::Processing;
    expect($order->canTransitionTo(OrderStatus::Completed))->toBeTrue();
});
