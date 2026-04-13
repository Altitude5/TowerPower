<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('Home', [
            'roles' => [
                'isCustomer' => $user?->isCustomer() ?? false,
                'isSeller' => $user?->isSeller() ?? false,
                'isDeliveryPerson' => $user?->isDeliveryPerson() ?? false,
                'isStaff' => $user?->isStaff() ?? false,
                'isSuperUser' => $user?->isSuperUser() ?? false,
            ],
            'categories' => Category::all(),
            'featuredProducts' => Product::with('category')
                ->where('available', true)
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }}
