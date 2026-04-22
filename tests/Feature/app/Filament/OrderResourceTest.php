<?php

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('allows super user and staff to access orders list', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(OrderResource::getUrl('index'))
        ->assertSuccessful();
})->with([Role::ROLE_SUPER_USER, Role::ROLE_STAFF]);

it('allows customers to see only their own orders in the list', function () {
    $customerActor = User::factory()->create();
    $customerActor->assignRole(Role::ROLE_CUSTOMER);

    $otherCustomer = User::factory()->create();
    $otherCustomer->assignRole(Role::ROLE_CUSTOMER);

    $ownedOrder = Order::factory()->create(['user_id' => $customerActor->id]);
    $otherOrder = Order::factory()->create(['user_id' => $otherCustomer->id]);

    $this->actingAs($customerActor);

    $query = OrderResource::getEloquentQuery();

    expect($query->count())->toBe(1);
    expect($query->where('id', $ownedOrder->id)->exists())->toBeTrue();
    expect($query->where('id', $otherOrder->id)->exists())->toBeFalse();
});

it('allows super user to edit order status', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::ROLE_SUPER_USER);

    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    $this->actingAs($super);

    Livewire::test(EditOrder::class, ['record' => $order->id])
        ->set('data.status', OrderStatus::Processing->value)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($order->refresh()->status)->toBe(OrderStatus::Processing);
});

it('disallows staff from editing order status', function () {
    $staff = User::factory()->create();
    $staff->assignRole(Role::ROLE_STAFF);

    $order = Order::factory()->create(['status' => OrderStatus::Pending]);

    $this->actingAs($staff);

    // Edit action should be hidden
    Livewire::test(ListOrders::class)
        ->assertTableActionHidden('edit', $order);
});
