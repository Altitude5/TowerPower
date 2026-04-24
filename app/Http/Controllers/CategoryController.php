<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function show(Category $category): Response
    {
        return Inertia::render('Category/Show', [
            'category' => $category,
            'products' => $category->products()
                ->where('available', true)
                ->latest()
                ->paginate(24),
        ]);
    }
}
