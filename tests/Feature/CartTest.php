<?php

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Tower;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a cart for a user', function () {
    $user = User::factory()->create();

    $cart = CartService::getCart($user);

    expect($cart)->toBeInstanceOf(Cart::class);
    expect($cart->user_id)->toBe($user->id);
    expect(Cart::count())->toBe(1);
});

it('snapshots product data when adding to cart', function () {
    $user = User::factory()->create();
    $shop = Shop::factory()->create(['name' => 'Original Shop']);
    $category = Category::factory()->create(['name' => 'Original Category']);
    $product = Product::factory()->create([
        'name' => 'Original Name',
        'price' => 1000,
        'price_type' => 'Unit',
        'shop_id' => $shop->id,
        'category_id' => $category->id,
    ]);

    $cartItem = CartService::addItem($user, $product, ['quantity' => 2]);

    expect($cartItem->product_name)->toBe('Original Name');
    expect($cartItem->category_name)->toBe('Original Category');
    expect($cartItem->shop_name)->toBe('Original Shop');
    expect($cartItem->price)->toBe(1000);
    expect($cartItem->price_type)->toBe('Unit');
    expect($cartItem->quantity)->toBe('2.000');

    // Change product data
    $product->update([
        'name' => 'New Name',
        'price' => 2000,
    ]);
    $shop->update(['name' => 'New Shop']);
    $category->update(['name' => 'New Category']);

    // Cart item should remain unchanged
    $cartItem->refresh();
    expect($cartItem->product_name)->toBe('Original Name');
    expect($cartItem->category_name)->toBe('Original Category');
    expect($cartItem->shop_name)->toBe('Original Shop');
    expect($cartItem->price)->toBe(1000);
});

it('updates quantity when adding same product twice', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price_type' => 'Unit']);

    CartService::addItem($user, $product, ['quantity' => 1]);
    CartService::addItem($user, $product, ['quantity' => 2]);

    $cart = CartService::getCart($user);
    expect($cart->items)->toHaveCount(1);
    expect($cart->items->first()->quantity)->toBe('3.000');
});

it('switches tower and clears items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $cart = CartService::getCart($user);
    CartService::addItem($user, $product, ['quantity' => 1]);

    expect($cart->items()->count())->toBe(1);

    CartService::switchTower($cart, 99);

    $cart->refresh();
    expect($cart->tower_id)->toBe(99);
    expect($cart->items()->count())->toBe(0);
});

it('calculates total price correctly for unit items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 150, // 1.50 ILS
        'price_type' => 'Unit',
    ]);

    $cartItem = CartService::addItem($user, $product, ['quantity' => 3]);

    expect($cartItem->totalPrice())->toBe(450); // 150 * 3
});

it('calculates total price correctly for weight items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 1000, // 10.00 ILS/Kg
        'price_type' => 'Weight',
    ]);

    $cartItem = CartService::addItem($user, $product, ['weight' => 1.5]);

    expect($cartItem->totalPrice())->toBe(1500); // 1000 * 1.5
});

it('calculates total price correctly for volume items', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 2000, // 20.00 ILS/Litre
        'price_type' => 'Volume',
    ]);

    $cartItem = CartService::addItem($user, $product, ['volume' => 0.75]);

    expect($cartItem->totalPrice())->toBe(1500); // 2000 * 0.75 = 1500
});

it('persists cart between sessions', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price_type' => 'Unit']);

    CartService::addItem($user, $product, ['quantity' => 5]);

    // Simulate new session/request
    $cart = Cart::where('user_id', $user->id)->first();
    expect($cart->items)->toHaveCount(1);
    expect($cart->items->first()->quantity)->toBe('5.000');
});

it('clears items without deleting the cart row', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $cart = CartService::getCart($user);
    CartService::addItem($user, $product, ['quantity' => 1]);

    CartService::clearItems($cart);

    expect(Cart::count())->toBe(1);
    expect(CartItem::count())->toBe(0);
});

it('decrements quantity and deletes item if it reaches zero', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price_type' => 'Unit']);

    CartService::addItem($user, $product, ['quantity' => 2]);
    expect(CartItem::count())->toBe(1);

    CartService::addItem($user, $product, ['quantity' => -1]);
    $cartItem = CartItem::where('product_id', $product->id)->first();
    expect($cartItem->quantity)->toBe('1.000');

    CartService::addItem($user, $product, ['quantity' => -1]);
    expect(CartItem::count())->toBe(0);
});

