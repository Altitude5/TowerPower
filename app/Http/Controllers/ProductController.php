<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function show(Category $category, Product $product): Response
    {
        abort_unless($product->category_id === $category->id, 404);
        abort_unless($product->available, 404);

        $product->load('shop');

        return Inertia::render('Product/Show', [
            'category' => $category,
            'product' => $product,
            'showAddToCart' => true, // Always show for now
        ]);
    }
}
