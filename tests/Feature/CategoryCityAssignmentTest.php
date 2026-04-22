<?php

use App\Models\Category;
use App\Models\CategoryCityAssignment;
use App\Models\City;
use App\Models\Shop;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('assigns a shop to a category in a city', function () {
    $category = Category::factory()->create();
    $city = City::factory()->create();
    $shop = Shop::factory()->create();

    $assignment = CategoryCityAssignment::create([
        'category_id' => $category->id,
        'city_id' => $city->id,
        'shop_id' => $shop->id,
    ]);

    expect($assignment)->toBeInstanceOf(CategoryCityAssignment::class)
        ->and($assignment->category_id)->toBe($category->id)
        ->and($assignment->city_id)->toBe($city->id)
        ->and($assignment->shop_id)->toBe($shop->id);
});

it('prevents multiple shops for same category in same city', function () {
    $category = Category::factory()->create();
    $city = City::factory()->create();
    $shop1 = Shop::factory()->create();
    $shop2 = Shop::factory()->create();

    CategoryCityAssignment::create([
        'category_id' => $category->id,
        'city_id' => $city->id,
        'shop_id' => $shop1->id,
    ]);

    $this->expectException(UniqueConstraintViolationException::class);

    CategoryCityAssignment::create([
        'category_id' => $category->id,
        'city_id' => $city->id,
        'shop_id' => $shop2->id,
    ]);
});

it('finds the shop for a category in a city using helper', function () {
    $category = Category::factory()->create();
    $city = City::factory()->create();
    $shop = Shop::factory()->create();

    CategoryCityAssignment::create([
        'category_id' => $category->id,
        'city_id' => $city->id,
        'shop_id' => $shop->id,
    ]);

    expect($category->shopInCity($city)->id)->toBe($shop->id)
        ->and($category->hasShopInCity($city))->toBeTrue();
});

it('returns null if no shop assigned to category in city', function () {
    $category = Category::factory()->create();
    $city = City::factory()->create();

    expect($category->shopInCity($city))->toBeNull()
        ->and($category->hasShopInCity($city))->toBeFalse();
});

it('returns assigned categories for a shop', function () {
    $shop = Shop::factory()->create();
    $category1 = Category::factory()->create(['name' => 'Cat 1']);
    $category2 = Category::factory()->create(['name' => 'Cat 2']);
    $city = City::factory()->create();

    CategoryCityAssignment::create([
        'category_id' => $category1->id,
        'city_id' => $city->id,
        'shop_id' => $shop->id,
    ]);

    CategoryCityAssignment::create([
        'category_id' => $category2->id,
        'city_id' => $city->id,
        'shop_id' => $shop->id,
    ]);

    $assignments = $shop->assignedCategories();
    expect($assignments)->toHaveCount(2)
        ->and($assignments->first()->category->name)->toBe('Cat 1')
        ->and($assignments->last()->category->name)->toBe('Cat 2');
});

it('auto-generates a unique slug for category', function () {
    $category1 = Category::create(['name' => 'Fresh Produce']);
    $category2 = Category::create(['name' => 'FreshProduce']);

    expect($category1->slug)->toBe('fresh_produce')
        ->and($category2->slug)->toBe('fresh_produce_2');
});

it('blocks category deletion if it has assignments', function () {
    $category = Category::factory()->create();
    $city = City::factory()->create();
    $shop = Shop::factory()->create();

    CategoryCityAssignment::create([
        'category_id' => $category->id,
        'city_id' => $city->id,
        'shop_id' => $shop->id,
    ]);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Cannot delete category because it has city-shop assignments.');

    $category->delete();
});

it('blocks shop deletion if it has assignments', function () {
    $category = Category::factory()->create();
    $city = City::factory()->create();
    $shop = Shop::factory()->create();

    CategoryCityAssignment::create([
        'category_id' => $category->id,
        'city_id' => $city->id,
        'shop_id' => $shop->id,
    ]);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Cannot delete shop because it has category-city assignments.');

    $shop->delete();
});
