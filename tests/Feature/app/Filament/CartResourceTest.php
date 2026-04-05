<?php

use App\Filament\Resources\Carts\CartResource;
use App\Filament\Resources\Carts\Pages\ListCarts;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('can list carts', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::ROLE_SUPER_USER);

    $cart = Cart::factory()->create();
    CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

    $this->actingAs($super);

    Livewire::test(ListCarts::class)
        ->assertCanSeeTableRecords([$cart])
        ->assertTableColumnExists('id')
        ->assertTableColumnExists('user.name')
        ->assertTableColumnExists('items_count');
});

it('allows super user to clear a cart', function () {
    $super = User::factory()->create();
    $super->assignRole(Role::ROLE_SUPER_USER);

    $cart = Cart::factory()->create();
    CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

    expect($cart->items()->count())->toBe(3);

    $this->actingAs($super);

    Livewire::test(ListCarts::class)
        ->callTableAction('clear', $cart)
        ->assertHasNoTableActionErrors();

    expect($cart->items()->count())->toBe(0);
});

it('does not allow staff to clear a cart', function () {
    $staff = User::factory()->create();
    $staff->assignRole(Role::ROLE_STAFF);

    $cart = Cart::factory()->create();
    CartItem::factory()->count(3)->create(['cart_id' => $cart->id]);

    $this->actingAs($staff);

    Livewire::test(ListCarts::class)
        ->assertTableActionHidden('clear', $cart);
});

it('denies access to unauthorized roles', function () {
    $customer = User::factory()->create();
    $customer->assignRole(Role::ROLE_CUSTOMER);

    $this->actingAs($customer)
        ->get(CartResource::getUrl('index'))
        ->assertForbidden();
});
