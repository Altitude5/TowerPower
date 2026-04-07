<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Street;
use App\Models\Tower;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        // 1. Geography
        $city = City::factory()->create(['name' => 'Tel Aviv', 'code' => '70']);
        $street = Street::factory()->create(['city_id' => $city->id, 'name' => 'Dizengoff']);

        // 2. Towers
        $tower = Tower::factory()->create([
            'city_id' => $city->id,
            'street_id' => $street->id,
            'name' => 'Tower A',
        ]);

        // 3. Shops & Categories
        $category = Category::factory()->create(['name' => 'Groceries']);
        $shop = Shop::factory()->create(['name' => 'Local Market']);

        // 4. Products
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'category_id' => $category->id,
            'price' => 1000,
        ]);

        // 5. Users
        $user = User::factory()->create(['email' => 'customer2@example.com']);
        $tower->users()->attach($user->id, ['apartment_number' => '1', 'floor' => '1']);

        // 6. Carts
        $cart = Cart::create(['user_id' => $user->id, 'tower_id' => $tower->id]);
        $cart->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'price_type' => $product->price_type,
            'quantity' => 1,
        ]);

        // 7. Orders & Transactions
        $order = Order::create([
            'user_id' => $user->id,
            'tower_id' => $tower->id,
            'status' => 'pending',
        ]);

        $subOrder = $order->subOrders()->create([
            'shop_id' => $shop->id,
            'status' => 'pending',
        ]);

        $subOrder->orderItems()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'price_type' => $product->price_type,
            'quantity' => 1,
        ]);

        Transaction::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'amount' => $product->price,
            'status' => 'pending',
            'currency' => 'ILS',
            'gateway' => 'stripe',
        ]);
    }
}
