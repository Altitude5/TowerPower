<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\City;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Street;
use App\Models\Tower;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Categories
        $fishCategory = Category::firstOrCreate(['slug' => 'fish'], ['name' => 'Fish Shop']);
        $meatCategory::firstOrCreate(['slug' => 'meat-and-puoltry'], ['name' => 'Meat & Poultry']);
        $winesCategory::firstOrCreate(['slug' => 'wines-and-spirits'], ['name' => 'Wines and Spirits Shop']);

        // 2. Seed Shops
        $fishShop = Shop::firstOrCreate(['name' => 'Fish Shop']);
        $meatShop = Shop::firstOrCreate(['name' => 'Meat Shop']);
        $winesShop = Shop::firstOrCreate(['name' => 'Wines Shop']);

        // 3. Seed Fish Products
        $products = [
            ['name' => 'Salmon', 'image' => 'salmon.png', 'price_type' => 'Weight', 'price' => 6900],
            ['name' => 'Tuna', 'image' => 'satunalmon.png', 'price_type' => 'Weight', 'price' => 6900],
            ['name' => 'Tilapia', 'image' => 'tilapia.png', 'price_type' => 'Weight', 'price' => 6490],
            ['name' => 'Alaska Pollock', 'image' => 'pollock.png', 'price_type' => 'Weight', 'price' => 3290],
            ['name' => 'Cod', 'image' => 'cod.png', 'price_type' => 'Weight', 'price' => 12000],
            ['name' => 'Catfish', 'image' => 'catfish.png', 'price_type' => 'Weight', 'price' => 6090],
            ['name' => 'Carp', 'image' => 'carp.png', 'price_type' => 'Weight', 'price' => 6690],
            ['name' => 'Sardines', 'image' => 'sardines.png', 'price_type' => 'Weight', 'price' => 6050],
            ['name' => '4 Tuna cans', 'image' => '4-tuna.png', 'price_type' => 'Unit', 'price' => 2020],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(
                ['slug' => Str::slug($p['name'])],
                [
                    'name' => $p['name'],
                    'price' => $p['price'],
                    'price_type' => ''.$p['price_type'],
                    'image_path' => ''.$p['image'],
                    'shop_id' => $fishShop->id,
                    'category_id' => $fishCategory->id,
                    'available' => true,
                ]
            );
        }

        // 4. Seed Geo Data (Cities, Streets, Towers)
        $israeliCities = [
            'Tel-Aviv' => '5000',
            'רמת-גן' => '8600',
            'Givataim' => '6300',
            'Petah Tikvah' => '7900',
            'Rishon Lezion' => '8300',
        ];

        foreach ($israeliCities as $cityName => $cityCode) {
            $city = City::firstOrCreate(
                ['code' => $cityCode],
                ['name' => $cityName]
            );

            // Create 3-5 streets per city
            for ($i = 0; $i < rand(3, 5); $i++) {
                $street = Street::firstOrCreate([
                    'city_id' => $city->id,
                    'name' => fake()->streetName(),
                ]);

                // Create 1-3 towers per street
                for ($j = 0; $j < rand(1, 3); $j++) {
                    Tower::create([
                        'name' => 'Tower '.fake()->company(),
                        'city_id' => $city->id,
                        'street_id' => $street->id,
                        'house_number' => fake()->buildingNumber(),
                        'zipcode' => fake()->postcode(),
                        'state' => 'Center',
                        'country' => 'Israel',
                        'latitude' => fake()->latitude(31.9, 32.2),
                        'longitude' => fake()->longitude(34.7, 34.9),
                    ]);
                }
            }
        }

        $city = City::firstOrCreate(
            ['code' => '8600'],
            ['name' => 'רמת-גן']
        );
        $street = Street::firstOrCreate([
            'city_id' => $city->id,
            'name' => 'החולה',
        ]);

        Tower::create([
            'name' => 'מגדל החולה 10',
            'city_id' => $city->id,
            'street_id' => $street->id,
            'house_number' => 10,
            'zipcode' => fake()->postcode(),
            'state' => 'Center',
            'country' => 'Israel',
            'latitude' => fake()->latitude(31.9, 32.2),
            'longitude' => fake()->longitude(34.7, 34.9),
            'image_path' => 'החולה-10-רמת-גן.jpg',
        ]);

        Tower::create([
            'name' => 'מגדל החולה 100',
            'city_id' => $city->id,
            'street_id' => $street->id,
            'house_number' => 100,
            'zipcode' => fake()->postcode(),
            'state' => 'Center',
            'country' => 'Israel',
            'latitude' => fake()->latitude(31.9, 32.2),
            'longitude' => fake()->longitude(34.7, 34.9),
            'image_path' => 'החולה-100-רמת-גן.jpg',
        ]);

    }
}
