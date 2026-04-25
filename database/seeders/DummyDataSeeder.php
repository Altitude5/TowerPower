<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Schedule;
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

        // // 1. Geography
        // $city = City::firstOrCreate(['name' => 'Tel Aviv'], ['code' => '70']);
        // $street = Street::firstOrCreate(['city_id' => $city->id, 'name' => 'Dizengoff']);

        // // 2. Towers
        // $towers = Tower::factory()->count(5)->create([
        //     'city_id' => $city->id,
        //     'street_id' => $street->id,
        // ]);

        // // 3. Categories
        // $categories = Category::factory()->count(3)->create();

        // // 4. Sellers and Shops
        // $sellerRole = Role::where('slug', 'seller')->first();
        // if ($sellerRole) {
        //     $sellers = User::factory()->count(3)->create()->each(function ($user) use ($sellerRole) {
        //         $user->assignRole($sellerRole);
        //     });

        //     foreach ($sellers as $seller) {
        //         $shop = Shop::factory()->create(['owner_id' => $seller->id]);
        //         Product::factory()->count(5)->create([
        //             'shop_id' => $shop->id,
        //             'category_id' => $categories->random()->id,
        //         ]);
        //     }
        // }
        $towers = Tower::all();
        // 5. Customers
        $customerRole = Role::where('slug', 'customer')->first();
        if ($customerRole) {
            $customers = User::factory()->count(20)->create()->each(function ($user) use ($customerRole) {
                $user->assignRole($customerRole);
            });

            // Register customers in towers
            foreach ($customers as $customer) {
                $tower = $towers->random();
                $tower->users()->attach($customer->id, [
                    'apartment_number' => rand(1, 100),
                    'floor' => rand(1, 20),
                ]);
            }

            // 1. Geography
            $city = City::firstOrCreate(['name' => 'Tel Aviv'], ['code' => '70']);
            $street = Street::firstOrCreate(['city_id' => $city->id, 'name' => 'Dizengoff']);

            // 5.5 Create Schedules for deliveries
            $allCities = City::all();
            if ($allCities->isEmpty()) {
                $allCities = collect([$city]);
            }
            
            $shops = Shop::all();
            if ($shops->isEmpty()) {
                // If no shops exist, we need to create some or at least one for schedules
                $sellerRole = Role::where('slug', 'seller')->first();
                $seller = User::factory()->create();
                $seller->assignRole($sellerRole);
                $shops = collect([Shop::factory()->create(['owner_id' => $seller->id])]);
            }

            $deliveryPersonRole = Role::where('slug', 'delivery_person')->first();
            $deliveryPersons = User::whereHas('roles', fn($q) => $q->where('slug', 'delivery_person'))->get();
            
            if ($deliveryPersons->isEmpty()) {
                $deliveryPersons = User::factory()->count(5)->create()->each(fn($u) => $u->assignRole($deliveryPersonRole));
            }

            for ($i = 0; $i < 100; $i++) {
                Schedule::factory()->create([
                    'shop_id' => $shops->random()->id,
                    'city_id' => $allCities->random()->id,
                    'delivery_person_id' => $deliveryPersons->random()->id,
                    'type' => 'positive',
                ]);
            }
        }

        $customers = User::whereHas('roles', function ($q) {
            $q->where('slug', 'customer');
        })->get();

        // 6. Orders & Transactions
        $products = Product::all();
        $shops = Shop::all();
        $deliveryService = app(\App\Services\DeliveryAssignmentService::class);

        for ($i = 0; $i < 100; $i++) {
            $customer = $customers->random();
            $tower = ($customer->towers && $customer->towers->isNotEmpty()) ? $customer->towers->first() : $towers->random();

            $order = Order::create([
                'user_id' => $customer->id,
                'tower_id' => $tower->id,
                'status' => 'pending',
            ]);

            $selectedShops = $shops->random(rand(1, 2));
            foreach ($selectedShops as $shop) {
                $subOrder = $order->subOrders()->create([
                    'shop_id' => $shop->id,
                    'status' => 'pending',
                ]);

                $product = $products->where('shop_id', $shop->id)->random();
                $subOrder->orderItems()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'price_type' => $product->price_type,
                    'quantity' => 1,
                ]);

                // Auto-assign delivery
                $deliveryService->assignToSubOrder($subOrder);
            }

            Transaction::create([
                'order_id' => $order->id,
                'user_id' => $customer->id,
                'amount' => $order->subOrders->sum(fn ($so) => $so->orderItems->sum('price')),
                'status' => 'pending',
                'currency' => 'ILS',
                'gateway' => 'stripe',
            ]);
        }
    }
}
