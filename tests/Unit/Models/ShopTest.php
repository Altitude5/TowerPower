<?php

use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('a Shop name must be unique', function () {
    Shop::factory()->create(['name' => 'Unique Shop']);
    Shop::factory()->create(['name' => 'Unique Shop']);
})->throws(QueryException::class);

test('a Shop can have an owner via BelongsTo relationship', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->withOwner($user)->create();

    expect($shop->owner->id)->toBe($user->id);
});

test('isOwner returns true when the given user is the owner', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->withOwner($user)->create();

    expect($shop->isOwner($user))->toBeTrue();
});

test('isOwner returns false when the given user is not the owner', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $shop = Shop::factory()->withOwner($owner)->create();

    expect($shop->isOwner($otherUser))->toBeFalse();
});

test('isOwner returns false when the shop has no owner', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['owner_id' => null]);

    expect($shop->isOwner($user))->toBeFalse();
});

test('makeOwner assigns a user as the shop owner', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['owner_id' => null]);

    $shop->makeOwner($user);

    expect($shop->fresh()->owner_id)->toBe($user->id);
});

test('makeOwner overwrites the existing owner', function () {
    $firstOwner = User::factory()->create();
    $secondOwner = User::factory()->create();
    $shop = Shop::factory()->withOwner($firstOwner)->create();

    $shop->makeOwner($secondOwner);

    expect($shop->fresh()->owner_id)->toBe($secondOwner->id);
});

test('removeOwner clears the owner when the given user is the current owner', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->withOwner($user)->create();

    $shop->removeOwner($user);

    expect($shop->fresh()->owner_id)->toBeNull();
});

test('removeOwner does nothing when the given user is not the current owner', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $shop = Shop::factory()->withOwner($owner)->create();

    $shop->removeOwner($otherUser);

    expect($shop->fresh()->owner_id)->toBe($owner->id);
});

test('minimumOrder returns the minimum_order value', function () {
    $shop = Shop::factory()->withMinimumOrder(100)->create();

    expect($shop->minimumOrder())->toBe(100);
});

test('minimumOrder returns null when not set', function () {
    $shop = Shop::factory()->create(['minimum_order' => null]);

    expect($shop->minimumOrder())->toBeNull();
});
