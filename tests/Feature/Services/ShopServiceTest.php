<?php

use App\Models\Shop;
use App\Models\User;
use App\Services\ShopService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->shopService = new ShopService;
});

test('a Shop without an owner and without products can be deleted', function () {
    $shop = Shop::factory()->create(['owner_id' => null]);

    $result = $this->shopService->delete($shop);

    expect($result)->toBeTrue()
        ->and(Shop::withTrashed()->find($shop->id)->trashed())->toBeTrue();
});

test('a Shop with an owner cannot be deleted', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->withOwner($user)->create();

    $this->shopService->delete($shop);
})->throws(Exception::class, 'Cannot delete shop because it has an owner.');

test('a Shop with an owner cannot be deleted and remains in the database', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->withOwner($user)->create();

    try {
        $this->shopService->delete($shop);
    } catch (Exception) {
        // Expected
    }

    expect(Shop::find($shop->id))->not->toBeNull();
});

test('a Shop can be deleted after its owner is removed', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->withOwner($user)->create();

    $shop->removeOwner($user);
    $result = $this->shopService->delete($shop);

    expect($result)->toBeTrue();
});
