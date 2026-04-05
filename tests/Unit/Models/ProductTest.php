<?php

use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('priceUnit returns ILS for Unit price type', function () {
    $product = Product::factory()->create(['price_type' => 'Unit']);

    expect($product->priceUnit())->toBe('ILS');
});

test('priceUnit returns ILS/Kg for Weight price type', function () {
    $product = Product::factory()->create(['price_type' => 'Weight']);

    expect($product->priceUnit())->toBe('ILS/Kg');
});

test('priceUnit returns ILS/Litre for Volume price type', function () {
    $product = Product::factory()->create(['price_type' => 'Volume']);

    expect($product->priceUnit())->toBe('ILS/Litre');
});

test('imageUrl returns Storage URL when image_path is set', function () {
    $product = Product::factory()->withImage('products/test.jpg')->create();

    expect($product->imageUrl())->toContain('products/test.jpg');
});

test('imageUrl returns placeholder when image_path is null', function () {
    $product = Product::factory()->create(['image_path' => null]);

    expect($product->imageUrl())->toBe('/images/placeholder-product.png');
});

test('stock returns stock_quantity when set', function () {
    $product = Product::factory()->withStockQuantity(42.5)->create();

    expect($product->stock())->toBe(42.5);
});

test('stock returns stock_weight when set', function () {
    $product = Product::factory()->withStockWeight(10.25)->create();

    expect($product->stock())->toBe(10.25);
});

test('stock returns stock_volume when set', function () {
    $product = Product::factory()->withStockVolume(5.0)->create();

    expect($product->stock())->toBe(5.0);
});

test('stock returns null when no stock field is set', function () {
    $product = Product::factory()->create();

    expect($product->stock())->toBeNull();
});

test('available returns true by default', function () {
    $product = Product::factory()->create();

    expect($product->available())->toBeTrue();
});

test('available returns false for unavailable product', function () {
    $product = Product::factory()->unavailable()->create();

    expect($product->available())->toBeFalse();
});

test('sku returns the SKU value', function () {
    $product = Product::factory()->create(['sku' => 'ABC-1234-XY']);

    expect($product->sku())->toBe('ABC-1234-XY');
});

test('sku returns null when not set', function () {
    $product = Product::factory()->create(['sku' => null]);

    expect($product->sku())->toBeNull();
});

test('product name is unique per shop (composite unique constraint)', function () {
    $shop = Shop::factory()->create();

    Product::factory()->create(['shop_id' => $shop->id, 'name' => 'Same Name']);
    Product::factory()->create(['shop_id' => $shop->id, 'name' => 'Same Name']);
})->throws(QueryException::class);

test('product with same name in different shop is allowed', function () {
    $shop1 = Shop::factory()->create();
    $shop2 = Shop::factory()->create();

    $p1 = Product::factory()->create(['shop_id' => $shop1->id, 'name' => 'Same Name']);
    $p2 = Product::factory()->create(['shop_id' => $shop2->id, 'name' => 'Same Name']);

    expect($p1->id)->not->toBe($p2->id);
});

test('product belongs to a shop', function () {
    $shop = Shop::factory()->create();
    $product = Product::factory()->create(['shop_id' => $shop->id]);

    expect($product->shop->id)->toBe($shop->id);
});

test('soft delete does not permanently remove the product', function () {
    $product = Product::factory()->create();

    $product->delete();

    expect(Product::withTrashed()->find($product->id))->not->toBeNull()
        ->and(Product::withTrashed()->find($product->id)->trashed())->toBeTrue();
});
