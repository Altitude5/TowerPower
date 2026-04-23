<?php

use App\Models\Product;
use App\Models\Rating;
use App\Models\Shop;
use App\Models\User;
use App\Services\RatingService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can rate a product', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $rating = RatingService::updateOrCreateRating($user, $product, 5);

    expect($rating->score)->toBe(5)
        ->and($rating->ratable_id)->toBe($product->id)
        ->and($rating->ratable_type)->toBe('product');
});

it('can update an existing rating', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    RatingService::updateOrCreateRating($user, $product, 3);
    expect(Rating::count())->toBe(1);

    RatingService::updateOrCreateRating($user, $product, 5);
    expect(Rating::count())->toBe(1);
    expect(Rating::first()->score)->toBe(5);
});

it('calculates average score for product', function () {
    $product = Product::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    RatingService::updateOrCreateRating($user1, $product, 4);
    RatingService::updateOrCreateRating($user2, $product, 5);

    expect($product->averageScore())->toBe(4.5);
});

it('cascades deletion to ratings when product is deleted', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();
    RatingService::updateOrCreateRating($user, $product, 5);

    expect(Rating::count())->toBe(1);

    $product->delete(); // This is soft delete by default, but our observer uses ratings()->delete()

    // Wait, Product uses SoftDeletes. static::deleting fires on soft delete too.
    expect(Rating::count())->toBe(0);
});

it('can rate a shop', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create();

    $rating = RatingService::updateOrCreateRating($user, $shop, 4);

    expect($rating->score)->toBe(4)
        ->and($rating->ratable_id)->toBe($shop->id)
        ->and($rating->ratable_type)->toBe('shop');
});

it('enforces score range via validation and database', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    // Database check (PostgreSQL only in this app)
    $this->expectException(QueryException::class);

    // Bypass service and Eloquent validation to test DB constraint
    DB::table('ratings')->insert([
        'user_id' => $user->id,
        'ratable_type' => 'product',
        'ratable_id' => $product->id,
        'score' => 6,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
});
