<?php

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->productService = new ProductService;
});

test('force delete succeeds when product has no order items or cart items', function () {
    $product = Product::factory()->create();
    $product->delete(); // soft delete first

    $result = $this->productService->forceDelete($product);

    expect($result)->toBeTrue()
        ->and(Product::withTrashed()->find($product->id))->toBeNull();
});

test('soft delete is always allowed via model delete', function () {
    $product = Product::factory()->create();

    $product->delete();

    expect(Product::withTrashed()->find($product->id)->trashed())->toBeTrue();
});
