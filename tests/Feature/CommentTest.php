<?php

use App\Models\Comment;
use App\Models\Product;
use App\Models\Role;
use App\Models\Shop;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

it('allows sellers to comment on products from their own shop', function () {
    $seller = User::factory()->create();
    $seller->assignRole(Role::ROLE_SELLER);

    $shop = Shop::factory()->create(['owner_id' => $seller->id]);
    $product = Product::factory()->create(['shop_id' => $shop->id]);

    $this->actingAs($seller);

    expect(Gate::allows('create', [Comment::class, $product]))->toBeTrue();
});

it('denies sellers from commenting on products from other shops', function () {
    $seller = User::factory()->create();
    $seller->assignRole(Role::ROLE_SELLER);

    $otherSeller = User::factory()->create();
    $shop = Shop::factory()->create(['owner_id' => $otherSeller->id]);
    $product = Product::factory()->create(['shop_id' => $shop->id]);

    $this->actingAs($seller);

    expect(Gate::allows('create', [Comment::class, $product]))->toBeFalse();
});

it('allows staff/admins to comment on any product', function () {
    $staff = User::factory()->create();
    $staff->assignRole(Role::ROLE_STAFF);

    $product = Product::factory()->create();

    $this->actingAs($staff);

    expect(Gate::allows('create', [Comment::class, $product]))->toBeTrue();
});

it('denies customers from commenting', function () {
    $customer = User::factory()->create();
    $customer->assignRole(Role::ROLE_CUSTOMER);

    $product = Product::factory()->create();

    $this->actingAs($customer);

    expect(Gate::allows('create', [Comment::class, $product]))->toBeFalse();
});

it('cascades deletion to comments when product is deleted', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    Comment::create([
        'user_id' => $user->id,
        'commentable_type' => 'product',
        'commentable_id' => $product->id,
        'text' => 'Test comment',
    ]);

    expect(Comment::count())->toBe(1);

    $product->delete();

    expect(Comment::count())->toBe(0);
});

it('allows author to edit their own comment', function () {
    $seller = User::factory()->create();
    $seller->assignRole(Role::ROLE_SELLER);

    $shop = Shop::factory()->create(['owner_id' => $seller->id]);
    $product = Product::factory()->create(['shop_id' => $shop->id]);

    $comment = Comment::create([
        'user_id' => $seller->id,
        'commentable_type' => 'product',
        'commentable_id' => $product->id,
        'text' => 'Old text',
    ]);

    $this->actingAs($seller);

    expect(Gate::allows('update', $comment))->toBeTrue();
});

it('denies author from editing comment if they lose access to commentable', function () {
    $seller = User::factory()->create();
    $seller->assignRole(Role::ROLE_SELLER);

    $shop = Shop::factory()->create(['owner_id' => $seller->id]);
    $product = Product::factory()->create(['shop_id' => $shop->id]);

    $comment = Comment::create([
        'user_id' => $seller->id,
        'commentable_type' => 'product',
        'commentable_id' => $product->id,
        'text' => 'Text',
    ]);

    // Reassign shop to someone else
    $shop->update(['owner_id' => User::factory()->create()->id]);

    $this->actingAs($seller);

    expect(Gate::allows('update', $comment))->toBeFalse();
});
