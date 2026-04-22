<?php

use App\Enums\SubOrderStatus;
use App\Filament\Resources\SubOrders\Pages\EditSubOrder;
use App\Filament\Resources\SubOrders\Pages\ListSubOrders;
use App\Filament\Resources\SubOrders\SubOrderResource;
use App\Models\Order;
use App\Models\Role;
use App\Models\SubOrder;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('allows super user and staff to access sub-orders list', function (string $role) {
    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user)
        ->get(SubOrderResource::getUrl('index'))
        ->assertSuccessful();
})->with([Role::ROLE_SUPER_USER, Role::ROLE_STAFF]);

it('allows customers to see only sub-orders from their own orders in the list', function () {
    $customerActor = User::factory()->create();
    $customerActor->assignRole(Role::ROLE_CUSTOMER);

    $otherCustomer = User::factory()->create();
    $otherCustomer->assignRole(Role::ROLE_CUSTOMER);

    $ownedOrder = Order::factory()->create(['user_id' => $customerActor->id]);
    $otherOrder = Order::factory()->create(['user_id' => $otherCustomer->id]);

    $ownedSubOrder = SubOrder::factory()->create(['order_id' => $ownedOrder->id]);
    $otherSubOrder = SubOrder::factory()->create(['order_id' => $otherOrder->id]);

    $this->actingAs($customerActor);

    $query = SubOrderResource::getEloquentQuery();

    expect($query->count())->toBe(1);
    expect($query->where('id', $ownedSubOrder->id)->exists())->toBeTrue();
    expect($query->where('id', $otherSubOrder->id)->exists())->toBeFalse();
});

it('allows super user to edit sub-order status', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::ROLE_SUPER_USER);

    $subOrder = SubOrder::factory()->create(['status' => SubOrderStatus::Pending]);

    $this->actingAs($super);

    Livewire::test(EditSubOrder::class, ['record' => $subOrder->id])
        ->set('data.status', SubOrderStatus::Processing->value)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($subOrder->refresh()->status)->toBe(SubOrderStatus::Processing);
});

it('disallows staff from editing sub-order status', function () {
    $staff = User::factory()->create();
    $staff->assignRole(Role::ROLE_STAFF);

    $subOrder = SubOrder::factory()->create(['status' => SubOrderStatus::Pending]);

    $this->actingAs($staff);

    // Edit action should be hidden
    Livewire::test(ListSubOrders::class)
        ->assertTableActionHidden('edit', $subOrder);
});
