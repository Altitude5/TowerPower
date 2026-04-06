<?php

namespace Tests\Unit\Models;

use App\Enums\SubOrderStatus;
use App\Models\OrderItem;
use App\Models\SubOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('aggregates total price from items', function () {
    $subOrder = SubOrder::factory()->create();
    OrderItem::factory()->create(['sub_order_id' => $subOrder->id, 'price' => 1000, 'quantity' => 2, 'weight' => null, 'volume' => null]);
    OrderItem::factory()->create(['sub_order_id' => $subOrder->id, 'price' => 500, 'weight' => 1.5, 'quantity' => null, 'volume' => null]);

    // (1000 * 2) + (500 * 1.5) = 2000 + 750 = 2750
    expect($subOrder->totalPrice())->toBe(2750);
});

it('aggregates total quantity from items', function () {
    $subOrder = SubOrder::factory()->create();
    OrderItem::factory()->create(['sub_order_id' => $subOrder->id, 'quantity' => 2, 'weight' => null, 'volume' => null]);
    OrderItem::factory()->create(['sub_order_id' => $subOrder->id, 'quantity' => 3, 'weight' => null, 'volume' => null]);
    OrderItem::factory()->create(['sub_order_id' => $subOrder->id, 'weight' => 1.5, 'quantity' => null, 'volume' => null]); // quantity is null

    expect($subOrder->totalQuantity())->toBe(5);
});

it('validates state transitions', function () {
    $subOrder = new SubOrder(['status' => SubOrderStatus::Pending]);
    
    expect($subOrder->canTransitionTo(SubOrderStatus::Processing))->toBeTrue();
    expect($subOrder->canTransitionTo(SubOrderStatus::Cancelled))->toBeTrue();
    expect($subOrder->canTransitionTo(SubOrderStatus::Delivered))->toBeFalse();
    
    $subOrder->status = SubOrderStatus::Processing;
    expect($subOrder->canTransitionTo(SubOrderStatus::OutForDelivery))->toBeTrue();
    expect($subOrder->canTransitionTo(SubOrderStatus::Pending))->toBeFalse();
});
