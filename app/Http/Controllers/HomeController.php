<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        
        return Inertia::render('Home', [
            'roles' => [
                'isCustomer' => $user->isCustomer(),
                'isSeller' => $user->isSeller(),
                'isDeliveryPerson' => $user->isDeliveryPerson(),
                'isStaff' => $user->isStaff(),
                'isSuperUser' => $user->isSuperUser(),
            ],
            'categories' => Category::all(),
            'featuredProducts' => Product::where('available', true)
                ->latest()
                ->take(8)
                ->get(),
        ]);
    }
}