it('adds unit, weight, and volume items correctly', function () {
    $user = User::factory()->create();

    $unitProduct = Product::factory()->create(['price_type' => 'Unit', 'price' => 100]);
    $weightProduct = Product::factory()->create(['price_type' => 'Weight', 'price' => 200]);
    $volumeProduct = Product::factory()->create(['price_type' => 'Volume', 'price' => 300]);

    CartService::addItem($user, $unitProduct, ['quantity' => 1]);
    CartService::addItem($user, $weightProduct, ['weight' => 0.5]);
    CartService::addItem($user, $volumeProduct, ['volume' => 0.25]);

    $cart = CartService::getCart($user);
    expect($cart->items)->toHaveCount(3);

    $unitItem = $cart->items()->where('product_id', $unitProduct->id)->first();
    expect($unitItem->quantity)->toBe('1.000');
    expect($unitItem->weight)->toBeNull();
    expect($unitItem->volume)->toBeNull();

    $weightItem = $cart->items()->where('product_id', $weightProduct->id)->first();
    expect($weightItem->weight)->toBe('0.500');
    expect($weightItem->quantity)->toBeNull();
    expect($weightItem->volume)->toBeNull();

    $volumeItem = $cart->items()->where('product_id', $volumeProduct->id)->first();
    expect($volumeItem->volume)->toBe('0.250');
    expect($volumeItem->quantity)->toBeNull();
    expect($volumeItem->weight)->toBeNull();
});

it('throws an exception if more than one of quantity, weight, or volume is provided', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    CartService::addItem($user, $product, [
        'quantity' => 1,
        'weight' => 2.5,
    ]);
})->throws(InvalidArgumentException::class, 'A cart item must have exactly one of: quantity, weight, or volume.');

it('uses default increments when amount is not provided', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
    ]);
    // Assuming the user has a default tower or we handle it
    $tower = Tower::factory()->create();
    $user->towers()->attach($tower, [
        'is_default' => true,
        'apartment_number' => '123',
        'floor' => 1,
    ]);

    $unitProduct = Product::factory()->create(['price_type' => 'Unit']);
    $weightProduct = Product::factory()->create(['price_type' => 'Weight']);
    $volumeProduct = Product::factory()->create(['price_type' => 'Volume']);

    // Add Unit item - should default to 1
    CartService::addItem($user, $unitProduct, []);
    $cart = CartService::getCart($user);
    expect($cart->items()->where('product_id', $unitProduct->id)->first()->quantity)->toBe('1.000');

    // Add Weight item - should default to 0.5
    CartService::addItem($user, $weightProduct, []);
    expect($cart->items()->where('product_id', $weightProduct->id)->first()->weight)->toBe('0.500');

    // Add Volume item - should default to 0.5
    CartService::addItem($user, $volumeProduct, []);
    expect($cart->items()->where('product_id', $volumeProduct->id)->first()->volume)->toBe('0.500');

    // Increment existing Unit item - should add 1
    CartService::addItem($user, $unitProduct, []);
    expect($cart->items()->where('product_id', $unitProduct->id)->first()->quantity)->toBe('2.000');

    // Increment existing Weight item - should add 0.25
    CartService::addItem($user, $weightProduct, []);
    expect($cart->items()->where('product_id', $weightProduct->id)->first()->weight)->toBe('0.750');

    // Increment existing Volume item - should add 0.25
    CartService::addItem($user, $volumeProduct, []);
    expect($cart->items()->where('product_id', $volumeProduct->id)->first()->volume)->toBe('0.750');
});

it('uses default increments when amount is provided as null', function () {
    $user = User::factory()->create();
    $tower = Tower::factory()->create();
    $user->towers()->attach($tower, [
        'is_default' => true,
        'apartment_number' => '123',
        'floor' => 1,
    ]);

    $unitProduct = Product::factory()->create(['price_type' => 'Unit']);

    // Pass null explicitly - simulating empty form field
    CartService::addItem($user, $unitProduct, ['quantity' => null]);

    $cart = CartService::getCart($user);
    $item = $cart->items()->where('product_id', $unitProduct->id)->first();

    expect($item->quantity)->not->toBeNull()
        ->and($item->quantity)->toBe('1.000');
});

it('returns grouped items in the cart index', function () {
    $user = User::factory()->create();
    $tower = Tower::factory()->create();
    $user->towers()->attach($tower, ['is_default' => true, 'apartment_number' => '1', 'floor' => 1]);

    $shop1 = Shop::factory()->create(['name' => 'Shop 1']);
    $shop2 = Shop::factory()->create(['name' => 'Shop 2']);

    $category1 = Category::factory()->create(['name' => 'Category 1']);
    $category2 = Category::factory()->create(['name' => 'Category 2']);

    $product1 = Product::factory()->create(['shop_id' => $shop1->id, 'category_id' => $category1->id, 'price_type' => 'Unit']);
    $product2 = Product::factory()->create(['shop_id' => $shop2->id, 'category_id' => $category2->id, 'price_type' => 'Unit']);

    CartService::addItem($user, $product1, ['quantity' => 1]);
    CartService::addItem($user, $product2, ['quantity' => 1]);

    $this->actingAs($user)
        ->get(route('cart.index'))
        ->assertInertia(fn ($page) => $page
            ->has('groupedItems', 2)
            ->where('groupedItems.0.category_name', 'Category 1')
            ->where('groupedItems.1.category_name', 'Category 2')
        );
});
