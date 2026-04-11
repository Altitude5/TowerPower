<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\CartService;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService)
    {
    }

    public function index(): Response
    {
        $cart = CartService::getCart(auth()->user());
        $cart->load('items.product');

        return Inertia::render('Cart/Index', [
            'cart' => $cart,
            'items' => $cart->items,
        ]);
    }
}
