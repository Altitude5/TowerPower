<?php

use App\Models\City;
use App\Models\Order;
use App\Models\Schedule;
use App\Models\Shop;
use App\Models\SubOrder;
use App\Models\Tower;
use App\Models\User;
use App\Services\DeliveryAssignmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new DeliveryAssignmentService;
});

it('assigns the nearest positive schedule', function () {
    $city = City::factory()->create();
    $tower = Tower::factory()->create(['city_id' => $city->id]);
    $shop = Shop::factory()->create();
    $order = Order::factory()->create(['tower_id' => $tower->id]);
    $subOrder = SubOrder::factory()->create(['order_id' => $order->id, 'shop_id' => $shop->id]);
    $deliveryPerson = User::factory()->create();

    // Create a schedule for next Wednesday
    $nextWednesday = Carbon::parse('next wednesday');
    Schedule::create([
        'shop_id' => $shop->id,
        'city_id' => $city->id,
        'delivery_person_id' => $deliveryPerson->id,
        'type' => 'positive',
        'recurrence' => 'weekly_single_day',
        'day_of_week' => Carbon::WEDNESDAY,
    ]);

    $delivery = $this->service->assignToSubOrder($subOrder);

    expect($delivery)->not->toBeNull();
    expect($delivery->date->isSameDay($nextWednesday))->toBeTrue();
    expect($delivery->delivery_person_id)->toBe($deliveryPerson->id);
});

it('prefers negative schedules as overrides', function () {
    $city = City::factory()->create();
    $tower = Tower::factory()->create(['city_id' => $city->id]);
    $shop = Shop::factory()->create();
    $order = Order::factory()->create(['tower_id' => $tower->id]);
    $subOrder = SubOrder::factory()->create(['order_id' => $order->id, 'shop_id' => $shop->id]);
    $deliveryPerson = User::factory()->create();

    // Positive: Daily
    Schedule::create([
        'shop_id' => $shop->id,
        'city_id' => $city->id,
        'delivery_person_id' => $deliveryPerson->id,
        'type' => 'positive',
        'recurrence' => 'daily',
    ]);

    // If today is Sunday, and Sunday and Monday are blocked, it should be Tuesday.
    $today = Carbon::parse('2026-04-26'); // A Sunday
    Carbon::setTestNow($today);

    // Negative: Sunday blocked
    Schedule::create([
        'shop_id' => $shop->id,
        'city_id' => $city->id,
        'delivery_person_id' => $deliveryPerson->id,
        'type' => 'negative',
        'recurrence' => 'one_time',
        'date' => $today,
    ]);

    // Negative: Monday blocked
    Schedule::create([
        'shop_id' => $shop->id,
        'city_id' => $city->id,
        'delivery_person_id' => $deliveryPerson->id,
        'type' => 'negative',
        'recurrence' => 'one_time',
        'date' => $today->copy()->addDay(),
    ]);

    $delivery = $this->service->assignToSubOrder($subOrder);

    expect($delivery->date->toDateString())->toBe($today->copy()->addDays(2)->toDateString());
});

it('returns null if no schedule matches within the window', function () {
    $city = City::factory()->create();
    $tower = Tower::factory()->create(['city_id' => $city->id]);
    $shop = Shop::factory()->create();
    $order = Order::factory()->create(['tower_id' => $tower->id]);
    $subOrder = SubOrder::factory()->create(['order_id' => $order->id, 'shop_id' => $shop->id]);

    // No schedules created

    $delivery = $this->service->assignToSubOrder($subOrder);

    expect($delivery)->toBeNull();
});

it('handles one-time positive schedules', function () {
    $city = City::factory()->create();
    $tower = Tower::factory()->create(['city_id' => $city->id]);
    $shop = Shop::factory()->create();
    $order = Order::factory()->create(['tower_id' => $tower->id]);
    $subOrder = SubOrder::factory()->create(['order_id' => $order->id, 'shop_id' => $shop->id]);
    $deliveryPerson = User::factory()->create();

    $specificDate = Carbon::today()->addDays(5);
    Schedule::create([
        'shop_id' => $shop->id,
        'city_id' => $city->id,
        'delivery_person_id' => $deliveryPerson->id,
        'type' => 'positive',
        'recurrence' => 'one_time',
        'date' => $specificDate,
    ]);

    $delivery = $this->service->assignToSubOrder($subOrder);

    expect($delivery->date->isSameDay($specificDate))->toBeTrue();
});
