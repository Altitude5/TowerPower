<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Categories
        $fishCategory = Category::firstOrCreate(['slug' => 'fish'], ['name' => 'Fish Shop']);
        Category::firstOrCreate(['slug' => 'meat-and-puoltry'], ['name' => 'Meat & Poultry']);
        Category::firstOrCreate(['slug' => 'wines-and-spirits'], ['name' => 'Wines and Spirits Shop']);

        // 2. Seed Shops
        $fishShop = Shop::firstOrCreate(['name' => 'Fish Shop']);
        Shop::firstOrCreate(['name' => 'Meat Shop']);

        // 3. Seed Fish Products
        $products = [
            ['name' => 'Salmon', 'image' => 'salmon.png', 'price' => 6900],
            ['name' => 'Tilapia', 'image' => 'tilapia.png', 'price' => 6490],
            ['name' => 'Alaska Pollock', 'image' => 'pollock.png', 'price' => 3290],
            ['name' => 'Cod', 'image' => 'cod.png', 'price' => 12000],
            ['name' => 'Catfish', 'image' => 'catfish.png', 'price' => 6090],
            ['name' => 'Carp', 'image' => 'carp.png', 'price' => 6690],
            ['name' => 'Sardines', 'image' => 'sardines.png', 'price' => 2020],
        ];

        foreach ($products as $p) {
            Product::firstOrCreate(
                ['slug' => Str::slug($p['name'])],
                [
                    'name' => $p['name'],
                    'price' => $p['price'],
                    'price_type' => 'Weight',
                    'image_path' => ''.$p['image'],
                    'shop_id' => $fishShop->id,
                    'category_id' => $fishCategory->id,
                    'available' => true,
                ]
            );
        }
    }
}
